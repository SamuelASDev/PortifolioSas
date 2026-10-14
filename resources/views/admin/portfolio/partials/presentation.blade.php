<section class="space-y-6" aria-labelledby="presentation-heading">
    <div>
        <h3 id="presentation-heading" class="text-lg font-semibold">Apresentação</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Essas informações aparecem na sua página pública quando você publicar o portfólio.</p>
    </div>
    <div>
        <x-input-label for="professional_name" value="Nome profissional" />
        <x-text-input id="professional_name" name="professional_name" class="block mt-1 w-full" :value="old('professional_name', $portfolio->professional_name)" maxlength="255" autocomplete="name" />
        <x-input-error :messages="$errors->get('professional_name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="headline" value="Título profissional" />
        <x-text-input id="headline" name="headline" class="block mt-1 w-full" :value="old('headline', $portfolio->headline)" maxlength="160" placeholder="Ex.: Desenvolvedor Laravel" />
        <x-input-error :messages="$errors->get('headline')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="bio" value="Biografia curta" />
        <textarea id="bio" name="bio" rows="5" maxlength="1000" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">{{ old('bio', $portfolio->bio) }}</textarea>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Até 1000 caracteres, em texto simples.</p>
        <x-input-error :messages="$errors->get('bio')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="photo" value="Foto profissional (opcional)" />
        @if ($portfolio->photo_url)
            <img src="{{ $portfolio->photo_url }}" alt="Sua foto profissional atual" class="mt-3 h-24 w-24 rounded-full object-cover">
            <label for="remove_photo" class="mt-3 flex items-center gap-2 text-sm">
                <input type="checkbox" id="remove_photo" name="remove_photo" value="1" @checked(old('remove_photo', false)) class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500">
                Remover foto atual
            </label>
        @endif
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help" class="mt-3 block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-white">
        <p id="photo-help" class="mt-2 text-sm text-gray-500 dark:text-gray-400">JPEG, PNG ou WebP. Até 2 MB e 3000 × 3000 pixels. Se houver um erro no formulário, selecione a foto novamente.</p>
        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
        <x-input-error :messages="$errors->get('remove_photo')" class="mt-2" />
    </div>
</section>
<section class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6" aria-labelledby="contacts-heading">
    <div>
        <h3 id="contacts-heading" class="text-lg font-semibold">Contato e redes</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Todos os campos são opcionais. O e-mail abaixo será público; o e-mail da sua conta não é exibido automaticamente.</p>
    </div>
    @foreach (['contact_email' => ['E-mail de contato', 'email'], 'github_url' => ['GitHub', 'url'], 'linkedin_url' => ['LinkedIn', 'url'], 'website_url' => ['Site pessoal', 'url']] as $field => [$label, $type])
        <div>
            <x-input-label :for="$field" :value="$label" />
            <x-text-input :id="$field" :name="$field" :type="$type" class="block mt-1 w-full" :value="old($field, $portfolio->{$field})" maxlength="255" />
            <x-input-error :messages="$errors->get($field)" class="mt-2" />
        </div>
    @endforeach
</section>
