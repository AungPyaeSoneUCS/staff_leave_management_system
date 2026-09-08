@extends('layouts.app')

@section('title', __('super_admin.create_admin'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('super_admin.create_admin') }}</h2>
        <p class="cu-muted mb-6">{{ __('super_admin.new_admin_account') }}</p>

        <form id="admin-create-form" action="{{ route('super-admin.admins.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="role" value="admin">

            <div class="mb-6">
                <label class="cu-label">{{ __('common.profile_image') }}</label>
                <div class="flex items-center gap-4 mt-2">
                    <div id="profile-image-preview" class="w-24 h-24 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" class="cu-input">
                        <p class="text-xs text-slate-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                        @error('profile_image')
                            <p class="cu-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="name" class="cu-label">{{ __('common.full_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="cu-input">
                    @error('name')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="name_mm" class="cu-label">{{ __('common.name_mm') }}</label>
                    <input type="text" name="name_mm" id="name_mm" value="{{ old('name_mm') }}" class="cu-input">
                    @error('name_mm')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="email" class="cu-label">{{ __('common.email_address') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="cu-input">
                    @error('email')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="cu-label">{{ __('common.password') }}</label>
                    <div class="flex justify-center items-center">
                        <input type="password" name="password" id="password" class="cu-input" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                        <span id="eye-open" class="px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                        <span id="eye-close" class="hidden px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                    @error('password')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="department_id" class="cu-label">{{ __('common.department') }}</label>
                    <select name="department_id" id="department_id" class="cu-select">
                        <option value="">{{ __('common.no_department') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="staff_id" class="cu-label">{{ __('common.staff_id') }}</label>
                    <div class="flex items-center">
                        <span class="cu-input w-fit inline-flex items-center px-3 rounded-l-md rounded-r-none border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm whitespace-nowrap">MOST-</span>
                        <input type="text" name="staff_id_number" id="staff_id_number" value="{{ old('staff_id') ? preg_replace('/^MOST-/', '', old('staff_id')) : '' }}" class="cu-input rounded-l-none border-l-0" placeholder="123 456">
                    </div>
                    <input type="hidden" name="staff_id" id="staff_id" value="{{ old('staff_id') }}">
                    @error('staff_id')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="position" class="cu-label">{{ __('common.position') }}</label>
                    <select name="position" id="position" class="cu-select">
                        <option value="">{{ __('common.select_position') }}</option>
                        @foreach($positions as $en => $mm)
                            <option value="{{ $en }}" {{ old('position') == $en ? 'selected' : '' }}>{{ $en }}</option>
                        @endforeach
                    </select>
                    @error('position')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="position_mm" class="cu-label">{{ __('common.position_mm') }}</label>
                    <select name="position_mm" id="position_mm" class="cu-select">
                        <option value="">{{ __('common.select_position') }}</option>
                        @foreach($positions as $en => $mm)
                            <option value="{{ $mm }}" {{ old('position_mm') == $mm ? 'selected' : '' }}>{{ $mm }}</option>
                        @endforeach
                    </select>
                    @error('position_mm')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="require_admin_approval" value="1"
                           {{ old('require_admin_approval') ? 'checked' : '' }}
                           class="cu-checkbox">
                    <span class="text-sm text-slate-700">{{ __('common.requires_admin_approval') }}</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="cu-label">{{ __('common.phone') }}</label>
                <div class="flex">
                    <input type="text" class="cu-input bg-slate-100 text-slate-500 border-r-0 rounded-r-none text-center font-mono select-none w-14 text-sm" value="09" disabled>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') ? substr(old('phone'), 2) : '' }}" class="cu-input rounded-l-none border-l-0 flex-1" minlength="7" maxlength="9" pattern="[2-9][0-9]{6,8}" title="Phone number must start with 09 followed by a digit 2-9, then 6-8 more digits (total 9-11 digits)" placeholder="2123456">
                </div>
                <p class="cu-form-error" id="phone-number-length-validation"></p>
                @error('phone')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('super-admin.admins.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('super_admin.create_admin') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const positionMap = @json($positions);
        const phoneNumber = document.getElementById('phone');
        const phoneNumberLengthValidation = document.getElementById('phone-number-length-validation');
        const openEye = document.getElementById('eye-open');
        const closeEye = document.getElementById('eye-close');
        const passwordInput = document.getElementById('password');
        const positionSelect = document.getElementById('position');
        const positionMmSelect = document.getElementById('position_mm');

        openEye.addEventListener('click', (e)=>{
            passwordInput.type = 'text';
            openEye.classList.add('hidden');
            closeEye.classList.remove('hidden');
        })

        closeEye.addEventListener('click', (e)=>{
            passwordInput.type = 'password';
            closeEye.classList.add('hidden');
            openEye.classList.remove('hidden');
        })
        if (positionSelect && positionMmSelect) {
            positionSelect.addEventListener('change', function () {
                const mm = positionMap[this.value] || '';
                positionMmSelect.value = mm;
            });

            positionMmSelect.addEventListener('change', function () {
                const en = Object.keys(positionMap).find(key => positionMap[key] === this.value) || '';
                positionSelect.value = en;
            });
        }

        const staffIdNumber = document.getElementById('staff_id_number');
        const staffIdHidden = document.getElementById('staff_id');

        if (staffIdNumber && staffIdHidden) {
            staffIdNumber.addEventListener('input', function () {
                let value = this.value.replace(/[^\d\s]/g, '');
                value = value.replace(/\s+/g, ' ').trim();
                this.value = value;
            });

            const form = staffIdNumber.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    let numberPart = staffIdNumber.value.replace(/\s/g, '');
                    staffIdHidden.value = 'MOST-' + numberPart;
                });
            }
        }

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

        if(phoneNumber){
            phoneNumber.addEventListener('input', (e)=>{
                phoneNumber.value = phoneNumber.value.replace(/[^0-9]/g, '').slice(0, 9);
                const phoneRegex = /^[2-9][0-9]{6,8}$/;
                if(phoneRegex.test(phoneNumber.value)){
                    phoneNumberLengthValidation.innerText = "";
                }else{
                    phoneNumberLengthValidation.innerText = "Phone number must start with 09 followed by a digit 2-9, then 6-8 more digits (total 9-11 digits)";
                }
            })
        }

        document.getElementById('admin-create-form').addEventListener('submit', function() {
            var phoneInput = document.getElementById('phone');
            if (phoneInput && phoneInput.value) {
                phoneInput.value = '09' + phoneInput.value.replace(/[^0-9]/g, '');
            }
        });
    </script>
@endpush
