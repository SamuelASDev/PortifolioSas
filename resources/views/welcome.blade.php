<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Crie um portfólio com sua apresentação, projetos e tecnologias. Personalize a aparência e publique quando estiver pronto.">
    <title>Seu trabalho merece um portfólio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 antialiased">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:absolute focus:p-4 focus:bg-indigo-600">Pular para o conteúdo</a>
    <header class="max-w-6xl mx-auto px-6 py-6 flex flex-wrap items-center justify-between gap-4 border-b border-gray-800">
        <a href="{{ url('/') }}" class="font-semibold text-lg">Portfólio<span class="text-indigo-400">.</span></a>
        <nav aria-label="Navegação principal" class="flex flex-wrap items-center gap-5 text-sm">
            <a href="#como-funciona" class="text-gray-300 hover:text-white underline-offset-4 hover:underline">Como funciona</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-3 font-semibold hover:bg-indigo-500">Meu painel</a>
            @else
                <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">Entrar</a>
            @endauth
        </nav>
    </header>
    <main id="conteudo" tabindex="-1" class="max-w-6xl mx-auto px-6">
        <section class="py-20 sm:py-28 max-w-3xl" aria-labelledby="hero-title">
            <p class="uppercase tracking-widest text-indigo-300 text-xs font-semibold mb-5">Uma página com a sua identidade</p>
            <h1 id="hero-title" class="text-4xl sm:text-6xl font-bold tracking-tight leading-tight">Seu trabalho merece<br>um portfólio.</h1>
            <p class="mt-6 text-lg text-gray-300 leading-relaxed max-w-2xl">Reúna seus projetos, conte como você constrói e mostre suas tecnologias. Personalize cada detalhe e compartilhe sua trajetória em um só link.</p>
            <div class="mt-9 flex flex-wrap gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-6 py-3 font-semibold hover:bg-indigo-500">Continuar meu portfólio →</a>
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-6 py-3 font-semibold hover:bg-indigo-500">Criar meu portfólio →</a>
                    <a href="{{ route('login') }}" class="rounded-lg border border-gray-600 px-6 py-3 font-semibold hover:bg-gray-800">Já tenho uma conta</a>
                @endauth
            </div>
            <p class="mt-5 text-sm text-gray-400">Confira a prévia e publique quando estiver pronto.</p>
        </section>
        <section id="como-funciona" class="border-t border-gray-800 py-14 sm:py-20" aria-labelledby="steps-title">
            <h2 id="steps-title" class="text-2xl sm:text-3xl font-semibold">Da primeira ideia ao link publicado</h2>
            <ol class="mt-9 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['01', 'Apresente-se', 'Adicione sua foto, uma biografia e os canais onde podem encontrar você.'],
                    ['02', 'Mostre o que criou', 'Conte a história dos projetos com textos, imagens, vídeos e tecnologias.'],
                    ['03', 'Deixe com a sua cara', 'Escolha cores e fontes, organize os destaques e publique seu portfólio.'],
                ] as [$number, $title, $description])
                    <li class="rounded-2xl border border-gray-800 bg-gray-900 p-7">
                        <span class="text-indigo-300 font-mono text-sm">{{ $number }}</span>
                        <h3 class="mt-5 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-3 text-gray-400 leading-relaxed">{{ $description }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </main>
    <footer class="border-t border-gray-800"><div class="max-w-6xl mx-auto px-6 py-8 text-sm text-gray-400">Portfólio · Seu trabalho, suas histórias.</div></footer>
</body>
</html>
