@extends('layouts.app')

@section('title', 'Gestor de documentos')

@section('content')
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <h1 class="text-xl font-semibold text-slate-900">Subir nuevo documento PDF</h1>
        <p class="mt-1 text-sm text-slate-600">Formato permitido: PDF (máx. 5 MB).</p>

        <form action="{{ route('docs.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="title" class="text-sm font-medium text-slate-700">Título del documento</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    placeholder="Ej. Contrato de servicios"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="document" class="text-sm font-medium text-slate-700">Archivo PDF</label>
                <input
                    type="file"
                    name="document"
                    id="document"
                    accept=".pdf,application/pdf"
                    required
                    class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                >
            </div>

            <div class="sm:col-span-2">
                <button
                    type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                >
                    Subir documento
                </button>
            </div>
        </form>
    </section>

    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Documentos</h2>
            <span class="text-sm text-slate-500">{{ $docs->count() }} {{ $docs->count() === 1 ? 'documento' : 'documentos' }}</span>
        </div>

        @if ($docs->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                No hay documentos PDF subidos todavía.
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($docs as $doc)
                    <article class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-xs sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <a
                                href="{{ asset('storage/' . $doc->file_path) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 font-semibold text-slate-900 underline decoration-slate-300 underline-offset-4 transition hover:text-blue-700 hover:decoration-blue-500"
                            >
                                <span>{{ $doc->title }}</span>
                                <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700">PDF</span>
                            </a>
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $doc->file_path }}</p>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <form action="{{ route('docs.update', $doc) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PUT')

                                <label for="document-{{ $doc->id }}" class="sr-only">Reemplazar documento</label>
                                <input
                                    type="file"
                                    name="document"
                                    id="document-{{ $doc->id }}"
                                    accept=".pdf,application/pdf"
                                    required
                                    class="max-w-56 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs file:mr-2 file:rounded file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-medium file:text-slate-700"
                                >
                                <button
                                    type="submit"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Reemplazar
                                </button>
                            </form>

                            <form action="{{ route('docs.destroy', $doc) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('¿Eliminar este documento?')"
                                    class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 sm:w-auto"
                                >
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

