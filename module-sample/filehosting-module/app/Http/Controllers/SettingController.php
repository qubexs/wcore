<?php

namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Services\SettingService;
use App\Modules\FileHosting\Models\FileStat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    /**
     * Show the settings page (admin only).
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->can('filehosting.manage_settings'), 403);

        $settings = $this->settingService->all();
        $maxUpload = $this->settingService->maxUploadBytes();

        // Aggregate stats for the settings dashboard
        $usageStats = [
            'total_uploads'   => FileStat::forAction(FileStat::ACTION_UPLOAD)->count(),
            'total_downloads' => FileStat::forAction(FileStat::ACTION_DOWNLOAD)->count(),
            'recent_activity' => FileStat::recent(7)->count(),
        ];

        return view('filehosting::settings', compact('settings', 'maxUpload', 'usageStats'));
    }

    /**
     * Return settings as JSON (for Vue/React frontends).
     */
    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.manage_settings'), 403);

        return response()->json([
            'settings'   => $this->settingService->all(),
            'max_upload' => $this->settingService->maxUploadBytes(),
        ]);
    }

    /**
     * Update a setting value.
     */
    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.manage_settings'), 403);

        $data = $request->validate([
            'key'   => 'required|string',
            'value' => 'required',
        ]);

        $this->settingService->set($data['key'], $data['value']);

        return response()->json(['message' => "Setting '{$data['key']}' updated."]);
    }

    /**
     * Flush the settings cache.
     */
    public function flushCache(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.manage_settings'), 403);

        $this->settingService->flush();

        return response()->json(['message' => 'Settings cache cleared.']);
    }
}
