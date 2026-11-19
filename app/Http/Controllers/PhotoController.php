<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PhotoController extends Controller
{
    public function index(): View
    {
        try {
            $photos = Photo::latest()->get();

            return view('photos.index', compact('photos'));
        } catch (Throwable $exception) {
            Log::error('Error al cargar la galería de fotos.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            session()->flash('error', 'No se pudo cargar la galería de imágenes. Inténtalo de nuevo.');

            return view('photos.index', ['photos' => collect()]);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ], [
                'name.required' => 'El título de la imagen es obligatorio.',
                'name.max' => 'El título no puede superar los 255 caracteres.',
                'image.required' => 'Debes seleccionar una imagen.',
                'image.image' => 'El archivo debe ser una imagen válida.',
                'image.mimes' => 'La imagen debe estar en formato JPG, JPEG, PNG o WEBP.',
                'image.max' => 'La imagen no puede superar los 2 MB.',
            ]);

            $path = $request->file('image')->store('photos', 'public');

            if (! $path) {
                throw new RuntimeException('No se pudo guardar la imagen en el disco público.');
            }

            try {
                Photo::create([
                    'name' => $validated['name'],
                    'image_path' => $path,
                ]);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($path);
                throw $exception;
            }

            return to_route('photos.index')->with('success', 'Imagen subida correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al subir una foto.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()
                ->withInput($request->except('image'))
                ->with('error', 'No se pudo subir la imagen. Inténtalo de nuevo.');
        }
    }

    public function update(Request $request, Photo $photo): RedirectResponse
    {
        try {
            $request->validate([
                'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ], [
                'image.required' => 'Debes seleccionar una nueva imagen.',
                'image.image' => 'El archivo debe ser una imagen válida.',
                'image.mimes' => 'La imagen debe estar en formato JPG, JPEG, PNG o WEBP.',
                'image.max' => 'La imagen no puede superar los 2 MB.',
            ]);

            $newPath = $request->file('image')->store('photos', 'public');

            if (! $newPath) {
                throw new RuntimeException('No se pudo guardar la nueva imagen en el disco público.');
            }

            $oldPath = $photo->image_path;

            try {
                $photo->update([
                    'image_path' => $newPath,
                ]);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($newPath);
                throw $exception;
            }

            if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            return to_route('photos.index')->with('success', 'Imagen actualizada correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al actualizar una foto.', [
                'photo_id' => $photo->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()->with('error', 'No se pudo actualizar la imagen. Inténtalo de nuevo.');
        }
    }

    public function destroy(Photo $photo): RedirectResponse
    {
        try {
            $photoPath = $photo->image_path;
            $photo->delete();

            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return to_route('photos.index')->with('success', 'Imagen eliminada correctamente');
        } catch (Throwable $exception) {
            Log::error('Error al eliminar una foto.', [
                'photo_id' => $photo->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()->with('error', 'No se pudo eliminar la imagen. Inténtalo de nuevo.');
        }
    }
}
