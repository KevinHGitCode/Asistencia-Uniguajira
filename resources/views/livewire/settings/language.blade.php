<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout 
        :heading="__('Language')" 
        :subheading="__('Select the preferred language for the application')"
    >
        @php
            $current = str_starts_with(app()->getLocale(), 'es') ? 'es' : 'en';
        @endphp

        <form 
            method="POST" 
            action="{{ route('settings.language.switch') }}" 
            x-data="{ locale: '{{ $current }}' }"
            x-ref="form"
        >
            @csrf

            <flux:radio.group variant="segmented" x-model="locale" name="locale">
                <flux:radio value="es" icon="language">{{ __('Spanish') }}</flux:radio>
                <flux:radio value="en" icon="language">{{ __('English') }}</flux:radio>
            </flux:radio.group>

            <!-- aseguras que el valor viaje en el POST -->
            <input type="hidden" name="locale" :value="locale">

            <button type="submit" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg">
                {{ __('Save') }}
            </button>
        </form>

        <!-- Si lo quieres sin botón: descomenta la línea de abajo -->
        <!-- <script>document.addEventListener('alpine:init', () => { document.querySelector('form[x-ref=form]')?.addEventListener('change', e => e.currentTarget.submit()); });</script> -->
        {{-- <!-- O directamente: @change="$refs.form.submit()" en <flux:radio.group> --> --}}
    </x-settings.layout>
</section>
