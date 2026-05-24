<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-gray-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-24 items-center gap-3 px-7">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-2xl font-black text-white shadow-sm">
            +
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="block text-2xl font-bold leading-tight text-primary">Clinic</a>
            <p class="text-xs text-gray-500">Management System</p>
        </div>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-sm' : 'text-gray-700 hover:bg-primary-light hover:text-gray-900' }}">
            <span class="text-lg">⌂</span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('students.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold {{ request()->routeIs('students.*') ? 'bg-primary text-white shadow-sm' : 'text-gray-700 hover:bg-primary-light hover:text-gray-900' }}">
            <span class="text-lg">♙</span>
            <span>Students</span>
        </a>
        <a href="{{ route('visits.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold {{ request()->routeIs('visits.*') ? 'bg-primary text-white shadow-sm' : 'text-gray-700 hover:bg-primary-light hover:text-gray-900' }}">
            <span class="text-lg">⊞</span>
            <span>Visits</span>
        </a>
        <a href="{{ route('medicines.index') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold {{ request()->routeIs('medicines.*') ? 'bg-primary text-white shadow-sm' : 'text-gray-700 hover:bg-primary-light hover:text-gray-900' }}">
            <span class="text-lg">▣</span>
            <span>Medicines</span>
        </a>
    </nav>

    <div class="m-4 rounded-lg border border-gray-200 bg-app-bg p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-light text-xl">?</div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Need Help?</p>
                <p class="mt-1 text-xs leading-5 text-gray-500">Visit the help center or contact support.</p>
            </div>
        </div>
        <a href="mailto:support@example.com" class="mt-4 flex h-9 items-center justify-center rounded-md border border-primary text-xs font-semibold text-primary-dark hover:bg-primary-light">
            Contact Support
        </a>
    </div>
</aside>

<div class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur lg:ml-64">
    <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-10">
        <div class="flex items-center gap-3 lg:hidden">
            <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-lg font-black text-white">+</a>
            <span class="text-lg font-bold text-primary">Clinic</span>
        </div>

        @php
            $searchRoute = request()->routeIs('students.*') ? route('students.index') : route('visits.index');
            $searchPlaceholder = request()->routeIs('students.*')
                ? 'Search student by name, ID, grade, or section...'
                : 'Search patient name, ID, or complaint...';
        @endphp
        <form method="GET" action="{{ $searchRoute }}" class="hidden w-full max-w-xl items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm md:flex">
            <span class="text-gray-400">⌕</span>
            <input name="search" value="{{ request('search') }}" class="w-full border-0 p-0 text-sm text-gray-700 placeholder:text-gray-400 focus:border-0 focus:ring-0" placeholder="{{ $searchPlaceholder }}">
        </form>

        <div class="ml-auto flex items-center gap-5">
            <div class="relative hidden sm:block">
                <span class="text-2xl text-gray-500">♧</span>
                <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">3</span>
            </div>

            <div class="flex items-center gap-3 rounded-lg px-2 py-1.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-light text-sm font-bold text-primary-dark">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="hidden text-left sm:block">
                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">Clinic Admin</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 hover:bg-app-bg hover:text-gray-900">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
