<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Invexa Frete') }}</title>
        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .invexa-auth-bg {
                min-height: 100vh;
                background: radial-gradient(circle at 15% 10%, #24314f 0%, #16213e 45%, #1a1a2e 100%);
            }
            .invexa-logo-badge {
                background: linear-gradient(135deg, #f97316, #ea580c);
                box-shadow: 0 8px 20px rgba(249, 115, 22, .4);
            }
            .invexa-auth-card {
                box-shadow: 0 20px 45px rgba(0, 0, 0, .25), 0 0 0 1px rgba(249, 115, 22, .08), 0 0 45px -10px rgba(249, 115, 22, .35);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="invexa-auth-bg flex flex-col justify-center items-center px-4 py-10">

            <div class="flex flex-col items-center mb-6">
                <a href="/" class="invexa-logo-badge w-16 h-16 rounded-2xl flex items-center justify-center mb-3">
                    <i class="bi bi-truck-front-fill text-white" style="font-size: 1.75rem"></i>
                </a>
                <div class="text-2xl font-bold text-white tracking-tight">
                    Invexa <span class="text-orange-500">Frete</span>
                </div>
                <div class="text-xs text-white/40 tracking-widest uppercase mt-1">
                    Gestão de Viagens
                </div>
            </div>

            <div class="invexa-auth-card w-full sm:max-w-md px-6 py-8 bg-white overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>

            <p class="text-white/30 text-xs mt-6">
                &copy; {{ date('Y') }} Invexa Frete ·
                <a href="{{ route('legal.termos') }}" class="text-white/30 no-underline">Termos</a> ·
                <a href="{{ route('legal.privacidade') }}" class="text-white/30 no-underline">Privacidade</a>
            </p>
            <p class="text-xs mt-1">
                <span class="text-white/30">Desenvolvido por</span>
                <a href="https://www.instagram.com/castilho_digital/" target="_blank" rel="noopener noreferrer"
                   class="text-orange-500 font-semibold no-underline">
                    <i class="bi bi-instagram me-1"></i>Castilho Soluções Digitais
                </a>
            </p>
        </div>
    </body>
</html>
