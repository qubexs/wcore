<?php

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
        $this->middleware('can:view files')->only('index');
        $this->middleware('can:upload files')->only('upload');
        $this->middleware('can:delete files')->only('delete');
    }

    public function index()
    {
        $files = File::all();
        return view('filehosting.index', compact('files'));
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']); // 10MB max
        $this->service->uploadFile($request->file('file'), auth()->id());
        return redirect()->back()->with('success', 'File uploaded!');
    }

    public function delete(File $file)
    {
        $this->service->deleteFile($file);
        return redirect()->back()->with('success', 'File deleted!');
    }
}
