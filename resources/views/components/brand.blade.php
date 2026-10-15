@props(['href' => '/', 'compact' => false])
<a href="{{ $href }}" {{ $attributes->class(['studio-brand']) }} aria-label="{{ config('brand.name') }} — início">
    <x-application-logo class="studio-brand-mark" />
    @unless ($compact)<span>{{ config('brand.name') }}<span class="studio-brand-period">.</span></span>@endunless
</a>
