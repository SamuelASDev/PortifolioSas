<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Novo projeto</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.projects.store') }}" class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-6">
                @include('admin.projects.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>
