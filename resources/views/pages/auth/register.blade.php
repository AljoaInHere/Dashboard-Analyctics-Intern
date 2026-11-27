<x-layouts::auth :title="__('Daftar Akun')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Buat Akun Baru')" :description="__('Lengkapi data di bawah ini untuk mendaftar ke SIE SMS A2P')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Nama Lengkap')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Nama lengkap Anda')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Alamat Email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="nama@tripleoneglobal.co.id"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Kata Sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Minimal 8 karakter')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Konfirmasi Kata Sandi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Ulangi kata sandi')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end pt-1">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Daftar Akun') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-slate-600 dark:text-zinc-400">
            <span>{{ __('Sudah memiliki akun?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Masuk') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
