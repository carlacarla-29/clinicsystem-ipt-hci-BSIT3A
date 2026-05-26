<x-guest-layout>
    <div class="login-card">
        <section class="login-welcome-panel" aria-label="Welcome">
            <div class="login-welcome-text">Welcome</div>

            <div class="login-illustration" aria-hidden="true">
                <div class="login-device">
                    <div class="login-device-screen">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="login-device-button"></div>
                </div>
                <div class="login-chart-card">
                    <div class="login-chart-bars">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="login-chart-line"></div>
                </div>
            </div>

            <p>Clinic Management System</p>
        </section>

        <section class="login-form-panel" aria-label="Login form">
            <div class="login-form-inner">
                <h1>LOGIN</h1>

                <x-auth-session-status :status="session('status')" />

                <form method="POST" action="{{ route('login.submit') }}" class="login-form">
                    @csrf

                    <div class="login-field">
                        <label for="email">Email</label>
                        <div class="login-input-wrap">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your email"
                            >
                            <span>@</span>
                        </div>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <div class="login-input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            <span>*</span>
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="login-options">
                        <label for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot?</a>
                        @endif
                    </div>

                    <button type="submit" class="login-submit">Login</button>

                    @if (Route::has('register'))
                        <p class="login-register">
                            No account yet?
                            <a href="{{ route('register') }}">Register</a>
                        </p>
                    @endif
                </form>
            </div>
        </section>
    </div>
</x-guest-layout>
