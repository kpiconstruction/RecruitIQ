<div class="flex items-center justify-center gap-4 mb-4">
    <img src="{{ asset('images/RECRUITIQ-LOGO.png') }}" alt="RecruitIQ" class="h-12">
    <img src="{{ asset('images/KPI-LOGO.png') }}" alt="KPI Construction Services" class="h-8">
</div>
<div class="mb-4">
    <img src="{{ asset('images/LOGIN-SCREEN.png') }}" alt="RecruitIQ Banner" class="w-full rounded-md">
    </div>
<x-slot name="subheading">
    {{ __('filament-panels::pages/auth/login.actions.register.before') }}
    <x-filament::link size="sm" :href="filament()->getPanel('recruitiq-candidate')->getLoginUrl()">
        sign in as candidate portal user
    </x-filament::link>
    <div class="mt-2 text-xs text-gray-500">Powered by KPI Construction Services</div>
</x-slot>
