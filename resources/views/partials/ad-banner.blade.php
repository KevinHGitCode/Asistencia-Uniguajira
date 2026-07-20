{{-- Banner discreto de patrocinio en el registro público (ADR-0030).
     No bloquea el formulario: fijo abajo, pequeño y descartable. El descarte
     se recuerda por sesión del navegador (sessionStorage). --}}
@if($banner)
    <div id="ad-banner"
         class="fixed inset-x-0 bottom-0 z-40 flex justify-center px-3 pb-3 pointer-events-none"
         hidden>
        <div class="pointer-events-auto flex items-center gap-2 rounded-xl border border-neutral-200/80 bg-white/95 py-1.5 pl-2 pr-1.5 shadow-lg backdrop-blur-sm dark:border-zinc-700/80 dark:bg-zinc-900/95">
            <span class="select-none self-start text-[8px] font-semibold uppercase tracking-widest text-gray-400 dark:text-zinc-500"
                  style="writing-mode: vertical-rl; transform: rotate(180deg);">
                Publicidad
            </span>

            @if($banner->target_url)
                <a href="{{ route('banners.click', $banner) }}" target="_blank" rel="nofollow sponsored noopener"
                   aria-label="Anuncio: {{ $banner->name }}">
                    <img src="{{ route('banners.image', $banner) }}"
                         alt="{{ $banner->name }}"
                         loading="lazy" decoding="async"
                         class="h-11 max-w-[calc(100vw-7rem)] rounded-lg object-contain sm:max-w-xs" />
                </a>
            @else
                <img src="{{ route('banners.image', $banner) }}"
                     alt="{{ $banner->name }}"
                     loading="lazy" decoding="async"
                     class="h-11 max-w-[calc(100vw-7rem)] rounded-lg object-contain sm:max-w-xs" />
            @endif

            <button type="button" id="ad-banner-close"
                    class="self-start rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 cursor-pointer"
                    aria-label="Cerrar anuncio">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        (function () {
            var KEY = 'aura-ad-banner-dismissed';
            var banner = document.getElementById('ad-banner');
            if (!banner) return;

            try {
                if (sessionStorage.getItem(KEY)) return; // ya lo cerró en esta sesión
            } catch (e) { /* sessionStorage bloqueado: mostrar igual */ }

            banner.hidden = false;

            // Impresión honesta (ADR-0030 fase 2): se reporta solo cuando el
            // banner realmente se mostró. sendBeacon no bloquea la página y
            // sobrevive al cierre de la pestaña.
            var payload = new URLSearchParams({ _token: '{{ csrf_token() }}' });
            navigator.sendBeacon('{{ route('banners.impression', $banner) }}', payload);

            document.getElementById('ad-banner-close').addEventListener('click', function () {
                banner.remove();
                try { sessionStorage.setItem(KEY, '1'); } catch (e) { /* sin persistencia */ }
            });
        })();
    </script>
@endif
