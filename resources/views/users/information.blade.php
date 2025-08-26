<x-layouts.app :title="__('Información del usuario')">
	<nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
		<ol class="list-reset flex">
			<li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
			<li><span class="mx-2">/</span></li>
			<li class="font-bold text-gray-900 dark:text-white">Información</li>
		</ol>
	</nav>

	<div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
		<div class="flex items-center gap-6 mb-6">
			<flux:avatar src="https://unavatar.io/x/{{ $user->name }}" />
			<div>
				<flux:heading class="text-2xl font-bold">{{ $user->name }}</flux:heading>
				<p class="text-gray-600 dark:text-gray-300">{{ $user->email }}</p>
			</div>
		</div>

		<div class="mb-6">
			<flux:heading class="text-lg font-semibold mb-2">Acciones realizadas</flux:heading>
			<ul class="list-disc pl-6 text-gray-700 dark:text-gray-200">
				{{-- Ejemplo de acciones--}}
				<li>Eventos creados: <span class="font-bold">0</span></li>
				<li>Asistencias registradas: <span class="font-bold">0</span></li>
				<li>Último acceso: <span class="font-bold">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</span></li>
			</ul>
		</div>

		<div>
			<flux:heading class="text-lg font-semibold mb-2">Detalles</flux:heading>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<span class="font-semibold">ID:</span> {{ $user->id }}
				</div>
				<div>
					<span class="font-semibold">Correo:</span> {{ $user->email }}
				</div>
				<div>
					<span class="font-semibold">Fecha de creación:</span> {{ $user->created_at->format('d/m/Y H:i') }}
				</div>
				<div>
					<span class="font-semibold">Actualizado:</span> {{ $user->updated_at->format('d/m/Y H:i') }}
				</div>
			</div>
		</div>
	</div>
</x-layouts.app>