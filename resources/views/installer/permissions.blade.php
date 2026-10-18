@extends('installer.layout', ['step' => 3])

@section('title', 'Directory Permissions')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Step 3: Directory Permissions</h2>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Checking write permissions for critical application storage and cache folders.
        </p>
    </div>

    <!-- Folders List -->
    <div class="space-y-2.5">
        @foreach($results as $folder => $info)
            <div class="p-3.5 rounded-xl bg-slate-800/50 border {{ $info['is_writable'] ? 'border-slate-700/60' : 'border-rose-500/50 bg-rose-950/20' }} flex items-center justify-between">
                <div>
                    <span class="text-xs font-mono font-bold text-slate-200">{{ $folder }}</span>
                    <p class="text-[11px] text-slate-500 font-mono">{{ $info['path'] }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[11px] font-black {{ $info['is_writable'] ? 'bg-teal-500/20 text-teal-400 border border-teal-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                    {{ $info['is_writable'] ? 'Writable ✓' : 'Not Writable ✕' }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Actions -->
    <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
        <a href="{{ route('installer.requirements') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
            &larr; Back
        </a>

        @if($allWritable)
            <a href="{{ route('installer.environment') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-extrabold text-sm transition-all shadow-lg shadow-teal-500/25">
                <span>Configure Database</span>
                <span>&rarr;</span>
            </a>
        @else
            <div class="text-right">
                <p class="text-xs text-rose-400 font-semibold mb-1">Set directory permissions to 775 or 777 to continue.</p>
                <a href="{{ route('installer.permissions') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs">
                    Re-check Permissions
                </a>
            </div>
        @endif
    </div>
</div>
@endsection