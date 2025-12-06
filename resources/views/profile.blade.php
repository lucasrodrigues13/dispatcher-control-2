@extends('layouts.app')

@section('conteudo')
<style>
    .big-exclamation {
        font-size: 20px;
        font-weight: bold;
        vertical-align: middle;
        margin-left: 5px;
    }
    .profile-photo-container {
        text-align: center;
        margin-bottom: 2rem;
    }
    .profile-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #013d81;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .profile-photo:hover {
        transform: scale(1.05);
    }
    #photo-input {
        display: none;
    }
    .photo-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #28a745;
        margin-bottom: 1rem;
    }
    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }
    .logo-preview {
        max-width: 200px;
        max-height: 100px;
        object-fit: contain;
        border: 4px solid #013d81;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .logo-preview:hover {
        transform: scale(1.05);
    }
    .logo-placeholder {
        width: 200px;
        height: 100px;
        border: 4px dashed #ccc;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }
    label.btn.btn-primary {
        color: #ffffff !important;
    }
    label.btn.btn-primary:hover {
        color: #ffffff !important;
    }
</style>
<div class="container py-5">
    <h2 class="text-center mb-5">My Profile</h2>


    <div class="row justify-content-center g-4">
        {{-- Profile Photo and Basic Info --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Profile Information</h5>

                    {{-- Profile Photo --}}
                    <div class="profile-photo-container">
                        @php
                            $photoUrl = $user->photo ? asset('storage/' . $user->photo) . '?v=' . time() . '&r=' . rand(1000, 9999) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=150&background=013d81&color=fff';
                        @endphp
                        <img src="{{ $photoUrl }}" 
                             alt="Profile Photo" 
                             class="profile-photo" 
                             id="current-photo"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=150&background=013d81&color=fff'">
                        <img id="photo-preview" class="photo-preview d-none" alt="Preview">
                    </div>

                    {{-- Update Profile Form --}}
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
                        @csrf
                        @method('patch')
                        
                        {{-- Photo Upload Input - MUST be inside the form --}}
                        <div class="mb-3 text-center">
                            <label for="photo-input" class="btn btn-primary">
                                <i class="fas fa-camera me-2"></i>
                                Change Photo
                            </label>
                            <input type="file" 
                                   id="photo-input" 
                                   name="photo" 
                                   accept="image/*"
                                   class="d-none">
                            <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($user->email_verified_at)
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>Email verified
                                </small>
                            @else
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-circle me-1"></i>Email not verified
                                </small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">Member Since</label>
                            <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>

                        @if($user->isOwner() || $user->is_owner)
                        <hr class="my-4">
                        
                        {{-- Site Logo --}}
                        <div class="logo-container">
                            @php
                                $logoUrl = $user->logo ? asset('storage/' . $user->logo) . '?v=' . time() . '&r=' . rand(1000, 9999) : null;
                            @endphp
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" 
                                     alt="Site Logo" 
                                     class="logo-preview" 
                                     id="current-logo"
                                     onerror="this.onerror=null; this.style.display='none'; document.getElementById('logo-placeholder').style.display='inline-flex';">
                            @endif
                            <div class="logo-placeholder {{ $user->logo ? 'd-none' : '' }}" id="logo-placeholder">
                                <div class="text-muted">
                                    <i class="fa fa-image fa-2x mb-2 d-block"></i>
                                    <small>No logo uploaded</small>
                                </div>
                            </div>
                            <img id="logo-preview" class="logo-preview d-none" alt="Preview">
                        </div>

                        {{-- Logo Upload Input - MUST be inside the form --}}
                        <div class="mb-3 text-center">
                            <label for="logo-input" class="btn btn-primary">
                                <i class="fas fa-image me-2"></i>
                                Change Logo
                            </label>
                            <input type="file" 
                                   id="logo-input" 
                                   name="logo" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                                   class="d-none">
                            <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPG, PNG, GIF, SVG</small>
                        </div>
                        @endif

                        @if($user->dispatcher)
                            <hr class="my-4">
                            <h6 class="text-muted mb-3">Company Information</h6>
                            <div class="mb-3">
                                <label class="text-muted small">Company</label>
                                <p class="mb-0">{{ $user->dispatcher->company_name ?? 'Not specified' }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Phone</label>
                                <p class="mb-0">{{ $user->dispatcher->phone ?? 'Not specified' }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Address</label>
                                <p class="mb-0">{{ $user->dispatcher->address ?? 'Not specified' }}</p>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if(session('warning'))
                        <h5 class="card-title mb-4">Change Password <span class="text-danger big-exclamation">!</span></h5>
                    @else
                        <h5 class="card-title mb-4">Change Password</h5>
                    @endif
                    <form id="passwordForm" method="POST" action="{{ route('profile.password.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="current_password"
                                       name="current_password"
                                       type="password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password"
                                        type="button"
                                        data-target="current_password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password"
                                       name="password"
                                       type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password"
                                        type="button"
                                        data-target="password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordHelp" class="form-text text-danger d-none">
                                Password must have at least 6 characters.
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password_confirmation"
                                       name="password_confirmation"
                                       type="password"
                                       class="form-control"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password"
                                        type="button"
                                        data-target="password_confirmation">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-key me-2"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Photo preview
    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');
    const currentPhoto = document.getElementById('current-photo');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('File Too Large', 'File size must be less than 2MB', 'warning');
                } else {
                    alert('File size must be less than 2MB');
                }
                photoInput.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Invalid File Type', 'Please select an image file (JPG, PNG, GIF)', 'warning');
                } else {
                    alert('Please select an image file');
                }
                photoInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
                photoPreview.classList.remove('d-none');
                currentPhoto.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password validation - same as dispatcher creation (min 6 characters)
    const passwordRegex = /^.{6,}$/;

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordHelp = document.getElementById('passwordHelp');

        if (!passwordRegex.test(password)) {
            e.preventDefault();
            passwordHelp.classList.remove('d-none');
            document.getElementById('password').focus();
        }
    });

    // Logo preview
    const logoInput = document.getElementById('logo-input');
    const logoPreview = document.getElementById('logo-preview');
    const currentLogo = document.getElementById('current-logo');
    const logoPlaceholder = document.getElementById('logo-placeholder');

    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('File Too Large', 'File size must be less than 2MB', 'warning');
                    } else {
                        alert('File size must be less than 2MB');
                    }
                    logoInput.value = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
                if (!validTypes.includes(file.type)) {
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('Invalid File Type', 'Please select an image file (JPG, PNG, GIF, SVG)', 'warning');
                    } else {
                        alert('Please select an image file');
                    }
                    logoInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                    logoPreview.classList.remove('d-none');
                    if (currentLogo) {
                        currentLogo.style.display = 'none';
                    }
                    if (logoPlaceholder) {
                        logoPlaceholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Ensure photo is included when submitting form
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        // Check if photo was selected
        if (photoInput.files.length > 0) {
            console.log('Photo file selected:', photoInput.files[0].name, photoInput.files[0].size);
            // Photo will be included automatically via enctype="multipart/form-data"
            // No need to do anything else - FormData handles it
        } else {
            console.log('No photo file selected');
        }
        
        // Check if logo was selected
        if (logoInput && logoInput.files.length > 0) {
            console.log('Logo file selected:', logoInput.files[0].name, logoInput.files[0].size);
            // Logo will be included automatically via enctype="multipart/form-data"
        }
    });

    // Force reload images after successful profile update
    @if(session('success') || session('status') === 'profile-updated')
        setTimeout(function() {
            // Reload current page images
            const images = document.querySelectorAll('img[id^="current-photo"], img[id^="header-avatar"], img[id^="sidebar-avatar"]');
            images.forEach(function(img) {
                if (img.src && !img.src.includes('ui-avatars.com')) {
                    const url = new URL(img.src);
                    url.searchParams.set('v', Date.now());
                    url.searchParams.set('r', Math.random());
                    img.src = url.toString();
                }
            });
            
            // Also reload images in parent window if in iframe
            if (window.parent && window.parent !== window) {
                window.parent.location.reload();
            }
        }, 500);
    @endif
});
</script>

@endsection
