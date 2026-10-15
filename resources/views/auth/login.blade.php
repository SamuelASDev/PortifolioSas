<x-guest-layout>
    <p class="studio-eyebrow">BOM TER VOCÊ POR AQUI</p>
    <h1 class="studio-auth-title">Seu próximo passo<br>começa aqui.</h1>
    <p class="studio-auth-subtitle">Entre no seu espaço e continue dando forma à sua trajetória.</p>
    <x-auth-session-status class="mb-5" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="studio-auth-form">
        @csrf
        <div><x-input-label for="email" value="Seu e-mail" /><x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="voce@exemplo.com" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
        <div><x-input-label for="password" value="Senha" /><x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" placeholder="Sua senha" /><x-input-error :messages="$errors->get('password')" class="mt-2" /></div>
        <div class="studio-auth-options"><label for="remember_me"><input id="remember_me" type="checkbox" name="remember" class="rounded">Manter conectado</label>@if(Route::has('password.request'))<a class="studio-text-link" href="{{ route('password.request') }}">Esqueci minha senha</a>@endif</div>
        <x-primary-button class="studio-button-wide">Entrar no meu espaço <span aria-hidden="true">↗</span></x-primary-button>
    </form>
    <p class="studio-auth-alternative">Ainda não tem um espaço?<a href="{{ route('register') }}" class="studio-text-link">Crie seu portfólio</a></p>
</x-guest-layout>
