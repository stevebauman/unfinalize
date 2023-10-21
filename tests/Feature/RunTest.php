<?php

use Illuminate\Support\Facades\File;

function stubsPath($path = ''): string {
    return __DIR__ . '/stubs/' . $path;
}

beforeEach(function () {
    // Create a sample composer.json file in stubs directory for testing.
    File::put(stubsPath('composer.json'), json_encode([
        'unfinalize' => ['provider/package']
    ]));
});

afterEach(function () {
    // Clean up the sample composer.json file in stubs directory.
    File::delete(stubsPath('composer.json'));
    File::deleteDirectory(stubsPath('vendor'));
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

it('fixes vendor file', function (string $before, string $after) {
    File::makeDirectory(stubsPath('vendor/provider/package'), recursive: true, force: true);
    File::put($file = stubsPath('vendor/provider/package/File.php'), $before);

    $this->artisan('run', [
        '--dir' => stubsPath(),
        '--annotate' => 'internal',
        '--methods' => 'public',
        '--properties' => 'protected',
    ])->assertSuccessful();

    expect(File::get($file))->toEqual($after);
})->with([
    [
        <<<PHP
        <?php
        final class Foo { private \$bar; private function baz() {} }
        PHP,

        <<<PHP
        <?php
        /**
         * @internal
         */
        class Foo { protected \$bar; public function baz() {} }
        PHP
    ]
]);

