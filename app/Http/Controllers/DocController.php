<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class DocController extends Controller
{
    public function index(): View
    {
        try {
            $docs = Doc::latest()->get();

            return view('docs.index', compact('docs'));
        } catch (Throwable $exception) {
            Log::error('Error al cargar el gestor de documentos.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            session()->flash('error', 'No se pudieron cargar los documentos. Inténtalo de nuevo.');

            return view('docs.index', ['docs' => collect()]);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            ], [
                'title.required' => 'El título del documento es obligatorio.',
                'title.max' => 'El título no puede superar los 255 caracteres.',
                'document.required' => 'Debes seleccionar un archivo PDF.',
                'document.file' => 'El archivo seleccionado no es válido.',
                'document.mimes' => 'Solo se permiten documentos en formato PDF.',
                'document.max' => 'El documento PDF no puede superar los 5 MB.',
            ]);

            $path = $request->file('document')->store('docs', 'public');

            if (! $path) {
                throw new RuntimeException('No se pudo guardar el documento en el disco público.');
            }

            try {
                Doc::create([
                    'title' => $validated['title'],
                    'file_path' => $path,
                ]);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($path);
                throw $exception;
            }

            return to_route('docs.index')->with('success', 'Documento subido correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al subir un documento.', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()
                ->withInput($request->except('document'))
                ->with('error', 'No se pudo subir el documento. Inténtalo de nuevo.');
        }
    }

    public function update(Request $request, Doc $doc): RedirectResponse
    {
        try {
            $request->validate([
                'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            ], [
                'document.required' => 'Debes seleccionar un nuevo archivo PDF.',
                'document.file' => 'El archivo seleccionado no es válido.',
                'document.mimes' => 'Solo se permiten documentos en formato PDF.',
                'document.max' => 'El documento PDF no puede superar los 5 MB.',
            ]);

            $newPath = $request->file('document')->store('docs', 'public');

            if (! $newPath) {
                throw new RuntimeException('No se pudo guardar el nuevo documento en el disco público.');
            }

            $oldPath = $doc->file_path;

            try {
                $doc->update([
                    'file_path' => $newPath,
                ]);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($newPath);
                throw $exception;
            }

            if ($oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            return to_route('docs.index')->with('success', 'Documento actualizado correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al actualizar un documento.', [
                'doc_id' => $doc->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()->with('error', 'No se pudo actualizar el documento. Inténtalo de nuevo.');
        }
    }

    public function destroy(Doc $doc): RedirectResponse
    {
        try {
            $filePath = $doc->file_path;
            $doc->delete();

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return to_route('docs.index')->with('success', 'Documento eliminado correctamente.');
        } catch (Throwable $exception) {
            Log::error('Error al eliminar un documento.', [
                'doc_id' => $doc->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return back()->with('error', 'No se pudo eliminar el documento. Inténtalo de nuevo.');
        }
    }
}
