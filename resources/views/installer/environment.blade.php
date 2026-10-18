@extends('installer.layout', ['step' => 4])

@section('title', 'Database & Environment Setup')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Step 4: Database & Application Setup</h2>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Provide your MySQL database credentials. The installer will test the connection and configure your environment.
        </p>
    </div>

    <div id="client-err-box" class="hidden p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold">
        <strong class="font-black">Connection Error:</strong> <span id="client-err-text"></span>
    </div>

    @if(isset($errors) && $errors->has('db_error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold">
            <strong class="font-black">Connection Error:</strong> {{ $errors->first('db_error') }}
        </div>
    @endif

    <form id="env-form" action="{{ route('installer.environment.save') }}" method="POST" class="space-y-5">
        @csrf

        <!-- App Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Application Name</label>
                <input type="text" name="app_name" value="{{ old('app_name', $env['app_name']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none" />
                @if(isset($errors) && $errors->has('app_name')) <p class="text-xs text-rose-400 mt-1">{{ $errors->first('app_name') }}</p> @endif
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Application URL</label>
                <input type="url" name="app_url" value="{{ old('app_url', $env['app_url']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none" />
                @if(isset($errors) && $errors->has('app_url')) <p class="text-xs text-rose-400 mt-1">{{ $errors->first('app_url') }}</p> @endif
            </div>
        </div>

        <div class="border-t border-slate-800 pt-4">
            <h4 class="text-xs font-bold text-teal-400 uppercase tracking-wider mb-3">MySQL Database Details</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 mb-1">Database Host</label>
                    <input type="text" name="db_host" value="{{ old('db_host', $env['db_host']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Port</label>
                    <input type="text" name="db_port" value="{{ old('db_port', $env['db_port']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none font-mono" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Database Name</label>
                    <input type="text" name="db_name" value="{{ old('db_name', $env['db_name']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Database Username</label>
                    <input type="text" name="db_user" value="{{ old('db_user', $env['db_user']) }}" required class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1">Database Password</label>
                    <input type="password" name="db_password" value="{{ old('db_password', $env['db_password']) }}" placeholder="Leave blank if none" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:border-teal-500 focus:outline-none font-mono" />
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
            <a href="{{ route('installer.permissions') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                &larr; Back
            </a>

            <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25 cursor-pointer disabled:cursor-not-allowed">
                <span id="btn-text">Save & Test Connection</span>
                <span id="btn-arrow">&rarr;</span>
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('env-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnArrow = document.getElementById('btn-arrow');
    const errBox = document.getElementById('client-err-box');
    const errText = document.getElementById('client-err-text');
    
    errBox.classList.add('hidden');
    btn.disabled = true;
    btn.classList.add('opacity-80');
    btnText.textContent = 'Saving & Verifying MySQL...';
    btnArrow.textContent = '⏳';

    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            window.location.href = data.redirect || "{{ route('installer.database') }}";
        } else {
            const data = await response.json().catch(() => ({}));
            let msg = data.message || 'Database connection error. Please verify your credentials.';
            if (data.errors) {
                msg = Object.values(data.errors).flat().join('<br>');
            }
            errText.innerHTML = msg;
            errBox.classList.remove('hidden');
            btn.disabled = false;
            btn.classList.remove('opacity-80');
            btnText.textContent = 'Save & Test Connection';
            btnArrow.textContent = '→';
        }
    } catch (err) {
        // Fallback for local artisan serve server restart
        setTimeout(() => {
            window.location.href = "{{ route('installer.database') }}";
        }, 1000);
    }
});
</script>
@endsection