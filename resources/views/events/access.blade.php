<x-layouts.app-nosidebar :title="__('Acceso al evento')">
    <div class="max-w-md mx-auto mt-10 p-6 border rounded-lg dark:bg-black shadow-lg">
        
        <!-- Información del evento -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold mb-2">{{ $event->title }}</h2>
            <p class="text-gray-600 dark:text-gray-300 text-sm">
                📅 {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
            </p>
            @if($event->start_time && $event->end_time)
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    🕐 {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                </p>
            @endif
            @if($event->location)
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    📍 {{ $event->location }}
                </p>
            @endif
        </div>

        <hr class="mb-6 border-gray-300 dark:border-gray-600">

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Mensajes de error -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="font-semibold">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulario de registro -->
        <form method="POST" action="{{ route('attendance.store', $event->link) }}">
            @csrf
            
            <div class="mb-6">
                <flux:input 
                    name="identification" 
                    :label="__('Número de identificación')" 
                    type="text"
                    required 
                    autofocus
                    placeholder="Ingresa tu número de identificación"
                    :value="old('identification')" />
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    * Ingresa tu número de documento tal como está registrado en el sistema.
                </p>
            </div>

            <div class="flex justify-center">
                <flux:button 
                    variant="primary" 
                    type="submit"
                    class="w-full hover:scale-105 transition-transform">
                    {{ __('Registrar asistencia') }}
                </flux:button>
            </div>
        </form>

        <!-- Información adicional -->
        <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
            <p>Si tienes problemas para registrar tu asistencia, contacta al organizador del evento.</p>
        </div>
    </div>
</x-layouts.app-nosidebar>