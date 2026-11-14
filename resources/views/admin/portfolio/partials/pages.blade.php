<section class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6" aria-labelledby="pages-heading">
    <div>
        <h3 id="pages-heading" class="text-lg font-semibold">Páginas Sobre e Contato</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Duas páginas extras no seu portfólio, com o mesmo visual definido em Aparência. A página inicial continua igual.</p>
    </div>
    <div class="studio-form-grid">
        <div class="studio-form-full">
            <x-input-label for="about" value="Sua história (página Sobre)" />
            <textarea id="about" name="about" rows="10" maxlength="10000" class="block w-full" aria-describedby="about-help" placeholder="Como você começou, o que estudou, onde trabalhou, o que te motiva e o que procura agora.">{{ old('about', $portfolio->about) }}</textarea>
            <p id="about-help" class="studio-field-help">Deixe uma linha em branco entre os parágrafos. Se ficar vazio, a página usa o resumo da apresentação. Até 10.000 caracteres.</p>
            <x-input-error :messages="$errors->get('about')" class="mt-2" />
        </div>
        <div class="studio-form-full">
            <x-input-label for="contact_intro" value="Mensagem da página Contato" />
            <textarea id="contact_intro" name="contact_intro" rows="3" maxlength="600" class="block w-full" placeholder="Ex.: Aberto a projetos freelance e vagas remotas. Respondo em até dois dias úteis.">{{ old('contact_intro', $portfolio->contact_intro) }}</textarea>
            <p class="studio-field-help">Os canais exibidos são os preenchidos em “Contato e redes”.</p>
            <x-input-error :messages="$errors->get('contact_intro')" class="mt-2" />
        </div>
    </div>
    <p class="flex flex-wrap gap-4 text-sm">
        <a class="underline" href="{{ route('admin.portfolio.preview.about') }}" target="_blank" rel="noopener">Prévia da página Sobre</a>
        <a class="underline" href="{{ route('admin.portfolio.preview.contact') }}" target="_blank" rel="noopener">Prévia da página Contato</a>
    </p>
</section>
