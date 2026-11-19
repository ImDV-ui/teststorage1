<?php

test('the application redirects root to the photo gallery', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('photos.index'));
});
