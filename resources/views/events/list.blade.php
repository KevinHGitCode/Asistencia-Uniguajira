<x-layouts.app :title="__('List')">
    <div>
        <p class="text-2xl font-bold text-white mb-4"> Tus eventos </p>
    </div>
        
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
                <livewire:event.card title="Annual Charity Gala" date="2024-12-10" location="Grand Ballroom, City Hall" />
            </div>
        </div>
</x-layouts.app>