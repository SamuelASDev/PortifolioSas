<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Meu portfólio</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div role="status" class="p-4 bg-green-900 border border-green-500 text-white rounded-lg">{{ session('status') }}</div>
            @endif
            @include('admin.portfolio.partials.publication')
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.portfolio.update') }}" class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-6 text-gray-900 dark:text-gray-100">
                @csrf
                @method('PATCH')
                @include('admin.portfolio.partials.presentation')
                @include('admin.portfolio.partials.appearance')
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <x-primary-button>Salvar alterações</x-primary-button>
                </div>
            </form>
            @include('admin.portfolio.partials.appearance-editor')
            <x-sharing-settings :portfolio="$portfolio" />
        </div>
    </div>
</x-app-layout>
