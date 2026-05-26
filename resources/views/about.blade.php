@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('About') }}</h1>

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <div class="card shadow mb-4">

                <div class="card-profile-image mt-4">
                    <img src="{{ asset('img/favicon.png') }}" class="rounded-circle" alt="user-image">
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-12">
                            <h5 class="font-weight-bold">{{ $settings['site_name'] ?? 'wCore' }} v1.0.0</h5>
                            <p>{{ $settings['site_name'] ?? 'wCore' }} – Modular Laravel Framework</p>

                            <p>Key features include:</p>
                            <ul>
                                <li><strong>Dynamic module management</strong> – install, activate, deactivate modules effortlessly.</li>
                                <li><strong>Role-based access control</strong> – fine-grained permissions for users and modules.</li>
                                <li><strong>Seamless module integration</strong> – build scalable, maintainable applications faster.</li>
                            </ul>

                            <p>{{ $settings['site_name'] ?? 'wCore' }} is the backbone for the main internal core App of Hospital Permaisuri Tengku Norashikin, designed to accelerate Laravel development with a robust modular architecture.</p>
                            <p>Accelerate Laravel development with a robust modular architecture, featuring dynamic menu management, role-based access control, and seamless module integration. Build scalable, maintainable applications faster and smarter</p>
                            <p>Recommend to install this preset on a project that you are starting from scratch, otherwise your project's design might break.</p>
                            <p>If you found this project useful, then please consider giving it a ⭐</p>
                            <a href="https://github.com/qubexs/wcore" target="_blank" class="btn btn-github">
                                <i class="fab fa-github fa-fw"></i> Go to repository
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-12">
                            <h5 class="font-weight-bold">Credits</h5>
                            <p>{{ $settings['site_name'] ?? 'wCore' }} uses some open-source third-party libraries/packages, many thanks to the web community.</p>
                            <ul>
                                <li><a href="https://laravel.com" target="_blank">Laravel</a> - Open source framework.</li>
                                <li><a href="https://github.com/qubexs" target="_blank">qubexs</a> - Making managing navigation in Laravel easy.</li>
                                <li><a href="https://github.com/qubexs/wcore" target="_blank">{{ $settings['site_name'] ?? 'wCore' }}</a> - Thanks to Start Bootstrap.</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
