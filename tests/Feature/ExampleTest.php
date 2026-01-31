<?php

test('returns a redirect response', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(302);
});