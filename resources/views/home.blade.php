<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="container m-auto grid grid-cols-3 gap-4">
                        @forelse($activities as $activity)
                            <div>
                                <img src="{{ asset('storage' . $activity->thumbnail) }}" alt="{{ $activity->name }}">
                                <h2>
                                    <a href="#" class="text-lg font-semibold">{{ $activity->name }}</a>
                                </h2>
                                <time>{{ $activity->start_date }}</time>
                            </div>
                        @empty
                            <p>No activities</p>
                        @endforelse
                    </div>

                    <div class="mt-6">{{ $activities->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
