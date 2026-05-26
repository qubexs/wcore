<?php

namespace App\Modules\Infographic\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Infographic\Models\Infographic;
use App\Modules\Infographic\Models\InfographicStat;

class InfographicController extends Controller
{
    /** Dashboard: list all infographics */
    public function index()
    {
        $infographics = Infographic::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('infographic::index', compact('infographics'));
    }

    /** Show create form */
    public function create()
    {
        return view('infographic::create');
    }

    /** Store infographic */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string', // or json if builder
        ]);

        $infographic = Infographic::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id(),
        ]);

        $this->logStat($infographic, 'create');

        return redirect()
            ->route('infographic.index')
            ->with('success', 'Infographic created successfully!');
    }

    /** View infographic */
    public function view(Infographic $infographic)
    {
        $this->logStat($infographic, 'view');

        return view('infographic::view', compact('infographic'));
    }

    /** Edit */
    public function edit(Infographic $infographic)
    {
        return view('infographic::edit', compact('infographic'));
    }

    /** Update */
    public function update(Request $request, Infographic $infographic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $infographic->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $this->logStat($infographic, 'update');

        return redirect()
            ->route('infographic.index')
            ->with('success', 'Infographic updated successfully!');
    }

    /** Delete */
    public function destroy(Infographic $infographic)
    {
        $this->logStat($infographic, 'delete');

        $infographic->delete();

        return redirect()
            ->back()
            ->with('success', 'Infographic deleted successfully!');
    }

    /** Statistic logger */
    protected function logStat(Infographic $infographic, string $action)
    {
        if (\Schema::hasTable('infographic_stats')) {
            InfographicStat::create([
                'infographic_id' => $infographic->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}