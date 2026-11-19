@extends('layouts.app')

@section('title', 'Galería de imágenes')

@section('content')
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
        <h1 class="text-xl font-semibold text-slate-900">Subir nueva imagen</h1>
        <p class="mt-1 text-sm text-slate-600">Formatos permitidos: JPG, JPEG, PNG o WEBP (máx. 2 MB).</p>

        <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="name" class="text-sm font-medium text-slate-700">Título de la imagen</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                    placeholder="Ej. Vacaciones en la montaña"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
                >
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="image" class="text-sm font-medium text-slate-700">Archivo de imagen</label>
                <input
                    type="file"
                    name="image"
                    id="image"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    required
                    class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                >
            </div>

            <div class="sm:col-span-2">
                <button
                    type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                >
                    Subir imagen
                </button>
            </div>
        </form>
    </section>

    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Galería</h2>
            <span class="text-sm text-slate-500">{{ $photos->count() }} {{ $photos->count() === 1 ? 'imagen' : 'imágenes' }}</span>
        </div>

        @if ($photos->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                No hay imágenes subidas todavía.
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($photos as $photo)
                    <article class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
                        <div class="aspect-4/3 bg-slate-100">
                            <img
                                src="{{ asset('storage/' . $photo->image_path) }}"
                                alt="{{ $photo->name }}"
                                class="h-full w-full object-cover"
                            >
                        </div>

                        <div class="flex flex-1 flex-col gap-4 p-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $photo->name }}</h3>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $photo->image_path }}</p>
                            </div>

                            <form action="{{ route('photos.update', $photo) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                @csrf
                                @method('PUT')

                                <label for="image-{{ $photo->id }}" class="text-xs font-medium text-slate-600">
                                    Reemplazar imagen
                                </label>
                                <input
                                    type="file"
                                    name="image"
                                    id="image-{{ $photo->id }}"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    required
                                    class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs file:mr-2 file:rounded file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-medium file:text-slate-700"
                                >
                                <button
                                    type="submit"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Actualizar imagen
                                </button>
                            </form>

                            <form action="{{ route('photos.destroy', $photo) }}" method="POST" class="mt-auto">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('¿Eliminar esta imagen?')"
                                    class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100"
                                >
                                    Eliminar imagen
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

