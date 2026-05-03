<div class="flex flex-col gap-5 px-6 py-7">
    <style>
        /* Aparición escalonada — sutil */
        .row-in { animation: row-in 600ms cubic-bezier(0.16, 1, 0.3, 1) both; }
        .row-in.d1 { animation-delay: 120ms; }
        .row-in.d2 { animation-delay: 200ms; }
        .row-in.d3 { animation-delay: 280ms; }
        .row-in.d4 { animation-delay: 360ms; }
        .row-in.d5 { animation-delay: 440ms; }
        @keyframes row-in {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Botón principal — sólido, sobrio */
        .btn-primary {
            position: relative;
            background: #ad3728;
            color: #fff;
            font-weight: 500;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: background 200ms ease, transform 150ms ease;
            cursor: pointer;
        }
        .btn-primary:hover { background: #c44030; }
        .btn-primary:active { transform: translateY(1px); }
        .btn-primary:disabled { opacity: 0.7; cursor: progress; }

        .btn-spinner {
            width: 1rem; height: 1rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 700ms linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <!-- Header -->
    <div class="row-in d1 flex w-full flex-col text-center">
        <h1 class="text-2xl font-semibold text-white tracking-tight">¡Bienvenido de vuelta!</h1>
        <p class="text-sm text-white/55 mt-1">{{ __('Enter your email and password below to log in') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-4">

        <!-- Email -->
        <div class="row-in d2">
            <flux:input
                wire:model="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />
        </div>

        <!-- Contraseña -->
        <div class="row-in d3 relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-xs" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Recuérdame -->
        <div class="row-in d4">
            <flux:checkbox wire:model="remember" :label="__('Remember me')" />
        </div>

        <!-- Botón -->
        <div class="row-in d5 mt-1">
            <button type="submit"
                    class="btn-primary w-full flex items-center justify-center gap-2"
                    wire:loading.attr="disabled"
                    wire:target="login">

                <span wire:loading.remove wire:target="login">
                    {{ __('Log in') }}
                </span>

                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <span class="btn-spinner"></span>
                    Verificando…
                </span>
            </button>
        </div>
    </form>

    <!-- Footer de créditos — texto plano, sin caja -->
    <div class="text-center text-[11px] text-white/40 pt-1">
        <span class="text-white/55">&copy;</span>
        Diseñado y desarrollado por
        <a href="https://uniguajira.edu.co" target="_blank" class="text-white/70 hover:text-white underline-offset-2 hover:underline">
            Semillero SIIS2 — Universidad de La Guajira
        </a>
    </div>
</div>
