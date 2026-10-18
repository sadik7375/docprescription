@extends('installer.layout', ['step' => 6])

@section('title', 'Super Admin Account Setup')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Step 6: Setup Super Admin Account</h2>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Create the primary Super Administrator account to manage doctors, plans, AI configurations, and payment gateways.
        </p>
    </div>

    @if(isset($errors) && $errors->has('admin_error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold">
            {{ $errors->first('admin_error') }}
        </div>
    @endif

    <form action="{{ route('installer.admin.save') }}" method="POST" autocomplete="off" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Super Admin Full Name</label>
            <input type="text" name="name" value="{{ old('name', 'Super Admin') }}" required autocomplete="off" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none" />
            @if(isset($errors) && $errors->has('name')) <p class="text-xs text-rose-400 mt-1">{{ $errors->first('name') }}</p> @endif
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Super Admin Email Address</label>
            <input type="email" name="email" value="{{ old('email', 'admin@dochelper.ai') }}" required autocomplete="off" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none font-mono" />
            @if(isset($errors) && $errors->has('email')) <p class="text-xs text-rose-400 mt-1">{{ $errors->first('email') }}</p> @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <div class="relative">
                    <input type="password" id="admin_password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimum 8 characters" class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none font-mono" />
                    <button type="button" onclick="togglePassword('admin_password', 'eye_icon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-400 cursor-pointer text-xs focus:outline-none" title="Show / Hide Password">
                        <i id="eye_icon1" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @if(isset($errors) && $errors->has('password')) <p class="text-xs text-rose-400 mt-1">{{ $errors->first('password') }}</p> @endif
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="admin_password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Re-type password" class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none font-mono" />
                    <button type="button" onclick="togglePassword('admin_password_confirmation', 'eye_icon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-400 cursor-pointer text-xs focus:outline-none" title="Show / Hide Password">
                        <i id="eye_icon2" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
            <span class="text-xs text-slate-500">You can also log in as doctor later.</span>

            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25 cursor-pointer">
                <span>Create Admin & Complete</span>
                <span>&rarr;</span>
            </button>
        </div>
    </form>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection