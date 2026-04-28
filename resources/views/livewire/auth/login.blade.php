<div class="flex flex-col gap-6 p-4 border-1 rounded-lg">
    
    <x-auth-header :title="__('Login')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
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
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    <div class="flex items-center justify-center gap-2 border-t border-neutral-100 bg-gray-50/60 px-6 py-3
        text-xs text-gray-500 dark:border-zinc-700 dark:bg-zinc-900/30 dark:text-zinc-400">
        
        <span class="text-[#e2a542] text-lg">&copy;</span>
        
        Diseñado y desarrollado por 
        <a href="https://uniguajira.edu.co" target="_blank" class="text-[#e2a542] hover:underline">
            Semillero SIIS2 - Universidad de La Guajira
        </a>
    </div>

    {{-- @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif --}}
</div>
