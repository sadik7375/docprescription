@extends('installer.layout', ['step' => 2])

@section('title', 'Server Requirements')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Step 2: Server Requirements Check</h2>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Verifying that your PHP version and essential web server extensions meet the minimum requirements.
        </p>
    </div>

    <!-- PHP Version -->
    <div class="p-4 rounded-2xl bg-slate-800/60 border {{ $results['php']['supported'] ? 'border-teal-500/30' : 'border-rose-500/40' }} flex items-center justify-between">
        <div>
            <h4 class="text-sm font-bold text-white">PHP Version</h4>
            <p class="text-xs text-slate-400">Required: >= {{ $results['php']['minimum'] }} (Current: <strong class="text-slate-200">{{ $results['php']['current'] }}</strong>)</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-black {{ $results['php']['supported'] ? 'bg-teal-500/20 text-teal-400 border border-teal-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
            {{ $results['php']['supported'] ? 'Passed ✓' : 'Failed ✕' }}
        </span>
    </div>

    <!-- Extensions Grid -->
    <div>
        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">PHP Extensions</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($results['extensions'] as $ext => $loaded)
                <div class="p-3 rounded-xl bg-slate-800/40 border {{ $loaded ? 'border-slate-700/60' : 'border-rose-500/50 bg-rose-950/20' }} flex items-center justify-between">
                    <span class="text-xs font-mono font-semibold {{ $loaded ? 'text-slate-200' : 'text-rose-300' }}">{{ $ext }}</span>
                    <span class="text-xs font-black {{ $loaded ? 'text-teal-400' : 'text-rose-400' }}">
                        {{ $loaded ? '✓' : '✕' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions -->
    <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
        <a href="{{ route('installer.welcome') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
            &larr; Back
        </a>

        @if($allSupported)
            <a href="{{ route('installer.permissions') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25">
                <span>Check Permissions</span>
                <span>&rarr;</span>
            </a>
        @else
            <div class="text-right">
                <p class="text-xs text-rose-400 font-semibold mb-1">Please enable the missing PHP extensions on your server to proceed.</p>
                <a href="{{ route('installer.requirements') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs">
                    Re-check
                </a>
            </div>
        @endif
    </div>
</div>
@endsection