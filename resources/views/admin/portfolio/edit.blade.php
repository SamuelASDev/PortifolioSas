<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações do Portfólio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-900 border border-green-500 text-white font-medium rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form method="POST" action="{{ route('admin.portfolio.update') }}" class="space-y-6 max-w-xl">
                        @csrf
                        @method('PATCH')
                        
                        <!-- Link Público -->
                        <div>
                            <label for="slug" class="block text-sm font-medium">Link do seu Portfólio</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 text-gray-500 dark:text-gray-400 sm:text-sm">
                                    {{ url('/') }}/
                                </span>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $portfolio->slug) }}" 
                                    class="block w-full flex-1 rounded-none rounded-r-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            @error('slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tema Visual -->
                        <div>
                            <label for="theme" class="block text-sm font-medium">Tema Visual</label>
                            <select id="theme" name="theme" 
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="dark" @selected(old('theme', $portfolio->theme) === 'dark')>Escuro (Dark Mode)</option>
                                <option value="light" @selected(old('theme', $portfolio->theme) === 'light')>Claro (Light Mode)</option>
                            </select>
                            <x-input-error :messages="$errors->get('theme')" class="mt-2" />
                        </div>

                        <!-- Cor Principal -->
                        <div>
                            <label for="primary_color" class="block text-sm font-medium">Cor Principal</label>
                            <div class="mt-1 flex items-center gap-3">
                                <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $portfolio->primary_color) }}" 
                                    class="h-10 w-14 rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 cursor-pointer">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Cor de destaque (botões e detalhes)</span>
                            </div>
                            <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest transition">
                                Guardar Alterações
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
