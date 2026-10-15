<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar projeto</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div role="status" class="p-4 bg-green-900 border border-green-500 text-white rounded-lg">{{ session('status') }}</div>
            @endif
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.projects.update', $project) }}" class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-6">
                @method('PUT')
                @include('admin.projects.partials.form')
            </form>
            <x-sharing-settings :portfolio="$project->portfolio" :project="$project" />
        </div>
    </div>
</x-app-layout>
