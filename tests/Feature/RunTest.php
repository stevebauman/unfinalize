<?php

use Illuminate\Support\Facades\File;

function stubsPath($path = ''): string {
    return __DIR__ . '/stubs/' . $path;
}

beforeEach(function () {
    // Ensure stubs directory exists
    if (!File::exists(stubsPath())) {
        File::makeDirectory(stubsPath());
    }

    // Create a sample composer.json file in stubs directory for testing.
    File::put(stubsPath('composer.json'), json_encode([
        'unfinalize' => ['package/to/unfinalize']
    ]));
});

afterEach(function () {
    // Clean up the sample composer.json file in stubs directory.
    File::delete(stubsPath('composer.json'));
});

it('errors if composer.json is not present', function () {
    File::delete(stubsPath('composer.json'));

    $this->artisan('run', ['--dir' => $dir = stubsPath()])
        ->assertFailed()
        ->expectsOutputToContain("composer.json is not present in directory [$dir].");
});

it('errors if composer.json contains invalid JSON', function () {
    File::put(stubsPath('composer.json'), 'invalid JSON');

    $this->artisan('run', ['--dir' => stubsPath()])
        ->assertFailed()
        ->expectsOutputToContain("composer.json contains invalid JSON.");
});

it('outputs info if no packages to unfinalize are configured', function () {
    File::put(stubsPath('composer.json'), json_encode([]));

    $this->artisan('run', ['--dir' => stubsPath()])
        ->assertSuccessful()
        ->expectsOutputToContain("composer.json does not contain any configured vendor paths to unfinalize.");
});
