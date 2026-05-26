<?php
// modules/filehosting/src/Controllers/FileHostingController.php

namespace App\Modules\FileHosting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\FileHosting\Services\FileHostingService;
use App\Modules\FileHosting\Models\File;

class FileHostingController extends Controller
{
    protected FileHostingService $service;

    public function __construct(FileHostingService $service)
    {
        $this->service = $service;

        // 🔐 Permissions (must match Gate names)
        //$this->middleware('can:filehosting.view')->only('index','all' );
        //$this->middleware('can:filehosting.upload')->only(['showUploadForm', 'upload']);
        //$this->middleware('can:filehosting.all')->only('all');
        //$this->middleware('can:filehosting.delete')->only('delete');
    }

    /**
     * Main dashboard
     */
    public function index()
    {
        $files = File::all();
        return view('filehosting::index', compact('files'));
    }

    /**
     * Show upload form
     */
    public function showUploadForm()
    {
        // Fetch all files (or limit to current user if needed)
        $files = File::all(); // OR: File::where('uploaded_by', Auth::user()->email)->get();

        return view('filehosting.upload', [
            'files' => $files
        ]);
    }

    /**
     * Handle upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $this->service->uploadFile($request->file('file'), auth()->id());

        return redirect()
            ->route('filehosting.upload')
            ->with('success', 'File uploaded!');
    }

    /**
     * Show all files
     */
    public function all()
    {

     

    dd(
        'HTTP CONTEXT',
        auth()->check(),
        auth()->user()?->email,
        auth()->user()?->can('filehosting.view')
    );

    // existing code…


     //   $files = File::all();
     //   return view('filehosting::view', compact('files'));
    }

    /**
     * Delete file
     */
    public function delete(File $file)
    {
        $this->service->deleteFile($file);
        return redirect()->back()->with('success', 'File deleted!');
    }
}
