<?php

namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Models\FileStat;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_files'   => File::where('uploaded_by', $user->id)->count(),
            'total_folders' => Folder::where('uploaded_by', $user->id)->count(),
            'total_size'    => File::where('uploaded_by', $user->id)->sum('size'),
            'total_downloads' => File::where('uploaded_by', $user->id)->sum('download_count'),
        ];

        $recentFiles = File::where('uploaded_by', $user->id)
            ->with('folder')
            ->latest()
            ->limit(10)
            ->get();

        $recentActivity = FileStat::where('uploaded_by', $user->id)
            ->with(['file', 'folder'])
            ->latest()
            ->limit(15)
            ->get();

        $rootFolders = Folder::whereNull('parent_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('visibility', 'public');
            })
            ->withCount('files')
            ->orderBy('name')
            ->get();

        return view('filehosting::index', compact(
            'stats', 'recentFiles', 'recentActivity', 'rootFolders'
        ));
    }
}
