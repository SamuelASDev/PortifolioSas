@foreach ([['dashboard', 'Visão geral', 'dashboard', 'dashboard'], ['admin.portfolio.edit', 'Meu portfólio', 'layout', 'admin.portfolio.*'], ['admin.projects.index', 'Projetos', 'code', 'admin.projects.*']] as [$route, $label, $icon, $pattern])
    <a href="{{ route($route) }}" @class(['studio-nav-link', 'is-active' => request()->routeIs($pattern)]) @if(request()->routeIs($pattern)) aria-current="page" @endif><x-public-icon :name="$icon" /><span>{{ $label }}</span>@if(request()->routeIs($pattern))<span class="studio-nav-dot" aria-hidden="true"></span>@endif</a>
@endforeach
