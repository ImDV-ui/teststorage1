<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Gestor de archivos')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('photos.index') }}" class="text-lg font-semibold tracking-tight text-slate-900">
                    Gestor de Archivos
                </a>

                <nav class="flex items-center gap-2" aria-label="Navegación principal">
                    <a
                        href="{{ route('photos.index') }}"
                        @class([
                            'rounded-lg px-3.5 py-2 text-sm font-medium transition',
                            'bg-slate-900 text-white' => request()->routeIs('photos.*'),
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('photos.*'),
                        ])
                    >
                        Galería de imágenes
                    </a>
                    <a
                        href="{{ route('docs.index') }}"
                        @class([
                            'rounded-lg px-3.5 py-2 text-sm font-medium transition',
                            'bg-slate-900 text-white' => request()->routeIs('docs.*'),
                            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('docs.*'),
                        ])
                    >
                        Gestor de documentos
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto flex max-w-5xl flex-col gap-6 px-4 py-8 sm:px-6">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Revisa los siguientes errores:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>

