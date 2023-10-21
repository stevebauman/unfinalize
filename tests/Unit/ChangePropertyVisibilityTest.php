<?php

use App\ChangePropertyVisibilityFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Tokenizer\Tokens;

it('changes private visibility properties to protected', function (string $before, string $after) {
    $fixer = new ChangePropertyVisibilityFixer();
    $fixer->configure(['visibility' => 'protected']);

    $tokens = Tokens::fromCode($before);

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    expect($tokens->generateCode())->toBe($after);
})->with([
    [
        '<?php class Sample { private $example; }',
        '<?php class Sample { protected $example; }'
    ],
    [
        '<?php class AnotherSample { private $anotherExample; private $secondExample; }',
        '<?php class AnotherSample { protected $anotherExample; protected $secondExample; }'
    ],
    [
        // Test to ensure it doesn't change private methods
        '<?php class Sample { private function example() {} }',
        '<?php class Sample { private function example() {} }'
    ],
]);

it('changes private visibility properties to public', function (string $before, string $after) {
    $fixer = new ChangePropertyVisibilityFixer();
    $fixer->configure(['visibility' => 'public']);

    $tokens = Tokens::fromCode($before);

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    expect($tokens->generateCode())->toBe($after);
})->with([
    [
        '<?php class Sample { private $example; }',
        '<?php class Sample { public $example; }'
    ],
    [
        '<?php class AnotherSample { private $anotherExample; private $secondExample; }',
        '<?php class AnotherSample { public $anotherExample; public $secondExample; }'
    ],
    [
        // Test to ensure it doesn't change private methods
        '<?php class Sample { private function example() {} }',
        '<?php class Sample { private function example() {} }'
    ],
]);

it('fails changing to an unknown visibility', function () {
    $fixer = new ChangePropertyVisibilityFixer();
    $fixer->configure(['visibility' => 'invalid']);

    $tokens = Tokens::fromCode(
        '<?php class Sample { private $example; }'
    );

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);
})->throws(InvalidFixerConfigurationException::class);
