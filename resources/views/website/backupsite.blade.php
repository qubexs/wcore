@extends('layouts.admin')
 <!-- resources/views/website/backupsite.blade.php--> 
@section('main-content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-file-archive mr-2"></i> Website Backup
    </h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <p>This page is for website backup & download.</p>

            {{-- You can add backup buttons here --}}
            <form action="#" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i> Download Website Backup
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
