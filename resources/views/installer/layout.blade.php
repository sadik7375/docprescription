<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Installation Wizard') - Doc Helper AI</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-between antialiased selection:bg-teal-500 selection:text-white">
    <!-- Background Gradient Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[400px] bg-teal-500/15 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-0 right-10 w-[500px] h-[300px] bg-cyan-500/10 blur-[100px] rounded-full"></div>
    </div>

    <!-- Top Navigation Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md py-4 px-6 sm:px-10 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-400 via-teal-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-teal-500/25 shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    <path d="M3.22 12H9.5l1.5-3 2 6.5 1.5-3.5h6.28"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white tracking-tight leading-tight">Doc Helper AI</h1>
                <p class="text-xs font-bold text-teal-400 tracking-wide">Next-Gen Clinical Practice OS</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 border border-slate-700 text-slate-300">
                v2.5 Production
            </span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">
        <!-- Step Indicators -->
        <div class="mb-8">
            <div class="grid grid-cols-6 gap-2 text-center text-xs font-semibold max-w-2xl mx-auto">
                @php
                    $steps = [
                        1 => ['title' => 'Welcome', 'route' => 'installer.welcome'],
                        2 => ['title' => 'Server', 'route' => 'installer.requirements'],
                        3 => ['title' => 'Permissions', 'route' => 'installer.permissions'],
                        4 => ['title' => 'Database', 'route' => 'installer.environment'],
                        5 => ['title' => 'Migration', 'route' => 'installer.database'],
                        6 => ['title' => 'Super Admin', 'route' => 'installer.admin'],
                    ];
                    $currentStep = $step ?? 1;
                @endphp

                @foreach($steps as $sNum => $sData)
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all {{ $currentStep > $sNum ? 'bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20' : ($currentStep === $sNum ? 'bg-teal-400 text-slate-950 ring-4 ring-teal-500/20 font-black' : 'bg-slate-800 text-slate-500 border border-slate-700') }}">
                            @if($currentStep > $sNum)
                                ✓
                            @else
                                {{ $sNum }}
                            @endif
                        </div>
                        <span class="hidden sm:inline text-[10px] {{ $currentStep >= $sNum ? 'text-slate-200 font-bold' : 'text-slate-500' }}">
                            {{ $sData['title'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Card Body -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs text-slate-500 border-t border-slate-900 bg-slate-950/80">
        Doc Helper AI &copy; {{ date('Y') }} • All Rights Reserved. Built for Medical Clinics & Practice Management.
    </footer>
</body>
</html>