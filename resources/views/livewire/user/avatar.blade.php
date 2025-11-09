<div class="relative inline-block flex-shrink-0">
    <!-- Avatar Display -->
    <div class="relative group">
        @if($user->avatar)
            <img class="{{ $size }} rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" 
                 src="{{ Storage::url($user->avatar) }}" 
                 alt="{{ $user->name }}">
        @else
            <div class="{{ $size }} rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-4 border-gray-200 dark:border-gray-600">
                <span class="text-[12rem] md:text-[16rem] font-bold uppercase text-gray-800 dark:text-white leading-none">
                    {{ substr($user->name, 0, 1) }}
                </span>
            </div>
        @endif

        @if($showUpload)
            <!-- Overlay hover -->
            <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="text-white text-xs font-medium">Cambiar</span>
            </div>
        @endif
    </div>

    @if($showUpload)
        <!-- Botón de cámara mejorado -->
        <div class="absolute -bottom-1 -right-1 z-10">
            <label for="avatar-{{ $user->id }}" 
                   class="flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full cursor-pointer shadow-lg border-2 border-white dark:border-gray-800 transition-all hover:scale-110">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 1H8.828a2 2 0 00-1.414.586L6.293 2.707A1 1 0 015.586 3H4zm6 9a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
            </label>
            <input type="file" 
                   wire:model="photo" 
                   accept="image/*" 
                   class="hidden" 
                   id="avatar-{{ $user->id }}">
        </div>

        <!-- Loading indicator mejorado -->
        <div wire:loading wire:target="photo" 
             class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-70 rounded-full z-20">
            <div class="flex flex-col items-center gap-2">
                <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-white text-xs">Subiendo...</span>
            </div>
        </div>
    @endif
</div>