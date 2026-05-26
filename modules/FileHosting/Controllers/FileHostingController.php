<?php

// modules\filehosting\app\Controllers\FileHostingController.php
namespace App\Modules\FileHosting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Models\FileStat;

class FileHostingController extends Controller
{
    // -------------------------------------------------------------------------
    // FIX #4: Removed service constructor injection — store() now handles
    //         all column mapping directly so nothing gets missed.
    // -------------------------------------------------------------------------

    /**
     * Dashboard: list all files (all.blade.php)
     * FIX #5: was File::with('uploader') but blade uses $file->owner
     */
    public function index()
    {
        $files = File::with(['owner', 'folder'])
                     ->orderByRaw('folder_id IS NULL')   // folders-first sort
                     ->orderBy('folder_id')
                     ->orderBy('name')
                     ->paginate(30);

        return view('filehosting::all', compact('files'));
    }

    /**
     * Show upload form + files tab  (upload.blade.php)
     * FIX #1: was returning view without $files or $folders — blade crashed
     *         on the "files" tab because $files was undefined.
     */
    public function showUploadForm()
    {
        $files = File::with(['owner', 'folder'])
                     ->orderBy('created_at', 'desc')
                     ->paginate(30);

        $folders = Folder::orderBy('name')->get();

        return view('filehosting::upload', compact('files', 'folders'));
    }

    /**
     * Handle file upload (POST filehosting.files.store)
     * FIX #2: method was named upload() but route calls store()
     * FIX #3: service->uploadFile() never set `path` → SQLSTATE 1364
     *         Now all columns are set explicitly right here.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file'       => 'required|file|max:512000', // 500 MB
            'folder_id'  => 'nullable|exists:folders,id',
            'visibility' => 'nullable|in:public,private,restricted',
            'expires_at' => 'nullable|date',
        ]);

        $uploaded   = $request->file('file');
        $storedName = Str::uuid() . '.' . $uploaded->getClientOriginalExtension();
        $directory  = 'uploads/' . now()->format('Y/m');
        $filePath   = $uploaded->storeAs($directory, $storedName, 'public');

        // ✅ All NOT NULL columns are explicitly set — no more 1364 errors
        $file = File::create([
            'name'          => $uploaded->getClientOriginalName(),
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_name'   => $storedName,
            'path'          => $filePath,   // ✅ FIX: was missing → 1364 on `path`
            'file_path'     => $filePath,   // kept for any legacy code that reads file_path
            'mime_type'     => $uploaded->getMimeType(),
            'size'          => $uploaded->getSize(),
            'extension'     => strtolower($uploaded->getClientOriginalExtension()),
            'checksum'      => hash_file('sha256', $uploaded->getRealPath()),
            'folder_id'     => $request->folder_id ?: null,
            'uploaded_by'   => auth()->id(),
            'visibility'    => $request->visibility ?? 'private',
            'description'   => $request->description,
            'expires_at'    => $request->expires_at ?: null,
        ]);

        $this->logStat($file, 'upload', $request);

        return redirect()
            ->route('filehosting.files.upload', ['tab' => 'files'])
            ->with('success', 'File "' . $file->name . '" uploaded successfully!');
    }

    /**
     * Serve file inline for preview
     */
    public function view(File $file)
    {
        $this->logStat($file, 'view');

        $relativePath = $this->resolveStoragePath($file);

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, "File '{$file->name}' not found on disk.");
        }

        return response()->file(
            Storage::disk('public')->path($relativePath),
            ['Content-Disposition' => 'inline; filename="' . $file->name . '"']
        );
    }

    /**
     * Force-download file
     */
    public function download(File $file)
    {
        $this->logStat($file, 'download');

        $relativePath = $this->resolveStoragePath($file);

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, "File '{$file->name}' not found on disk.");
        }

        return Storage::disk('public')->download($relativePath, $file->name);
    }

    /**
     * Delete file record + physical file
     * FIX #6: method was delete() but Laravel resource routes expect destroy()
     */
    public function destroy(File $file)
    {
        // Remove physical file from storage
        $relativePath = $this->resolveStoragePath($file);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $file->delete();

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the actual storage-relative path from whichever column is set.
     * Handles both old records (file_path) and new records (path).
     */
    protected function resolveStoragePath(File $file): string
    {
        // Prefer the `path` column; fall back to `file_path`; last resort: reconstruct
        $raw = $file->path ?: $file->file_path;

        if ($raw) {
            return $raw;
        }

        // Legacy fallback — reconstruct from stored_name
        return 'filehosting/files/' . ($file->stored_name ?: basename($file->name));
    }

    /**
     * FIX #7: logStat() had no guard — crashed if file_stats table absent.
     *         Now wrapped in try/catch + Schema check.
     */
    protected function logStat(File $file, string $action, ?Request $request = null)
    {
        try {
            if (!Schema::hasTable('file_stats')) {
                return;
            }

            $req = $request ?? request();

            FileStat::create([
                'file_id'    => $file->id,
                'user_id'    => auth()->id(),
                'action'     => $action,
                'ip'         => $req->ip(),
                'user_agent' => $req->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Stat logging must never break the main flow
            logger()->warning('FileHosting: stat log failed — ' . $e->getMessage());
        }
    }
}