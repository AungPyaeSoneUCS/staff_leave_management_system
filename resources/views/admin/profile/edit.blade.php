@extends('layouts.app')

@section('title', __('admin.profile'))

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('admin.profile') }}</h2>
        <p class="cu-muted mb-4">{{ __('admin.profile_subtitle') }}</p>

        <form id="admin-profile-form" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-4">
                        <div id="profile-image-preview" class="w-16 h-16 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shrink-0">
                            @if($user->profile_image)
                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label for="profile_image" class="cu-label text-xs cursor-pointer">{{ __('common.profile_image') }}</label>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="cu-input text-sm">
                            @error('profile_image')
                                <p class="cu-form-error text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wide pt-2">{{ __('common.personal_information') }}</h3>

                    <div>
                        <label for="name" class="cu-label text-xs">{{ __('common.full_name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="cu-input text-sm" required>
                        @error('name')
                            <p class="cu-form-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postion" class="cu-label text-xs">{{ __('common.position') }}</label>
                        <input type="position" value="{{ app()->getLocale() == 'my' ? $user->position_mm ?? $user->position ?? '-' : $user->position ?? '-' }}" class="cu-input text-sm" disabled>
                    </div>

                    <div>
                        <label for="email" class="cu-label text-xs">{{ __('common.email_address') }}</label>
                        <input type="email" value="{{ $user->email }}" class="cu-input text-sm" disabled>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('staff.email_cannot_change') }}</p>
                    </div>

                    <div>
                        <label class="cu-label text-xs">{{ __('common.phone') }}</label>
                        <div class="flex">
                            <input type="text" class="cu-input bg-slate-100 text-slate-500 border-r-0 rounded-r-none text-center font-mono select-none w-14 text-sm" value="09" disabled>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) ? substr(old('phone', $user->phone), 2) : '' }}" class="cu-input rounded-l-none border-l-0 flex-1 text-sm" minlength="7" maxlength="9" pattern="[2-9][0-9]{6,8}" title="Phone number must start with 09 followed by a digit 2-9, then 6-8 more digits (total 9-11 digits)" placeholder="2123456">
                        </div>
                        @error('phone')
                            <p class="cu-form-error text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wide">{{ __('admin.change_password') }}</h3>

                    <div>
                        <label for="current_password" class="cu-label text-xs">{{ __('admin.current_password') }}</label>
                        <div class="flex justify-center items-center">
                            <input type="password" name="current_password" id="current_password" class="cu-input text-sm" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                            <span id="eye-open-current_password" class="px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span id="eye-close-current_password" class="hidden px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                                                @error('current_password')
                            <p class="cu-form-error text-xs">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password" class="cu-label text-xs">{{ __('admin.new_password') }}</label>
                        <div class="flex justify-center items-center">
                            <input type="password" name="password" id="password" class="cu-input text-sm" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                            <span id="eye-open-password" class="px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span id="eye-close-password" class="hidden px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                                                @error('password')
                            <p class="cu-form-error text-xs">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="cu-label text-xs">{{ __('admin.confirm_password') }}</label>
                        <div class="flex justify-center items-center">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="cu-input text-sm" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                            <span id="eye-open-password_confirmation" class="px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                            <span id="eye-close-password_confirmation" class="hidden px-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </span>
                                                @error('password_confirmation')
                            <p class="cu-form-error text-xs">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="cu-btn-secondary text-sm py-1.5 px-4">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary text-sm py-1.5 px-4">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const profileImageInput = document.getElementById('profile_image');
        const profileImagePreview = document.getElementById('profile-image-preview');

        if (profileImageInput && profileImagePreview) {
            profileImageInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profileImagePreview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        const openEyecurrent_password = document.getElementById('eye-open-current_password');
        const closeEyecurrent_password = document.getElementById('eye-close-current_password');
        const current_passwordField = document.getElementById('current_password');
        
        if (openEyecurrent_password && closeEyecurrent_password && current_passwordField) {
            openEyecurrent_password.addEventListener('click', (e) => {
                current_passwordField.type = 'text';
                openEyecurrent_password.classList.add('hidden');
                closeEyecurrent_password.classList.remove('hidden');
            });
            
            closeEyecurrent_password.addEventListener('click', (e) => {
                current_passwordField.type = 'password';
                closeEyecurrent_password.classList.add('hidden');
                openEyecurrent_password.classList.remove('hidden');
            });
        }

        const openEyepassword = document.getElementById('eye-open-password');
        const closeEyepassword = document.getElementById('eye-close-password');
        const passwordField = document.getElementById('password');
        
        if (openEyepassword && closeEyepassword && passwordField) {
            openEyepassword.addEventListener('click', (e) => {
                passwordField.type = 'text';
                openEyepassword.classList.add('hidden');
                closeEyepassword.classList.remove('hidden');
            });
            
            closeEyepassword.addEventListener('click', (e) => {
                passwordField.type = 'password';
                closeEyepassword.classList.add('hidden');
                openEyepassword.classList.remove('hidden');
            });
        }

        const openEyepassword_confirmation = document.getElementById('eye-open-password_confirmation');
        const closeEyepassword_confirmation = document.getElementById('eye-close-password_confirmation');
        const password_confirmationField = document.getElementById('password_confirmation');
        
        if (openEyepassword_confirmation && closeEyepassword_confirmation && password_confirmationField) {
            openEyepassword_confirmation.addEventListener('click', (e) => {
                password_confirmationField.type = 'text';
                openEyepassword_confirmation.classList.add('hidden');
                closeEyepassword_confirmation.classList.remove('hidden');
            });
            
            closeEyepassword_confirmation.addEventListener('click', (e) => {
                password_confirmationField.type = 'password';
                closeEyepassword_confirmation.classList.add('hidden');
                openEyepassword_confirmation.classList.remove('hidden');
            });
        }

        document.getElementById('admin-profile-form').addEventListener('submit', function() {
            var phoneInput = document.getElementById('phone');
            if (phoneInput && phoneInput.value) {
                phoneInput.value = '09' + phoneInput.value.replace(/[^0-9]/g, '');
            }
        });
    </script>
@endpush
