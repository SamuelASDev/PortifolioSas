<button {{ $attributes->merge(['type' => 'submit', 'class' => 'studio-button studio-button-danger']) }}>
    {{ $slot }}
</button>
