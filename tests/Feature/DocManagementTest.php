<?php

use App\Models\Doc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('the docs page lists documents with links opening in a new tab and navigation to photos', function () {
    $doc = Doc::factory()->create([
        'title' => 'Manual de usuario',
        'file_path' => 'docs/manual.pdf',
    ]);

    $response = $this->get(route('docs.index'));

    $response->assertOk()
        ->assertSee('Manual de usuario')
        ->assertSee('target="_blank"', false)
        ->assertSee(asset('storage/'.$doc->file_path), false)
        ->assertSee(route('photos.index'));
});

test('a pdf document can be uploaded to the docs folder on the public disk', function () {
    Storage::fake('public');

    $response = $this->post(route('docs.store'), [
        'title' => 'Contrato',
        'document' => UploadedFile::fake()->create('contrato.pdf', 500, 'application/pdf'),
    ]);

    $response->assertRedirect(route('docs.index'));
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $doc = Doc::first();

    expect($doc)->not->toBeNull()
        ->and($doc->title)->toBe('Contrato')
        ->and($doc->file_path)->toStartWith('docs/');

    Storage::disk('public')->assertExists($doc->file_path);
});

test('a pdf document can be replaced and the old file is deleted from disk', function () {
    Storage::fake('public');

    $oldPath = UploadedFile::fake()->create('viejo.pdf', 200, 'application/pdf')->store('docs', 'public');
    $doc = Doc::factory()->create([
        'title' => 'Factura',
        'file_path' => $oldPath,
    ]);

    $response = $this->put(route('docs.update', $doc), [
        'document' => UploadedFile::fake()->create('nuevo.pdf', 300, 'application/pdf'),
    ]);

    $response->assertRedirect(route('docs.index'));
    $response->assertSessionHas('success');

    $doc->refresh();

    expect($doc->file_path)->not->toBe($oldPath)
        ->and($doc->file_path)->toStartWith('docs/');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($doc->file_path);
});

test('a pdf document and its physical file can be deleted', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->create('eliminar.pdf', 200, 'application/pdf')->store('docs', 'public');
    $doc = Doc::factory()->create([
        'title' => 'Borrador',
        'file_path' => $path,
    ]);

    $response = $this->delete(route('docs.destroy', $doc));

    $response->assertRedirect(route('docs.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('docs', ['id' => $doc->id]);
    Storage::disk('public')->assertMissing($path);
});

test('document upload validates required fields, pdf format, and max size', function () {
    Storage::fake('public');

    $this->post(route('docs.store'), [])
        ->assertSessionHasErrors(['title', 'document']);

    $this->post(route('docs.store'), [
        'title' => 'No es PDF',
        'document' => UploadedFile::fake()->image('foto.jpg'),
    ])->assertSessionHasErrors(['document']);

    $this->post(route('docs.store'), [
        'title' => 'PDF muy grande',
        'document' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf'),
    ])->assertSessionHasErrors(['document']);

    expect(Doc::count())->toBe(0);
});
