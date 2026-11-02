<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Superadmin') | SafeRuas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/admin-dashboard.js'])
    @stack('styles')
</head>
<body class="m-0 overflow-x-clip bg-[#f6f8fc] font-[Inter,ui-sans-serif,system-ui,sans-serif] text-[13px] text-[#17243b]">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-[100] flex w-[250px] flex-col overflow-y-auto border-r border-[#e8edf5] bg-white px-4 pt-[25px] pb-[15px] transition-transform max-[1000px]:w-[220px] max-[800px]:w-[250px] max-[800px]:-translate-x-full data-[open]:max-[800px]:translate-x-0">
        <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3 py-3 font-extrabold text-[#172b4d] no-underline">
            <span class="grid size-10 place-items-center rounded-xl bg-[#2563eb] text-white" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" class="size-6"><path d="M3 19 8 6l4 8 4-6 5 11H3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
            </span>
            <span><span class="block text-xl">SafeRuas</span><span class="block text-[10px] font-semibold tracking-widest text-slate-400">SUPERADMIN</span></span>
        </a>
        <p class="mt-9 px-3 text-[10px] font-bold tracking-widest text-slate-400">MENU UTAMA</p>
        <nav aria-label="Menu superadmin" class="mt-3 grid gap-2">
            <a href="{{ route('superadmin.dashboard') }}" @if (request()->routeIs('superadmin.dashboard')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline {{ request()->routeIs('superadmin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Zm6-3v15m6-12v15" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                Dashboard dan peta
            </a>
            <a href="{{ route('superadmin.laporan.index') }}" @if (request()->routeIs('superadmin.laporan.index', 'superadmin.laporan.show')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline {{ request()->routeIs('superadmin.laporan.index', 'superadmin.laporan.show') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true"><path d="M7 3h10v18H5V3h2Zm1 5h6m-6 4h8m-8 4h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                Seluruh laporan
            </a>
            <a href="{{ route('superadmin.admin_instansi.index') }}" @if (request()->routeIs('superadmin.admin_instansi.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline {{ request()->routeIs('superadmin.admin_instansi.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-5 8v-4h4v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Admin Instansi
            </a>
            <a href="{{ route('superadmin.masyarakat.index') }}" @if (request()->routeIs('superadmin.masyarakat.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold no-underline {{ request()->routeIs('superadmin.masyarakat.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M15 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.6"/></svg>
                Masyarakat
            </a>
        </nav>
        <div class="mt-auto border-t border-slate-100 pt-5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 font-bold text-blue-700" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="min-w-0 flex-1"><p class="truncate text-xs font-bold">{{ auth()->user()->name }}</p><p class="mt-1 text-[11px] text-slate-400">Superadmin</p></div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="flex min-h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-[9px] border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500" aria-label="Logout dari akun">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" aria-hidden="true"><path d="M10 4H4v16h6m4-13 5 5-5 5m-6-5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>
    <div class="ml-[250px] min-h-screen min-w-0 max-[1000px]:ml-[220px] max-[800px]:ml-0">
        <header class="sticky top-0 z-50 flex h-[70px] items-center gap-3 border-b border-[#e8edf5] bg-white px-[34px] max-[1000px]:px-[22px] max-[800px]:h-[62px] max-[800px]:px-4">
            <button id="mobileMenu" type="button" class="grid size-9 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 min-[801px]:hidden" aria-label="Buka menu" aria-controls="sidebar">
                <svg viewBox="0 0 24 24" fill="none" class="size-5" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <span class="text-xs text-slate-400">SafeRuas <span class="px-2 text-slate-300">/</span> <span class="font-semibold text-slate-700">@yield('breadcrumb', 'Dashboard')</span></span>
            <span class="ml-auto rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-semibold text-blue-700">Superadmin</span>
        </header>
        <main class="mx-auto min-w-0 max-w-[1600px] px-[34px] pt-[30px] pb-5 max-[1000px]:px-[22px] max-[1000px]:pt-6 max-[500px]:px-[14px] max-[500px]:pt-5">
            @if (session('success'))
                <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
