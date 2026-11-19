<?php

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a photo can be uploaded', function () {
    Storage::fake('public');

    $response = $this->post(route('photos.store'), [
        'name' => 'Prueba',
        'image' => UploadedFile::fake()->image('prueba.jpg', 100, 100),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $photo = Photo::first();

    expect($photo)->not->toBeNull()
        ->and($photo->name)->toBe('Prueba');

    Storage::disk('public')->assertExists($photo->image_path);
});

test('the public storage disk accepts a real image', function () {
    $image = UploadedFile::fake()->image('disco-real.png', 50, 50);

    $path = $image->store('photos', 'public');

    expect($path)->not->toBeFalse()
        ->and(Storage::disk('public')->exists($path))->toBeTrue();

    Storage::disk('public')->delete($path);
});

test('a photo and its image can be deleted', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('eliminar.png')->store('photos', 'public');
    $photo = Photo::create([
        'name' => 'Eliminar',
        'image_path' => $path,
    ]);

    $response = $this->delete(route('photos.destroy', $photo));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Imagen eliminada correctamente');
    $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    Storage::disk('public')->assertMissing($path);
});

test('the photos gallery page displays photos and navigation to docs', function () {
    $photo = Photo::factory()->create([
        'name' => 'Atardecer',
        'image_path' => 'photos/atardecer.jpg',
    ]);

    $response = $this->get(route('photos.index'));

    $response->assertOk()
        ->assertSee('Atardecer')
        ->assertSee(route('docs.index'));
});

test('a photo can be replaced and the old file is deleted from disk', function () {
    Storage::fake('public');

    $oldPath = UploadedFile::fake()->image('anterior.jpg')->store('photos', 'public');
    $photo = Photo::factory()->create([
        'name' => 'Paisaje',
        'image_path' => $oldPath,
    ]);

    $response = $this->put(route('photos.update', $photo), [
        'image' => UploadedFile::fake()->image('nueva.png', 200, 200),
    ]);

    $response->assertRedirect(route('photos.index'));
    $response->assertSessionHas('success');

    $photo->refresh();

    expect($photo->image_path)->not->toBe($oldPath)
        ->and($photo->image_path)->toStartWith('photos/');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($photo->image_path);
});

test('photo upload validates required fields, image format, and max size', function () {
    Storage::fake('public');

    $this->post(route('photos.store'), [])
        ->assertSessionHasErrors(['name', 'image']);

    $this->post(route('photos.store'), [
        'name' => 'No es imagen',
        'image' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors(['image']);

    $this->post(route('photos.store'), [
        'name' => 'Muy grande',
        'image' => UploadedFile::fake()->image('grande.jpg')->size(3000),
    ])->assertSessionHasErrors(['image']);

    expect(Photo::count())->toBe(0);
});
