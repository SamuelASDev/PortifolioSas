<button {{ $attributes->merge(['type' => 'button', 'class' => 'studio-button studio-button-secondary']) }}>
    {{ $slot }}
</button>
