<x-guest-layout>
    <p class="studio-eyebrow">QUASE TUDO PRONTO</p>
    <h1 class="studio-auth-title">Seu espaço espera<br>por você.</h1>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Confirme seu e-mail pelo link enviado para acessar o painel. Se você já tinha uma conta e ainda não confirmou o endereço, use o botão abaixo para receber um novo link.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            Um novo link de verificação foi enviado para o e-mail da sua conta.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar verificação
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                Sair
            </button>
        </form>
    </div>
    <a href="{{ route('profile.edit') }}" class="mt-5 inline-block text-sm text-indigo-600 dark:text-indigo-400 underline">Corrigir o e-mail da conta</a>
</x-guest-layout>
