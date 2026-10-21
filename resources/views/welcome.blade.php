<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Seu trabalho, em perspectiva. Crie um portfólio com sua identidade, organize seus projetos e compartilhe sua trajetória com Vértice.">
    <title>{{ config('brand.name') }} · Seu trabalho, em perspectiva</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="studio studio-marketing">
    <a href="#conteudo" class="studio-skip">Pular para o conteúdo</a>
    <div class="studio-marketing-container">
        <header class="studio-marketing-header"><x-brand /><nav aria-label="Navegação principal"><a href="#como-funciona">Como funciona</a>@auth<a href="{{ route('dashboard') }}" class="studio-button studio-button-secondary">Meu espaço <span aria-hidden="true">↗</span></a>@else<a href="{{ route('login') }}">Entrar</a><a href="{{ route('register') }}" class="studio-button studio-button-secondary">Começar <span aria-hidden="true">↗</span></a>@endauth</nav></header>
        <main id="conteudo" tabindex="-1">
            <section class="studio-marketing-hero" aria-labelledby="hero-title">
                <div class="studio-marketing-hero-copy">
                    <span class="vertice-index">01 / SEU PONTO DE PARTIDA</span>
                    <h1 id="hero-title">O que você faz<br>merece um<br><em>ponto de vista.</em></h1>
                    <p>Seu trabalho tem uma história. Dê a ela um espaço com a sua identidade, do primeiro projeto à próxima oportunidade.</p>
                    <div class="studio-hero-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="studio-button studio-button-primary">Continuar meu portfólio <span aria-hidden="true">↗</span></a>
                        @else
                            <a href="{{ route('register') }}" class="studio-button studio-button-primary">Criar meu portfólio <span aria-hidden="true">↗</span></a>
                        @endauth
                        <a href="#como-funciona" class="vertice-text-action">Explore as possibilidades <span aria-hidden="true">↓</span></a>
                    </div>
                    <small class="vertice-hero-note"><span aria-hidden="true">↳</span> Seu trabalho no centro. Sua identidade em cada detalhe.</small>
                </div>
                <figure class="vertice-canvas" aria-labelledby="canvas-caption">
                    <div class="vertice-canvas-heading"><span>ESTUDO DE PERSPECTIVA</span><span aria-hidden="true">V / 01</span></div>
                    <svg class="vertice-perspective" viewBox="0 0 520 500" fill="none" aria-hidden="true">
                        <path d="M-40 30 260 430 560 30M-40 180 260 430 560 180M-40 330 260 430 560 330M260-30V500M0 430H520" stroke="currentColor" stroke-width=".7"/>
                        <path d="M60 70 228 390H292L460 70H352L260 258 168 70Z" fill="#b6a0f5"/>
                        <path d="M352 70 260 258 292 322 416 70Z" fill="#f2eee5"/>
                        <path d="M60 70H460M228 390H292" stroke="#d5f2ae" stroke-width="1"/>
                        <circle cx="260" cy="430" r="7" fill="#d5f2ae"/>
                        <path d="M250 430H270M260 420V440" stroke="#101313"/>
                    </svg>
                    <div class="vertice-sample">
                        <div class="vertice-sample-top"><span>PORTFÓLIO / DESIGN & CÓDIGO</span><span aria-hidden="true">↗</span></div>
                        <strong>Marina Costa<span>.</span></strong>
                        <p>Entre ideias<br>e novas formas.</p>
                        <div class="vertice-sample-bottom"><span>Identidade. Interfaces. Experiências.</span><span aria-hidden="true">01 — 03</span></div>
                    </div>
                    <figcaption id="canvas-caption"><span>Uma possibilidade. A próxima é sua.</span><span aria-hidden="true">↗</span></figcaption>
                </figure>
            </section>
            <div class="vertice-principle"><span>UM ESPAÇO. SEU PONTO DE VISTA.</span><p>Ideias ganham forma.<br><strong>Seu trabalho ganha direção.</strong></p><svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M8 40 40 8M8 8H40V40" stroke="currentColor" stroke-width="2"/></svg></div>
            <section id="como-funciona" class="studio-marketing-steps" aria-labelledby="steps-title">
                <div class="studio-marketing-section-top"><div><p class="vertice-index">02 / DA IDEIA AO MUNDO</p><h2 id="steps-title">Comece com o que é seu.</h2></div><p>Você traz a trajetória.<br>O Vértice abre espaço para ela.</p></div>
                <ol class="studio-feature-grid">
                    @foreach([['01','profile','Apresente sua perspectiva.','Seu nome, sua trajetória e os caminhos para encontrar você. A primeira impressão começa aqui.'],['02','code','Dê contexto ao que criou.','Reúna imagens, vídeos e tecnologias. Conte as decisões e ideias por trás de cada projeto.'],['03','layout','Assine do seu jeito.','Escolha cores, fontes e destaques. Veja a prévia e publique quando fizer sentido para você.']] as [$number,$icon,$title,$description])
                        <li class="studio-feature"><div class="studio-feature-top"><span>{{ $number }}</span><x-public-icon :name="$icon" /></div><h3>{{ $title }}</h3><p>{{ $description }}</p></li>
                    @endforeach
                </ol>
            </section>
            <section class="vertice-invitation" aria-labelledby="invitation-title"><span class="vertice-index">SEU PRÓXIMO CAPÍTULO</span><h2 id="invitation-title">Encontre seu vértice<span>.</span></h2><a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="studio-button studio-button-primary">{{ auth()->check() ? 'Voltar ao meu espaço' : 'Começar meu portfólio' }} <span aria-hidden="true">↗</span></a></section>
        </main>
        <footer class="studio-marketing-footer"><x-brand /><span>Seu trabalho, em perspectiva.</span><span>© {{ date('Y') }} Vértice</span></footer>
    </div>
</body>
</html>
