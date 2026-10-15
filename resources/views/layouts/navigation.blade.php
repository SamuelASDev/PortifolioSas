<aside class="studio-sidebar" aria-label="Menu do painel">
    <x-brand :href="route('dashboard')" />
    <div class="studio-sidebar-label">SEU ESPAÇO</div>
    <nav aria-label="Navegação principal" class="studio-nav"><x-studio-nav-links /></nav>
    <div class="studio-sidebar-note"><span class="studio-tiny-star" aria-hidden="true">✳</span><p>Boas ideias merecem<br>ser vistas.</p><span>Construa o seu próximo passo.</span></div>
    <div class="studio-sidebar-bottom">
        <a href="{{ route('profile.edit') }}" @class(['studio-nav-link', 'is-active' => request()->routeIs('profile.*')])><x-public-icon name="gear" /><span>Minha conta</span></a>
        <div class="studio-account"><span class="studio-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><span>Seu espaço criativo</span></div><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="studio-icon-button" aria-label="Sair da conta" title="Sair da conta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M9 5H5v14h4m5-11 4 4-4 4m-5-4h9" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form></div>
    </div>
</aside>
<header class="studio-mobile-bar">
    <x-brand :href="route('dashboard')" />
    <details class="studio-mobile-menu"><summary aria-label="Abrir navegação"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg></summary><nav aria-label="Navegação do celular"><x-studio-nav-links /><a class="studio-nav-link" href="{{ route('profile.edit') }}"><x-public-icon name="gear" /> Minha conta</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="studio-nav-link" type="submit">Sair da conta</button></form></nav></details>
</header>
