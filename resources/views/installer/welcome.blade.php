@extends('installer.layout', ['step' => 1])

@section('title', 'Welcome to Installation')

@section('content')
<div class="text-center space-y-6">
    <div class="w-16 h-16 rounded-2xl bg-teal-500/10 border border-teal-500/30 text-teal-400 flex items-center justify-center mx-auto text-3xl shadow-lg shadow-teal-500/10">
        🩺
    </div>

    <div class="space-y-2">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Welcome to Doc Helper AI</h2>
        <p class="text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
            Thank you for choosing Doc Helper AI. This automated setup wizard will help you configure your database connection, server extensions, and initial super administrator account in a few simple steps.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left pt-2">
        <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 space-y-1">
            <span class="text-xl">⚡</span>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Fast Setup</h4>
            <p class="text-[11px] text-slate-400">Automated database tables migration and initial demo seeder.</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 space-y-1">
            <span class="text-xl">🔒</span>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Secure Config</h4>
            <p class="text-[11px] text-slate-400">Generates unique encryption keys and locks wizard after install.</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 space-y-1">
            <span class="text-xl">🤖</span>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">AI Medical Copilot</h4>
            <p class="text-[11px] text-slate-400">Ready for Google Gemini & OpenAI clinical assistant keys.</p>
        </div>
    </div>

    <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
        <span class="text-xs text-slate-500 font-medium">Estimated time: ~2 minutes</span>
        <a href="{{ route('installer.requirements') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25 cursor-pointer">
            <span>Check Requirements</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
@endsection