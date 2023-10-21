<?php

use App\ChangeMethodVisibilityFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Tokenizer\Tokens;

it('has the correct name', function () {
    $fixer = new ChangeMethodVisibilityFixer();
    expect($fixer->getName())->toBe('Unfinalize/change_method_visibility');
});

it('changes private visibility to protected', function (string $before, string $after) {
    $fixer = new ChangeMethodVisibilityFixer();
    $fixer->configure(['visibility' => 'protected']);

    $tokens = Tokens::fromCode($before);

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    expect($tokens->generateCode())->toBe($after);
})->with([
    [
        '<?php class Sample { private function example() {} }',
        '<?php class Sample { protected function example() {} }'
    ],
    [
        '<?php class AnotherSample { private function anotherExample() {} private function secondExample() {} }',
        '<?php class AnotherSample { protected function anotherExample() {} protected function secondExample() {} }'
    ],
]);

it('changes private visibility to public', function (string $before, string $after) {
    $fixer = new ChangeMethodVisibilityFixer();
    $fixer->configure(['visibility' => 'public']);

    $tokens = Tokens::fromCode($before);

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    expect($tokens->generateCode())->toBe($after);
})->with([
    [
        '<?php class Sample { private function example() {} }',
        '<?php class Sample { public function example() {} }'
    ],
    [
        '<?php class AnotherSample { private function anotherExample() {} private function secondExample() {} }',
        '<?php class AnotherSample { public function anotherExample() {} public function secondExample() {} }'
    ]
]);

it('does not change private properties', function (string $before, string $after) {
    $fixer = new ChangeMethodVisibilityFixer();
    $fixer->configure(['visibility' => 'protected']);

    $tokens = Tokens::fromCode($before);

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);

    expect($tokens->generateCode())->toBe($after);
})->with([
    [
        '<?php class Sample { private $example; }',
        '<?php class Sample { private $example; }'
    ],
]);


it('fails changing to an unknown visibility', function () {
    $fixer = new ChangeMethodVisibilityFixer();
    $fixer->configure(['visibility' => 'invalid']);

    $tokens = Tokens::fromCode(
        '<?php class Sample { private function example() {} }'
    );

    $fixer->fix(new SplFileInfo(__FILE__), $tokens);
})->throws(InvalidFixerConfigurationException::class);
