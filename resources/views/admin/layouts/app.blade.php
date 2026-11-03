<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <img src="{{ asset('picture/logo.png') }}" alt="SafeRuas" class="hidden">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/admin-dashboard.js'])
    @stack('styles')
</head>
<body class="m-0 bg-[#f6f8fc] font-[Inter,ui-sans-serif,system-ui,sans-serif] text-[13px] text-[#17243b]">
<div class="min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-[100] flex w-[250px] flex-col overflow-y-auto border-r border-[#e8edf5] bg-white px-4 pt-[25px] pb-[15px] transition-transform duration-300 ease-in-out max-[1000px]:w-[220px] max-[800px]:w-[250px] max-[800px]:-translate-x-full data-[open]:max-[800px]:translate-x-0" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-[11px] px-[10px] pb-[30px] text-inherit no-underline">
           <span class="grid h-10 w-10 shrink-0 place-items-center">
    <img
        src="{{ asset('picture/logo.png') }}"
        alt="Logo SafeRuas"
        class="h-10 w-10 object-contain"
    >
</span>
            <span>
                <span class="block text-[19px] font-extrabold">SafeRuas</span>
                <span class="mt-[3px] block text-[9px] tracking-[1.5px] text-[#98a4b7]">Instansi</span>
            </span>
        </a>

        <div class="px-3 pb-[13px] text-[9px] font-extrabold tracking-[1.3px] text-[#9aa6b8]">MENU UTAMA</div>
        <nav class="grid gap-1.5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-[13px] rounded-[10px] p-[13px] text-xs font-semibold no-underline {{ request()->routeIs('admin.dashboard') ? 'bg-[#eff6ff] text-[#2563eb] shadow-[inset_3px_0_#2563eb]' : 'text-[#77859a] hover:bg-[#eff6ff] hover:text-[#2563eb]' }}">
                <span class="w-5 text-center text-[19px]">▦</span><span>Dashboard</span>
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="flex items-center gap-[13px] rounded-[10px] p-[13px] text-xs font-semibold no-underline {{ request()->routeIs('admin.laporan.*') ? 'bg-[#eff6ff] text-[#2563eb] shadow-[inset_3px_0_#2563eb]' : 'text-[#77859a] hover:bg-[#eff6ff] hover:text-[#2563eb]' }}">
                <span class="w-5 text-center text-[19px]">◤</span><span>Manajemen Laporan</span>
            {{-- </a>
            @if (Route::has('admin.penugasan.index'))
                <a href="{{ route('admin.penugasan.index') }}" class="flex items-center gap-[13px] rounded-[10px] p-[13px] text-xs font-semibold no-underline {{ request()->routeIs('admin.penugasan.*') ? 'bg-[#eff6ff] text-[#2563eb] shadow-[inset_3px_0_#2563eb]' : 'text-[#77859a] hover:bg-[#eff6ff] hover:text-[#2563eb]' }}">
                    <span class="w-5 text-center text-[19px]">◎</span><span>Penugasan Petugas</span>
                </a>
            @endif --}}
            @if (Route::has('admin.statistik.index'))
                <a href="{{ route('admin.statistik.index') }}" class="flex items-center gap-[13px] rounded-[10px] p-[13px] text-xs font-semibold no-underline {{ request()->routeIs('admin.statistik.*') ? 'bg-[#eff6ff] text-[#2563eb] shadow-[inset_3px_0_#2563eb]' : 'text-[#77859a] hover:bg-[#eff6ff] hover:text-[#2563eb]' }}">
                    <span class="w-5 text-center text-[19px]">▥</span><span>Statistik</span>
                </a>
            @endif
        </nav>

        <div class="mt-auto">
            <div class="mb-4 flex gap-2.5 rounded-xl border border-[#e8edf5] bg-[#f5f8ff] px-[11px] py-[13px]">
                <div class="grid h-[26px] w-[26px] shrink-0 place-items-center rounded-[10px] bg-[#dce9ff] font-extrabold text-[#1d4ed8]">?</div>
                <div><strong class="text-[11px]">Pusat Bantuan</strong><p class="mt-1 mb-0 text-[10px] leading-[1.6] text-[#7d8aa0]">Kelola laporan infrastruktur dengan mudah.</p></div>
            </div>
            <div class="flex items-center gap-[9px] border-t border-[#e8edf5] px-1 pt-4">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-[10px] bg-[#dce9ff] font-extrabold text-[#1d4ed8]">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                <div class="min-w-0 flex-1">
                    <strong class="block overflow-hidden text-ellipsis whitespace-nowrap text-[10px]">{{ auth()->user()->name ?? 'Administrator' }}</strong>
                    <span class="mt-1 block text-[10px] text-[#7d8aa0]">Admin Instansi</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="flex min-h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-[9px] border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500" aria-label="Logout dari akun">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" aria-hidden="true"><path d="M10 4H4v16h6m4-13 5 5-5 5m-6-5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="ml-[250px] min-h-screen max-[1000px]:ml-[220px] max-[800px]:ml-0">
        <header class="sticky top-0 z-50 flex h-[70px] items-center justify-between border-b border-[#e8edf5] bg-white px-[34px] max-[1000px]:px-[22px] max-[800px]:h-[62px] max-[800px]:px-4">
            <div class="flex items-center">
                <button type="button" class="mr-2 hidden h-[34px] w-[34px] place-items-center rounded-[9px] border-0 bg-transparent text-[#7d8aa0] hover:bg-[#eff6ff] hover:text-[#2563eb] max-[800px]:grid" id="mobileMenu" aria-label="Buka menu">☰</button>
                <div class="flex items-center gap-2.5 text-[11px] text-[#9aa5b6]"><span>SafeRuas</span><span class="text-[#d1d8e3]">/</span><span class="font-bold text-[#17243b]">@yield('breadcrumb', 'Dashboard')</span></div>
            </div>
            <div class="flex items-center gap-[18px]">
                <div class="flex items-center gap-2 text-[10px] text-[#718097]"><span class="h-[7px] w-[7px] rounded-full bg-[#20b985]"></span>Sistem aktif</div>
                <div class="h-7 w-px bg-[#e8edf5] max-[800px]:hidden"></div>
                <div class="flex items-center gap-[9px]">
                    <div class="grid h-8 w-8 shrink-0 place-items-center rounded-[10px] bg-[#dce9ff] text-[11px] font-extrabold text-[#1d4ed8]">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                    <div class="max-[800px]:hidden"><strong class="block overflow-hidden text-ellipsis whitespace-nowrap text-[10px]">{{ auth()->user()->name ?? 'Administrator' }}</strong><span class="mt-1 block text-[10px] text-[#7d8aa0]">Admin Instansi</span></div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1600px] px-[34px] pt-[30px] pb-5 max-[1000px]:px-[22px] max-[1000px]:pt-6 max-[800px]:px-[22px] max-[500px]:px-[14px] max-[500px]:pt-5">
            @if (session('success'))
                <div class="mb-[18px] flex gap-2.5 rounded-[10px] border border-[#d2f2e3] bg-[#eafaf3] px-[14px] py-3 text-[11px] text-[#16845e]"><span>✓</span><div>{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="mb-[18px] flex gap-2.5 rounded-[10px] border border-[#ffdce0] bg-[#fff0f1] px-[14px] py-3 text-[11px] text-[#c64050]"><span>!</span><div>@foreach ($errors->all() as $error)<p class="mb-1 mt-0">{{ $error }}</p>@endforeach</div></div>
            @endif
            @yield('content')
            <footer class="flex justify-between gap-[15px] px-0.5 pt-[7px] text-[9px] text-[#a0aabd] max-[500px]:flex-col">
                <span>© {{ date('Y') }} SafeRuas</span><span>Membangun infrastruktur yang lebih baik bersama.</span>
            </footer>
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
