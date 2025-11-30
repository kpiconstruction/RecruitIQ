<style>
    body { 
        background-image: url("{{ asset('images/LOGIN-SCREEN.png') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<div class="flex items-center justify-center gap-4 mb-4">
    <img src="{{ asset('images/RECRUITIQ-LOGO.png') }}" alt="RecruitIQ" class="h-12">
</div>
<x-slot name="subheading">
    {{ __('filament-panels::pages/auth/login.actions.register.before') }}
    <x-filament::link size="sm" :href="filament()->getPanel('recruitiq-candidate')->getLoginUrl()">
        sign in as candidate portal user
    </x-filament::link>
    <div class="mt-2 text-xs text-gray-500">RecruitIQ</div>
</x-slot>
