<button {{ $attributes->merge(['type' => 'submit', 'class' => 'studio-button studio-button-primary']) }}>
    {{ $slot }}
</button>
