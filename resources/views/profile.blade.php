@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Profile') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- LEFT COLUMN: Profile Card --}}
        <div class="col-lg-4 order-lg-2">

            {{-- Profile Summary Card --}}
            <div class="card shadow mb-4">
                <div class="card-profile-image mt-4 text-center">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                             class="rounded-circle avatar font-weight-bold" 
                             style="height: 180px; width: 180px; object-fit: cover;"
                             alt="{{ Auth::user()->fullName }}">
                    @else
                        <figure class="rounded-circle avatar avatar font-weight-bold" 
                                style="font-size: 60px; height: 180px; width: 180px; background: #4e73df; color: white; display: inline-flex; align-items: center; justify-content: center;" 
                                data-initial="{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}">
                        </figure>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <h5 class="font-weight-bold">
                                    {{ Auth::user()->salutation ? Auth::user()->salutation . ' ' : '' }}
                                    {{ Auth::user()->fullName }}
                                </h5>
                                <p class="text-primary font-weight-bold">
                                    {{ Auth::user()->professional_title ?? Auth::user()->job_title ?? 'Administrator' }}
                                </p>
                                @if(Auth::user()->specialization)
                                    <p class="text-muted small">{{ Auth::user()->specialization }}</p>
                                @endif
                                
                                {{-- Profile Completeness Badge --}}
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Profile Complete</span>
                                        <span class="small font-weight-bold">{{ Auth::user()->profile_completeness ?? 0 }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ Auth::user()->profile_completeness ?? 0 }}%" 
                                             aria-valuenow="{{ Auth::user()->profile_completeness ?? 0 }}" 
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Quick Stats --}}
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="card-profile-stats">
                                <span class="heading">{{ number_format(Auth::user()->created_at->diffInDays(now()), 0) }}</span>
                                <span class="description">Days Active</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card-profile-stats">
                                <span class="heading">{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->diffForHumans() : 'Never' }}</span>
                                <span class="description">Last Login</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card-profile-stats">
                                <span class="heading">{{ strtoupper(Auth::user()->preferred_language ?? 'EN') }}</span>
                                <span class="description">Language</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Info Card --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-envelope mr-2 text-primary"></i> Primary Email</span>
                            <span class="text-muted">{{ Auth::user()->email }}</span>
                        </li>
                        @if(Auth::user()->secondary_email)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-envelope-open mr-2 text-secondary"></i> Secondary Email</span>
                            <span class="text-muted">{{ Auth::user()->secondary_email }}</span>
                        </li>
                        @endif
                        @if(Auth::user()->phone)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-phone mr-2 text-success"></i> Phone</span>
                            <span class="text-muted">{{ Auth::user()->phone }} {{ Auth::user()->phone_extension ? 'ext. ' . Auth::user()->phone_extension : '' }}</span>
                        </li>
                        @endif
                        @if(Auth::user()->mobile_phone)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-mobile-alt mr-2 text-info"></i> Mobile</span>
                            <span class="text-muted">{{ Auth::user()->mobile_phone }}</span>
                        </li>
                        @endif
                        @if(Auth::user()->fax)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-fax mr-2 text-warning"></i> Fax</span>
                            <span class="text-muted">{{ Auth::user()->fax }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Professional Credentials Card --}}
            @if(Auth::user()->mmc_reg_no || Auth::user()->other_reg_no || Auth::user()->key_credentials)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Professional Credentials</h6>
                </div>
                <div class="card-body">
                    @if(Auth::user()->mmc_reg_no)
                    <div class="mb-3">
                        <small class="text-muted d-block">MMC Registration No</small>
                        <span class="font-weight-bold">{{ Auth::user()->mmc_reg_no }}</span>
                        @if(Auth::user()->mmc_reg_expiry)
                            <span class="badge {{ Auth::user()->mmc_reg_expiry->isPast() ? 'badge-danger' : (Auth::user()->mmc_reg_expiry->diffInDays(now()) < 30 ? 'badge-warning' : 'badge-success') }} ml-2">
                                {{ Auth::user()->mmc_reg_expiry->isPast() ? 'Expired' : 'Valid until ' . Auth::user()->mmc_reg_expiry->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    @endif
                    
                    @if(Auth::user()->other_reg_no)
                    <div class="mb-3">
                        <small class="text-muted d-block">Other Registration No</small>
                        <span class="font-weight-bold">{{ Auth::user()->other_reg_no }}</span>
                        @if(Auth::user()->other_reg_expiry)
                            <span class="badge {{ Auth::user()->other_reg_expiry->isPast() ? 'badge-danger' : (Auth::user()->other_reg_expiry->diffInDays(now()) < 30 ? 'badge-warning' : 'badge-success') }} ml-2">
                                {{ Auth::user()->other_reg_expiry->isPast() ? 'Expired' : 'Valid until ' . Auth::user()->other_reg_expiry->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    @endif
                    
                    @if(Auth::user()->key_credentials)
                    <div class="mb-3">
                        <small class="text-muted d-block">Key Credentials</small>
                        <p class="small">{{ Auth::user()->key_credentials }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN: Edit Forms --}}
        <div class="col-lg-8 order-lg-1">

            {{-- Main Profile Form --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
                    <span class="badge badge-info">{{ Auth::user()->timezone ?? 'UTC' }}</span>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('profile.update') }}" autocomplete="off" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- SECTION 1: PERSONAL INFORMATION --}}
                        <h6 class="heading-small text-muted mb-4">
                            <i class="fas fa-user mr-2"></i>Personal Information
                        </h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                {{-- Salutation --}}
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-control-label" for="salutation">Title</label>
                                        <select id="salutation" class="form-control" name="salutation">
                                            <option value="">-</option>
                                            <option value="Mr." {{ old('salutation', Auth::user()->salutation) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                            <option value="Mrs." {{ old('salutation', Auth::user()->salutation) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                            <option value="Ms." {{ old('salutation', Auth::user()->salutation) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                            <option value="Dr." {{ old('salutation', Auth::user()->salutation) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                                            <option value="Prof." {{ old('salutation', Auth::user()->salutation) == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                                            <option value="Dato" {{ old('salutation', Auth::user()->salutation) == 'Dato' ? 'selected' : '' }}>Dato</option>
                                            <option value="Datin" {{ old('salutation', Auth::user()->salutation) == 'Datin' ? 'selected' : '' }}>Datin</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- First Name --}}
                                <div class="col-lg-5">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" placeholder="First name" 
                                               value="{{ old('name', Auth::user()->name) }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                {{-- Last Name --}}
                                <div class="col-lg-5">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                               name="last_name" placeholder="Last name" 
                                               value="{{ old('last_name', Auth::user()->last_name) }}">
                                        @error('last_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Professional Title & Job Title --}}
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="professional_title">Professional Title</label>
                                        <input type="text" id="professional_title" class="form-control" 
                                               name="professional_title" placeholder="e.g. Consultant Surgeon" 
                                               value="{{ old('professional_title', Auth::user()->professional_title) }}">
                                        <small class="form-text text-muted">Doctor, Engineer, Consultant, etc.</small>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="job_title">Job Title</label>
                                        <input type="text" id="job_title" class="form-control" 
                                               name="job_title" placeholder="e.g. Head of Department" 
                                               value="{{ old('job_title', Auth::user()->job_title) }}">
                                        <small class="form-text text-muted">Position in organization</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Bio --}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label" for="bio">Biography</label>
                                        <textarea id="bio" class="form-control" name="bio" rows="3" 
                                                  placeholder="Brief description about yourself...">{{ old('bio', Auth::user()->bio) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Avatar Upload --}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label" for="avatar">Profile Photo</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/*">
                                            <label class="custom-file-label" for="avatar">Choose file...</label>
                                        </div>
                                        <small class="form-text text-muted">Recommended: 400x400px, max 2MB</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- SECTION 2: CONTACT INFORMATION --}}
                        <h6 class="heading-small text-muted mb-4">
                            <i class="fas fa-address-card mr-2"></i>Contact Information
                        </h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                {{-- Primary Email --}}
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="email">Primary Email <span class="text-danger">*</span></label>
                                        <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" placeholder="work@organization.com" 
                                               value="{{ old('email', Auth::user()->email) }}" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                {{-- Secondary Email --}}
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="secondary_email">Secondary Email</label>
                                        <input type="email" id="secondary_email" class="form-control @error('secondary_email') is-invalid @enderror" 
                                               name="secondary_email" placeholder="personal@gmail.com" 
                                               value="{{ old('secondary_email', Auth::user()->secondary_email) }}">
                                        <small class="form-text text-muted">Personal email for notifications</small>
                                        @error('secondary_email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Phone --}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="phone">Office Phone</label>
                                        <input type="tel" id="phone" class="form-control" 
                                               name="phone" placeholder="+60 3-1234 5678" 
                                               value="{{ old('phone', Auth::user()->phone) }}">
                                    </div>
                                </div>
                                {{-- Extension --}}
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-control-label" for="phone_extension">Ext.</label>
                                        <input type="text" id="phone_extension" class="form-control" 
                                               name="phone_extension" placeholder="123" 
                                               value="{{ old('phone_extension', Auth::user()->phone_extension) }}">
                                    </div>
                                </div>
                                {{-- Mobile --}}
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label" for="mobile_phone">Mobile Phone</label>
                                        <input type="tel" id="mobile_phone" class="form-control" 
                                               name="mobile_phone" placeholder="+60 12-345 6789" 
                                               value="{{ old('mobile_phone', Auth::user()->mobile_phone) }}">
                                    </div>
                                </div>
                                {{-- Fax --}}
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label" for="fax">Fax</label>
                                        <input type="tel" id="fax" class="form-control" 
                                               name="fax" placeholder="+60 3-1234 5679" 
                                               value="{{ old('fax', Auth::user()->fax) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- SECTION 3: PROFESSIONAL INFORMATION --}}
                        <h6 class="heading-small text-muted mb-4">
                            <i class="fas fa-certificate mr-2"></i>Professional Information
                        </h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                {{-- Specialization --}}
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="specialization">Specialization</label>
                                        <input type="text" id="specialization" class="form-control" 
                                               name="specialization" placeholder="e.g. Cardiology, Orthopedics" 
                                               value="{{ old('specialization', Auth::user()->specialization) }}">
                                    </div>
                                </div>
                                {{-- EIN/Employee ID --}}
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="ein">Employee ID (EIN)</label>
                                        <input type="text" id="ein" class="form-control" 
                                               name="ein" placeholder="Employee identification number" 
                                               value="{{ old('ein', Auth::user()->ein) }}" readonly>
                                        <small class="form-text text-muted">Contact HR to update</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Registration Numbers --}}
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="mmc_reg_no">MMC Registration No</label>
                                        <input type="text" id="mmc_reg_no" class="form-control @error('mmc_reg_no') is-invalid @enderror" 
                                               name="mmc_reg_no" placeholder="Medical Council Reg. No" 
                                               value="{{ old('mmc_reg_no', Auth::user()->mmc_reg_no) }}">
                                        @error('mmc_reg_no')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="mmc_reg_expiry">MMC Expiry Date</label>
                                        <input type="date" id="mmc_reg_expiry" class="form-control" 
                                               name="mmc_reg_expiry" 
                                               value="{{ old('mmc_reg_expiry', Auth::user()->mmc_reg_expiry ? Auth::user()->mmc_reg_expiry->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="other_reg_no">Other Registration No</label>
                                        <input type="text" id="other_reg_no" class="form-control @error('other_reg_no') is-invalid @enderror" 
                                               name="other_reg_no" placeholder="Other professional reg. no" 
                                               value="{{ old('other_reg_no', Auth::user()->other_reg_no) }}">
                                        @error('other_reg_no')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="other_reg_expiry">Other Expiry Date</label>
                                        <input type="date" id="other_reg_expiry" class="form-control" 
                                               name="other_reg_expiry" 
                                               value="{{ old('other_reg_expiry', Auth::user()->other_reg_expiry ? Auth::user()->other_reg_expiry->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Key Credentials --}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label" for="key_credentials">Key Credentials</label>
                                        <textarea id="key_credentials" class="form-control" name="key_credentials" rows="3" 
                                                  placeholder="List your professional licenses, certifications, degrees...">{{ old('key_credentials', Auth::user()->key_credentials) }}</textarea>
                                        <small class="form-text text-muted">Separate multiple credentials with commas or new lines</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- SECTION 4: PREFERENCES --}}
                        <h6 class="heading-small text-muted mb-4">
                            <i class="fas fa-cog mr-2"></i>Preferences
                        </h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                {{-- Language --}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="preferred_language">Preferred Language</label>
                                        <select id="preferred_language" class="form-control" name="preferred_language">
                                            <option value="en" {{ old('preferred_language', Auth::user()->preferred_language ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                            <option value="ms" {{ old('preferred_language', Auth::user()->preferred_language ?? 'en') == 'ms' ? 'selected' : '' }}>Bahasa Melayu</option>
                                            <option value="zh" {{ old('preferred_language', Auth::user()->preferred_language ?? 'en') == 'zh' ? 'selected' : '' }}>中文 (Chinese)</option>
                                            <option value="ta" {{ old('preferred_language', Auth::user()->preferred_language ?? 'en') == 'ta' ? 'selected' : '' }}>தமிழ் (Tamil)</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Timezone --}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="timezone">Timezone</label>
                                        <select id="timezone" class="form-control" name="timezone">
                                            @php
                                                $timezones = [
                                                    'UTC' => 'UTC',
                                                    'Asia/Kuala_Lumpur' => 'Kuala Lumpur (GMT+8)',
                                                    'Asia/Singapore' => 'Singapore (GMT+8)',
                                                    'Asia/Jakarta' => 'Jakarta (GMT+7)',
                                                    'Asia/Bangkok' => 'Bangkok (GMT+7)',
                                                    'Asia/Manila' => 'Manila (GMT+8)',
                                                    'Asia/Hong_Kong' => 'Hong Kong (GMT+8)',
                                                    'Asia/Tokyo' => 'Tokyo (GMT+9)',
                                                    'Australia/Sydney' => 'Sydney (GMT+10/11)',
                                                    'Europe/London' => 'London (GMT/ BST)',
                                                    'America/New_York' => 'New York (EST/EDT)',
                                                    'America/Los_Angeles' => 'Los Angeles (PST/PDT)',
                                                ];
                                            @endphp
                                            @foreach($timezones as $value => $label)
                                                <option value="{{ $value }}" {{ old('timezone', Auth::user()->timezone ?? 'UTC') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- Department (Read-only or editable based on permissions) --}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="department">Department</label>
                                        <input type="text" id="department" class="form-control" 
                                               value="{{ Auth::user()->department }}" readonly>
                                        <small class="form-text text-muted">Contact HR to change department</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Two Factor Toggle --}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="two_factor_enabled" 
                                                   name="two_factor_enabled" value="1"
                                                   {{ old('two_factor_enabled', Auth::user()->two_factor_enabled) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="two_factor_enabled">
                                                Enable Two-Factor Authentication
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Requires authenticator app setup</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- SECTION 5: SECURITY --}}
                        <h6 class="heading-small text-muted mb-4">
                            <i class="fas fa-lock mr-2"></i>Change Password
                        </h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="current_password">Current Password</label>
                                        <input type="password" id="current_password" class="form-control" 
                                               name="current_password" placeholder="Enter current password">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="new_password">New Password</label>
                                        <input type="password" id="new_password" class="form-control" 
                                               name="new_password" placeholder="New password">
                                        <small class="form-text text-muted">Min 8 characters</small>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="confirm_password">Confirm Password</label>
                                        <input type="password" id="confirm_password" class="form-control" 
                                               name="password_confirmation" placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pl-lg-4 mt-4">
                            <div class="row">
                                <div class="col text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                    <a href="{{ route('home') }}" class="btn btn-secondary btn-lg ml-2">Cancel</a>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script>
    // Show filename in custom file input
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush