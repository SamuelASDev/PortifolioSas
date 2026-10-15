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
                <div class="studio-marketing-hero-copy"><span class="studio-hero-badge">UM ESPAÇO PARA QUEM CRIA</span><h1 id="hero-title">Seu trabalho,<br>em sua melhor<br><em>perspectiva.</em></h1><p>Mais que uma coleção de projetos. Um espaço para contar sua história, expressar sua identidade e abrir novas possibilidades.</p><div class="studio-hero-actions">@auth<a href="{{ route('dashboard') }}" class="studio-button studio-button-primary">Continuar meu portfólio <span aria-hidden="true">↗</span></a>@else<a href="{{ route('register') }}" class="studio-button studio-button-primary">Criar meu portfólio <span aria-hidden="true">↗</span></a><a href="#como-funciona" class="studio-button studio-button-secondary">Conhecer o Vértice</a>@endauth</div><small>Do seu primeiro projeto à sua próxima oportunidade.</small></div>
                <div><div class="studio-demo" role="img" aria-label="Exemplo ilustrativo de um portfólio com apresentação profissional e dois projetos"><div class="studio-demo-top"><i></i><i></i><i></i><span>seu próximo portfólio</span></div><div class="studio-demo-page"><div class="studio-demo-identity"><b>Marina Costa.</b><span>Projetos &nbsp; Sobre &nbsp; Contato</span></div><h2>Ideias que encontram<br>novas formas.</h2><p>Designer & desenvolvedora. Transformando boas ideias em experiências digitais com propósito.</p><div class="studio-demo-works"><div><div class="studio-demo-thumbnail"></div><strong>Entre formas</strong><small>Design & desenvolvimento</small></div><div><div class="studio-demo-thumbnail is-second"></div><strong>Studio Botânica</strong><small>Identidade & web</small></div></div></div></div><p class="studio-demo-caption">Uma possibilidade. A sua vai ter o seu jeito.</p></div>
            </section>
            <section id="como-funciona" class="studio-marketing-steps" aria-labelledby="steps-title"><div class="studio-marketing-section-top"><div><p class="studio-eyebrow">SIMPLES DE COMEÇAR. SEU EM CADA DETALHE.</p><h2 id="steps-title">Um novo olhar para<br>o que você já faz.</h2></div><p>Você cuida das ideias. O Vértice dá a elas um espaço à altura.</p></div><ol class="studio-feature-grid">@foreach([['01','profile','Comece por você','Seu nome, sua trajetória e os caminhos para encontrar você. Uma apresentação que aproxima.'],['02','code','Conte cada projeto','Reúna imagens, vídeos e tecnologias. Mostre não só o resultado, mas a história por trás dele.'],['03','layout','Encontre sua expressão','Escolha cores e fontes, organize os destaques e publique quando tudo estiver com a sua cara.']] as [$number,$icon,$title,$description])<li class="studio-feature"><div class="studio-feature-top"><x-public-icon :name="$icon" /><span>{{ $number }}</span></div><h3>{{ $title }}</h3><p>{{ $description }}</p></li>@endforeach</ol></section>
        </main>
        <footer class="studio-marketing-footer"><x-brand /><span>Seu trabalho, em perspectiva.</span><span>© {{ date('Y') }} Vértice</span></footer>
    </div>
</body>
</html>
