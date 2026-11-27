<x-layouts::auth :title="__('Masuk')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Masuk ke Akun Anda')" :description="__('Masukkan email dan kata sandi untuk mengakses Dashboard SIE SMS A2P')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Alamat Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@tripleoneglobal.co.id"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Kata Sandi')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Masukkan kata sandi')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-xs end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Lupa kata sandi?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

            <div class="flex items-center justify-end pt-1">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Masuk ke Dashboard') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-slate-600 dark:text-zinc-400">
            <span>{{ __('Belum memiliki akun?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Daftar sekarang') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
