@extends('installer.layout', ['step' => 6])

@section('title', 'Installation Complete!')

@section('content')
<div class="text-center space-y-6">
    <div class="w-20 h-20 rounded-3xl bg-teal-500/20 border-2 border-teal-500/40 text-teal-400 flex items-center justify-center mx-auto text-4xl shadow-xl shadow-teal-500/20 animate-bounce">
        🎉
    </div>

    <div class="space-y-2">
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Installation Successfully Completed!</h2>
        <p class="text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
            Doc Helper AI is now fully configured and ready for clinical operations. The setup wizard has been permanently locked for system security.
        </p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-800/60 border border-teal-500/30 text-left space-y-3 max-w-md mx-auto">
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Environment:</span>
            <span class="text-teal-400 font-bold uppercase">Production Locked</span>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Security Key:</span>
            <span class="text-slate-200 font-mono">Generated & Cached ✓</span>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Database Schema:</span>
            <span class="text-slate-200 font-mono">Migrated & Seeded ✓</span>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Lock File:</span>
            <span class="text-emerald-400 font-mono text-[11px]">storage/installed (Active)</span>
        </div>
    </div>

    <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-black text-sm transition-all shadow-xl shadow-teal-500/30 cursor-pointer">
            <span>Proceed to Login Portal</span>
            <span>&rarr;</span>
        </a>
        <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm transition">
            Visit Landing Page
        </a>
    </div>
</div>
@endsection