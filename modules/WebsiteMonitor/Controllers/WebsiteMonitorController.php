<?php

namespace App\Modules\WebsiteMonitor\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\WebsiteMonitor\Models\MonitorTarget;
use App\Modules\WebsiteMonitor\Models\MonitorLog;
use App\Modules\WebsiteMonitor\Models\MonitorAlert;
use App\Models\ModuleSetting;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WebsiteMonitorController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────

    public function index()
    {
        $targets = MonitorTarget::with(['creator', 'pic', 'alerts' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('last_checked_at', 'desc')
            ->get();

        $stats = [
            'total' => $targets->count(),
            'healthy' => $targets->filter(fn($t) => $t->last_checked_at && $t->isUp())->count(),
            'down' => $targets->filter(fn($t) => $t->last_checked_at && !$t->isUp())->count(),
            'pending' => $targets->filter(fn($t) => $t->last_checked_at === null)->count(),
        ];

        $settings = $this->getSettings();
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);

        return view('websitemonitor::index', compact('targets', 'stats', 'settings', 'users'));
    }

    // ─── CRUD ─────────────────────────────────────────────────────

    public function create()
    {
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);
        return view('websitemonitor::create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'method' => 'nullable|in:GET,HEAD,POST',
            'check_interval' => 'nullable|integer|min:1|max:1440',
            'timeout' => 'nullable|integer|min:1|max:120',
            'expected_status' => 'nullable|integer|min:100|max:599',
            'check_string' => 'nullable|string|max:500',
            'alert_on_down' => 'nullable|boolean',
            'alert_methods' => 'nullable|string|max:50',
            'pic_user_id' => 'nullable|exists:users,id',
        ]);

        MonitorTarget::create([
            'name' => $request->name,
            'url' => $request->url,
            'method' => $request->method ?? 'GET',
            'check_interval' => $request->check_interval ?? 5,
            'timeout' => $request->timeout ?? 10,
            'expected_status' => $request->expected_status ?? 200,
            'check_string' => $request->check_string,
            'is_active' => true,
            'alert_on_down' => $request->boolean('alert_on_down', true),
            'alert_methods' => $request->alert_methods ?? 'message',
            'pic_user_id' => $request->pic_user_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('websitemonitor.index')
            ->with('success', 'Monitor target created successfully.');
    }

    public function edit($id)
    {
        $target = MonitorTarget::findOrFail($id);
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'email']);
        return view('websitemonitor::edit', compact('target', 'users'));
    }

    public function update(Request $request, $id)
    {
        $target = MonitorTarget::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'method' => 'nullable|in:GET,HEAD,POST',
            'check_interval' => 'nullable|integer|min:1|max:1440',
            'timeout' => 'nullable|integer|min:1|max:120',
            'expected_status' => 'nullable|integer|min:100|max:599',
            'check_string' => 'nullable|string|max:500',
            'alert_on_down' => 'nullable|boolean',
            'alert_methods' => 'nullable|string|max:50',
            'pic_user_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        $target->update([
            'name' => $request->name,
            'url' => $request->url,
            'method' => $request->method ?? $target->method,
            'check_interval' => $request->check_interval ?? 5,
            'timeout' => $request->timeout ?? 10,
            'expected_status' => $request->expected_status ?? 200,
            'check_string' => $request->check_string,
            'is_active' => $request->boolean('is_active', $target->is_active),
            'alert_on_down' => $request->boolean('alert_on_down', $target->alert_on_down),
            'alert_methods' => $request->alert_methods ?? 'message',
            'pic_user_id' => $request->pic_user_id,
        ]);

        return redirect()
            ->route('websitemonitor.index')
            ->with('success', 'Monitor target updated successfully.');
    }

    public function destroy($id)
    {
        $target = MonitorTarget::findOrFail($id);
        $target->logs()->delete();
        $target->alerts()->delete();
        $target->delete();

        return redirect()
            ->route('websitemonitor.index')
            ->with('success', 'Monitor target deleted successfully.');
    }

    // ─── Check ────────────────────────────────────────────────────

    public function checkNow($id)
    {
        $target = MonitorTarget::findOrFail($id);
        $result = $this->performCheck($target);

        MonitorLog::create([
            'monitor_target_id' => $target->id,
            'status_code' => $result['status_code'],
            'response_time' => $result['response_time'],
            'error_message' => $result['error'],
            'checked_by' => auth()->id(),
        ]);

        $wasUp = $target->isUp();
        $isUp = $result['status_code'] === $target->expected_status
            && $result['error'] === null
            && ($target->check_string === null || $result['body_contains']);

        $target->update([
            'last_checked_at' => now(),
            'last_status' => $result['status_code'],
            'last_response_time' => $result['response_time'],
            'last_error' => $result['error'],
        ]);

        // Send alert if transitioned from up to down
        if ($target->alert_on_down && $wasUp && !$isUp) {
            $this->sendAlert($target, $result);
        }

        return redirect()
            ->route('websitemonitor.index')
            ->with('success', "Check completed. Status: {$result['status_code']} (" . round($result['response_time'], 2) . "s)");
    }

    // ─── Logs ─────────────────────────────────────────────────────

    public function logs($id)
    {
        $target = MonitorTarget::with('logs.checker')->findOrFail($id);
        $logs = $target->logs()->orderBy('created_at', 'desc')->paginate(25);

        return view('websitemonitor::logs', compact('target', 'logs'));
    }

    // ─── Settings ─────────────────────────────────────────────────

    public function settings()
    {
        $settings = $this->getSettings();
        return view('websitemonitor::settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'default_check_interval' => 'integer|min:1|max:1440',
            'default_timeout' => 'integer|min:1|max:120',
            'alert_global_enabled' => 'boolean',
            'default_alert_methods' => 'string|max:50',
            'notify_admin_ids' => 'nullable|string',
            'auto_resolve_after' => 'integer|min:0|max:10080',
            'retain_log_days' => 'integer|min:1|max:365',
        ]);

        $keys = [
            'default_check_interval', 'default_timeout', 'alert_global_enabled',
            'default_alert_methods', 'notify_admin_ids', 'auto_resolve_after', 'retain_log_days',
        ];

        foreach ($keys as $key) {
            $val = $request->input($key);
            if ($val !== null) {
                ModuleSetting::updateOrCreate(
                    ['module' => 'websitemonitor', 'key' => $key],
                    ['value' => is_bool($val) ? ($val ? '1' : '0') : (string) $val]
                );
            }
        }

        return redirect()
            ->route('websitemonitor.settings')
            ->with('success', 'Settings updated successfully.');
    }

    // ─── Dashboard Widget API ─────────────────────────────────────

    public function widgetData()
    {
        $targets = MonitorTarget::all();

        $stats = [
            'total' => $targets->count(),
            'healthy' => $targets->filter(fn($t) => $t->last_checked_at && $t->isUp())->count(),
            'down' => $targets->filter(fn($t) => $t->last_checked_at && !$t->isUp())->count(),
            'pending' => $targets->filter(fn($t) => $t->last_checked_at === null)->count(),
        ];

        $downTargets = $targets->filter(fn($t) => $t->last_checked_at && !$t->isUp())->values()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'url' => $t->url,
            'last_status' => $t->last_status,
            'last_response_time' => $t->last_response_time,
            'last_checked_at' => $t->last_checked_at?->diffForHumans(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'down_targets' => $downTargets,
                'health_percentage' => $stats['total'] > 0
                    ? round(($stats['healthy'] / $stats['total']) * 100)
                    : 100,
            ],
        ]);
    }

    // ─── Cron: Auto Check ─────────────────────────────────────────

    public function cronCheck()
    {
        $targets = MonitorTarget::needsCheck()->get();
        $results = [];

        foreach ($targets as $target) {
            $result = $this->performCheck($target);

            MonitorLog::create([
                'monitor_target_id' => $target->id,
                'status_code' => $result['status_code'],
                'response_time' => $result['response_time'],
                'error_message' => $result['error'],
                'checked_by' => null,
            ]);

            $wasUp = $target->isUp();
            $isUp = $result['status_code'] === $target->expected_status
                && $result['error'] === null
                && ($target->check_string === null || $result['body_contains']);

            $target->update([
                'last_checked_at' => now(),
                'last_status' => $result['status_code'],
                'last_response_time' => $result['response_time'],
                'last_error' => $result['error'],
            ]);

            // Alert on down transition
            if ($target->alert_on_down && $wasUp && !$isUp) {
                $this->sendAlert($target, $result);
            }

            $results[] = [
                'id' => $target->id,
                'name' => $target->name,
                'status' => $isUp ? 'up' : 'down',
                'status_code' => $result['status_code'],
                'response_time' => $result['response_time'],
            ];
        }

        return $results;
    }

    // ─── Core Check Logic ─────────────────────────────────────────

    protected function performCheck(MonitorTarget $target): array
    {
        $method = strtolower($target->method ?? 'get');
        $client = new Client([
            'timeout' => $target->timeout,
            'allow_redirects' => true,
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'WebsiteMonitor/1.0',
            ],
        ]);

        $start = microtime(true);

        try {
            $response = $client->request($method, $target->url);
            $end = microtime(true);
            $responseTime = round(($end - $start), 3);
            $body = (string) $response->getBody();

            $bodyContains = true;
            if ($target->check_string) {
                $bodyContains = str_contains($body, $target->check_string);
            }

            return [
                'status_code' => $response->getStatusCode(),
                'response_time' => $responseTime,
                'error' => $bodyContains ? null : "Required text not found: '{$target->check_string}'",
                'body_contains' => $bodyContains,
            ];
        } catch (ConnectException $e) {
            $end = microtime(true);
            return [
                'status_code' => 0,
                'response_time' => round(($end - $start), 3),
                'error' => 'Connection failed: ' . $e->getMessage(),
                'body_contains' => false,
            ];
        } catch (RequestException $e) {
            $end = microtime(true);
            $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            return [
                'status_code' => $code,
                'response_time' => round(($end - $start), 3),
                'error' => $e->getMessage(),
                'body_contains' => false,
            ];
        } catch (\Exception $e) {
            $end = microtime(true);
            return [
                'status_code' => 0,
                'response_time' => round(($end - $start), 3),
                'error' => $e->getMessage(),
                'body_contains' => false,
            ];
        }
    }

    // ─── Alert Logic ──────────────────────────────────────────────

    protected function sendAlert(MonitorTarget $target, array $result): void
    {
        $settings = $this->getSettings();
        $alertMethods = explode(',', $target->alert_methods ?? 'message');

        $subject = "DOWN: {$target->name}";
        $messageText = "Website Monitor Alert\n\n"
            . "Target: {$target->name}\n"
            . "URL: {$target->url}\n"
            . "Status: " . ($result['status_code'] ?: 'No Response') . "\n"
            . "Response Time: {$result['response_time']}s\n"
            . "Error: " . ($result['error'] ?? 'None') . "\n"
            . "Checked At: " . now()->format('Y-m-d H:i:s') . "\n"
            . "---\n"
            . "This is an automated alert from Website Monitor.";

        // Send to PIC
        $recipients = [];

        if ($target->pic_user_id) {
            $recipients[] = $target->pic_user_id;
        }

        // Also send to admin notify list
        if (!empty($settings['notify_admin_ids'])) {
            $adminIds = array_map('trim', explode(',', $settings['notify_admin_ids']));
            $recipients = array_merge($recipients, $adminIds);
        }

        $recipients = array_unique(array_filter($recipients));

        foreach ($recipients as $userId) {
            if (!User::find($userId)) continue;

            foreach ($alertMethods as $method) {
                $method = trim($method);

                if ($method === 'message') {
                    $this->sendInternalMessage($userId, $subject, $messageText);
                }

                if ($method === 'email' && !empty($settings['mail_enabled'])) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw($messageText, function ($msg) use ($userId, $subject) {
                            $user = User::find($userId);
                            if ($user) {
                                $msg->to($user->email)->subject($subject);
                            }
                        });
                    } catch (\Exception $e) {
                        Log::error("WebsiteMonitor: Failed to send email to user {$userId}: " . $e->getMessage());
                    }
                }
            }

            // Log alert
            MonitorAlert::create([
                'monitor_target_id' => $target->id,
                'alert_type' => implode(',', $alertMethods),
                'sent_to_user_id' => $userId,
                'subject' => $subject,
                'message' => $messageText,
                'sent_at' => now(),
            ]);
        }
    }

    protected function sendInternalMessage(int $userId, string $subject, string $body): void
    {
        try {
            $senderId = 1; // System user (admin)
            $conversation = Conversation::firstOrCreate(
                [
                    'type' => 'direct',
                    'created_by' => $senderId,
                ],
                [
                    'title' => $subject,
                    'created_by' => $senderId,
                ]
            );

            // Ensure participant
            $conversation->participants()->firstOrCreate(['user_id' => $userId]);
            $conversation->participants()->firstOrCreate(['user_id' => $senderId]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'receiver_id' => $userId,
                'content' => $body,
                'type' => 'text',
            ]);
        } catch (\Exception $e) {
            Log::error("WebsiteMonitor: Failed to send internal message to user {$userId}: " . $e->getMessage());
        }
    }

    // ─── Settings Helper ──────────────────────────────────────────

    protected function getSettings(): array
    {
        $defaults = [
            'default_check_interval' => '5',
            'default_timeout' => '10',
            'alert_global_enabled' => '1',
            'default_alert_methods' => 'message',
            'notify_admin_ids' => '',
            'auto_resolve_after' => '60',
            'retain_log_days' => '30',
            'mail_enabled' => '0',
        ];

        $settings = ModuleSetting::where('module', 'websitemonitor')->pluck('value', 'key')->toArray();

        return array_merge($defaults, $settings);
    }
}
