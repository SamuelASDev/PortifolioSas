@props(['name'])
@if (str_starts_with($name, 'si:') && ($brand = app(\App\Support\TechIcons::class)->find(substr($name, 3))))
<svg {{ $attributes->class('pf-icon pf-icon-brand') }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="{{ $brand['path'] }}" /></svg>
@else
<svg {{ $attributes->class('pf-icon') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('pen')
            <path d="M4 20h4L19 9l-4-4L4 16Z" /><path d="m13 7 4 4" />
            @break
        @case('cloud')
            <path d="M7 18a5 5 0 1 1 1-9.9A6 6 0 0 1 19 10a4 4 0 0 1-1 8Z" />
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2" /><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18" />
            @break
        @case('monitor')
            <rect x="2" y="4" width="20" height="13" rx="2" /><path d="M8 21h8m-4-4v4" />
            @break
        @case('server')
            <rect x="3" y="3" width="18" height="7" rx="1.5" /><rect x="3" y="14" width="18" height="7" rx="1.5" /><path d="M7 6.5h.01M7 17.5h.01" />
            @break
        @case('sparkles')
            <path d="M12 3v4m0 10v4M3 12h4m10 0h4m-3.5-6.5-2 2m-7 7-2 2m0-11 2 2m7 7 2 2" />
            @break
        @case('chart')
            <path d="M3 3v18h18" /><path d="M7 15l4-4 3 3 5-6" />
            @break
        @case('terminal')
            <rect x="2" y="4" width="20" height="16" rx="2" /><path d="m6 9 3 3-3 3m5 0h6" />
            @break
        @case('package')
            <path d="m12 2 9 5v10l-9 5-9-5V7Z" /><path d="m3 7 9 5 9-5M12 12v10" />
            @break
        @case('palette')
            <path d="M12 3a9 9 0 1 0 0 18c1.5 0 2-1 2-2s-1-2 0-3h3a4 4 0 0 0 4-4c0-5-4-9-9-9Z" /><circle cx="7.5" cy="11" r="1" /><circle cx="10" cy="7" r="1" /><circle cx="15" cy="7.5" r="1" />
            @break
        @case('book')
            <path d="M4 4h6a3 3 0 0 1 2 1 3 3 0 0 1 2-1h6v15h-6a2 2 0 0 0-2 2 2 2 0 0 0-2-2H4Z" /><path d="M12 5v16" />
            @break
        @case('chip')
            <rect x="6" y="6" width="12" height="12" rx="1.5" /><path d="M9 2v4m6-4v4M9 18v4m6-4v4M2 9h4m-4 6h4m12-6h4m-4 6h4" />
            @break
        @case('phone')
            <path d="M5 3h4l2 5-3 2a11 11 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2Z" />
            @break
        @case('pin')
            <path d="M12 21s7-6.1 7-12a7 7 0 0 0-14 0c0 5.9 7 12 7 12Z" /><circle cx="12" cy="9" r="2.5" />
            @break
        @case('instagram')
            <rect x="3" y="3" width="18" height="18" rx="5" /><circle cx="12" cy="12" r="4" /><path d="M17.5 6.5h.01" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('search')
            <circle cx="10" cy="10" r="7" /><path d="m15 15 6 6" />
            @break
        @case('filter')
            <path d="M3 4h18l-7 8v7l-4 2v-9Z" />
            @break
        @case('tag')
            <path d="M3 3h8l10 10-8 8L3 11Z" /><circle cx="7" cy="7" r="1" />
            @break
        @case('star')
            <path d="m12 3 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1Z" />
            @break
        @case('check')
            <circle cx="12" cy="12" r="9" /><path d="m7 12 3 3 7-7" />
            @break
        @case('profile')
            <circle cx="12" cy="8" r="4" /><path d="M4 22v-2a8 8 0 0 1 16 0v2" />
            @break
        @case('code')
            <path d="m7 5-6 7 6 7m10-14 6 7-6 7M14 3l-4 18" />
            @break
        @case('layout')
            <rect x="2" y="3" width="20" height="18" rx="2" /><path d="M2 8h20M9 8v13" />
            @break
        @case('dashboard')
            <rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="11" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><path d="M14 18h7m-7 3h7" />
            @break
        @case('cart')
            <path d="M2 3h3l3 13h11l3-10H6" /><circle cx="9" cy="21" r="1" /><circle cx="18" cy="21" r="1" />
            @break
        @case('mobile')
            <rect x="6" y="2" width="12" height="20" rx="2" /><path d="M10 5h4m-3 14h2" />
            @break
        @case('game')
            <path d="M7 6h10c5 0 7 15 3 15l-5-5H9l-5 5C0 21 2 6 7 6Z" /><path d="M5 11h6m-3-3v6m8-3h.1m3 2h.1" />
            @break
        @case('gear')
            <path d="m9 3 1-2h4l1 2 3 2 2 1v4l2 2-2 2v4l-2 1-3 2-1 2h-4l-1-2-3-2-2-1v-4l-2-2 2-2V6l2-1Z" /><circle cx="12" cy="12" r="4" />
            @break
        @case('cube')
            <path d="m12 2 9 5v10l-9 5-9-5V7Zm0 10 9-5m-9 5L3 7m9 5v10M7.5 4.5l9 5v5" />
            @break
        @case('database')
            <ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M3 5v14c0 4 18 4 18 0V5M3 12c0 4 18 4 18 0" />
            @break
        @case('php')
            <ellipse cx="12" cy="12" rx="11" ry="7" /><text x="12" y="15" text-anchor="middle" font-size="8" font-weight="700" font-family="sans-serif" fill="currentColor" stroke="none">PHP</text>
            @break
        @case('javascript')
            <rect x="2" y="2" width="20" height="20" rx="2" /><text x="12" y="17" text-anchor="middle" font-size="12" font-weight="700" font-family="sans-serif" fill="currentColor" stroke="none">JS</text>
            @break
        @case('copy')
            <rect x="8" y="8" width="12" height="13" rx="2" /><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h3" />
            @break
        @case('share')
            <circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" /><path d="m8.6 10.5 6.8-4m-6.8 7 6.8 4" />
            @break
        @case('github')
            <path d="M9 19c-4 1-4-2-6-2m12 5v-4a3.5 3.5 0 0 0-1-2.7c3.3-.4 6.8-1.6 6.8-7.3A5.7 5.7 0 0 0 19.3 4a5.3 5.3 0 0 0-.1-4S18 0 15 1.5a13.5 13.5 0 0 0-6 0C6 0 4.8 0 4.8 0a5.3 5.3 0 0 0-.1 4A5.7 5.7 0 0 0 3.2 8c0 5.7 3.5 6.9 6.8 7.3A3.5 3.5 0 0 0 9 18v4" transform="translate(0 1) scale(1 .9)" />
            @break
        @case('linkedin')
            <rect x="3" y="3" width="18" height="18" rx="2" /><path d="M7 10v7m4 0v-7m0 3c0-4 6-4 6 0v4" /><circle cx="7" cy="7" r=".7" fill="currentColor" />
            @break
        @case('email')
            <rect x="2" y="4" width="20" height="16" rx="2" /><path d="m3 6 9 7 9-7" />
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9" /><ellipse cx="12" cy="12" rx="4" ry="9" /><path d="M3 12h18" />
            @break
        @case('chevron-left')
            <path d="m15 5-7 7 7 7" />
            @break
        @case('chevron-right')
            <path d="m9 5 7 7-7 7" />
            @break
        @default
            <rect x="4" y="3" width="16" height="18" rx="2" /><path d="M8 8h8M8 12h8M8 16h5" />
    @endswitch
</svg>
@endif
