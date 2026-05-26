<?php

// src\Controllers\FileHostingController.php
namespace App\Modules\FileHosting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Modules\FileHosting\Services\FileHostingService;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\FileStat;

class FileHostingController extends Controller
{
    protected FileHostingService $service;

    public function __construct(FileHostingService $service)
    {
        $this->service = $service;
    }

    /** Dashboard: list all files */
    public function index()
    {
    // Fetch all files with uploader relationship, newest first
    $files = File::with('uploader')
                 ->orderBy('created_at', 'desc')
                 ->get();

    return view('filehosting::index', compact('files'));
    }

    /** Show upload form */
    public function showUploadForm()
    {
        return view('filehosting::upload');
    }

    /** Handle file upload */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        // Ensure folder exists
        $folder = 'filehosting/files';
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0755, true);
        }

        // Upload the file
        $file = $this->service->uploadFile($request->file('file'), auth()->id());

        // Log upload action
        if (\Schema::hasTable('file_stats')) {
            FileStat::create([
                'file_id' => $file->id,
                'user_id' => auth()->id(),
                'action' => 'upload',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect()
            ->route('filehosting.upload')
            ->with('success', 'File uploaded successfully!');
    }

    /** View / open file inline */
    public function view(File $file)
    {
        $this->logStat($file, 'view');

        $fileName = $file->stored_name ?: basename($file->path);
        $relativePath = 'filehosting/files/' . $fileName;

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, "File '{$file->name}' not found.");
        }

        $fullPath = Storage::disk('public')->path($relativePath);

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . $file->name . '"',
        ]);
    }

    /** Download file */
    public function download(File $file)
    {
        $this->logStat($file, 'download');

        $fileName = $file->stored_name ?: basename($file->path);
        $relativePath = 'filehosting/files/' . $fileName;

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, "File '{$file->name}' not found.");
        }

        return Storage::disk('public')->download($relativePath, $file->name);
    }

    /** Delete file */
    public function delete(File $file)
    {
        $this->service->deleteFile($file);

        return redirect()->back()->with('success', 'File deleted successfully!');
    }

    /** Centralized statistic logger */
    protected function logStat(File $file, string $action)
    {
        FileStat::create([
            'file_id' => $file->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
