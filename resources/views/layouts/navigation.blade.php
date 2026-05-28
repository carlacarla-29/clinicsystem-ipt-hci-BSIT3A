<aside class="main-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-mark"></div>
        <div>
            <a href="{{ route('dashboard') }}" class="sidebar-brand-title">Clinic</a>
            <p class="sidebar-brand-subtitle">Management System</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <span class="sidebar-link-icon">
                <img src="{{ asset('icons/home.png') }}" alt="Dashboard Icon" class="sidebar-icon-img">
            </span>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('students.index') }}" class="sidebar-link {{ request()->routeIs('students.*') ? 'is-active' : '' }}">
            <span class="sidebar-link-icon">
                <img src="{{ asset('icons/students.png') }}" alt="Students Icon" class="sidebar-icon-img">
            </span>
            <span>Students</span>
        </a>

        <a href="{{ route('visits.index') }}" class="sidebar-link {{ request()->routeIs('visits.*') ? 'is-active' : '' }}">
            <span class="sidebar-link-icon">
                <img src="{{ asset('icons/visits.png') }}" alt="Visits Icon" class="sidebar-icon-img">
            </span>
            <span>Visits</span>
        </a>

        <a href="{{ route('medicines.index') }}" class="sidebar-link {{ request()->routeIs('medicines.*') ? 'is-active' : '' }}">
            <span class="sidebar-link-icon">
                <img src="{{ asset('icons/medicines.png') }}" alt="Medicines Icon" class="sidebar-icon-img">
            </span>
            <span>Medicines</span>
        </a>
    </nav>
</aside>

<div class="topbar">
    <div class="topbar-inner">
        <div class="mobile-brand">
            <a href="{{ route('dashboard') }}" class="mobile-brand-mark">+</a>
            <span>Clinic</span>
        </div>

        @php
            $searchRoute = request()->routeIs('students.*') ? route('students.index') : route('visits.index');
            $searchPlaceholder = request()->routeIs('students.*')
                ? 'Search student by name, ID, grade, or section...'
                : 'Search patient name, ID, or complaint...';
        @endphp

        <form method="GET" action="{{ $searchRoute }}" class="topbar-search">
            <span>Q</span>
            <input name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}">
        </form>

        <div class="topbar-actions">
            <div class="notification-button">
                <span></span>
                <strong></strong>
            </div>

            <div class="user-menu">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="user-details">
                    <p>{{ Auth::user()->name }}</p>
                    <span>Clinic Admin</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>
