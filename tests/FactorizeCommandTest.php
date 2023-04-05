<?php

use function Pest\Laravel\artisan;
use Illuminate\Support\Facades\Artisan;

it('registers the command', function () {
    $registered = array_key_exists('smousss:factorize', Artisan::all());

    expect($registered)->toBeTrue();
});

it("throws an exception when the secret key isn't set", function () {
    artisan('smousss:factorize')->assertExitCode(1);
});
