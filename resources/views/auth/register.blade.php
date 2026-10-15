<x-guest-layout>
    <p class="studio-eyebrow">O PRIMEIRO PASSO É SEU</p>
    <h1 class="studio-auth-title">Dê um novo lugar<br>ao seu trabalho.</h1>
    <p class="studio-auth-subtitle">Crie sua conta e comece a construir um portfólio com a sua identidade.</p>
    <form method="POST" action="{{ route('register') }}" class="studio-auth-form">
        @csrf
        <div><x-input-label for="name" value="Como podemos chamar você?" /><x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Seu nome" /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
        <div><x-input-label for="email" value="Seu e-mail" /><x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="voce@exemplo.com" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
        <div><x-input-label for="password" value="Crie uma senha" /><x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" minlength="8" placeholder="Pelo menos 8 caracteres" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
        <div><x-input-label for="password_confirmation" value="Confirme a senha" /><x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repita sua senha" /><x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" /></div>
        <x-primary-button class="studio-button-wide">Criar meu espaço <span aria-hidden="true">↗</span></x-primary-button>
        <p class="studio-field-help">Depois do cadastro, enviaremos um link para confirmar seu e-mail.</p>
    </form>
    <p class="studio-auth-alternative">Já faz parte?<a href="{{ route('login') }}" class="studio-text-link">Entre na sua conta</a></p>
</x-guest-layout>
