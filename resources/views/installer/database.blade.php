@extends('installer.layout', ['step' => 5])

@section('title', 'Database Migration & Seeding')

@section('content')
<div class="text-center space-y-6">
    <div class="w-16 h-16 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-400 flex items-center justify-center mx-auto text-3xl shadow-lg shadow-teal-500/10">
        🗄️
    </div>

    <div class="space-y-2">
        <h2 class="text-2xl font-extrabold text-white tracking-tight">Step 5: Database Migration & Schema Build</h2>
        <p class="text-sm text-slate-400 max-w-lg mx-auto leading-relaxed">
            Your database connection was successfully verified! Now we will create the database tables, default clinical templates, and initial master records.
        </p>
    </div>

    @if(isset($errors) && $errors->has('migration_error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold text-left">
            <strong class="font-black">Migration Failed:</strong> {{ $errors->first('migration_error') }}
        </div>
    @endif

    <div class="p-4 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-left space-y-2 text-xs text-slate-300">
        <div class="flex items-center gap-2">
            <span class="text-teal-400 font-black">✓</span>
            <span>Generates unique 256-bit AES application security key</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-teal-400 font-black">✓</span>
            <span>Builds complete schema for Prescriptions, Patients, Chambers, Drugs, and AI Analytics</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-teal-400 font-black">✓</span>
            <span>Seeds standard clinical lookups, default medicines, and pricing plans</span>
        </div>
    </div>

    <form action="{{ route('installer.database.run') }}" method="POST" id="migrateForm">
        @csrf
        <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
            <a href="{{ route('installer.environment') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                &larr; Back
            </a>

            <button type="submit" id="submitBtn" onclick="this.disabled=true; this.innerHTML='Building Schema & Seeding (Please wait)...'; document.getElementById('migrateForm').submit();" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25 cursor-pointer">
                <span>Run Migrations & Seeds</span>
                <span>&rarr;</span>
            </button>
        </div>
    </form>
</div>
@endsection