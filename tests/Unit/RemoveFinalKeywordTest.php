<?php

use App\RemoveFinalKeywordFixer;
use PhpCsFixer\Tokenizer\Tokens;

it('has the correct name', function () {
    $fixer = new RemoveFinalKeywordFixer();
    expect($fixer->getName())->toBe('Unfinalize/remove_final_keyword');
});

it('has the correct definition', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $definition = $fixer->getDefinition();

    expect($definition->getSummary())->toBe('Removes "final" keyword from classes and methods.');
    expect($definition->getCodeSamples())->toHaveCount(2);
});

it('is a candidate for tokens with T_FINAL', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $tokens = Tokens::fromCode("<?php\nfinal class MyClass {}");

    expect($fixer->isCandidate($tokens))->toBeTrue();
});

it('is not a candidate for tokens without T_FINAL', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $tokens = Tokens::fromCode("<?php\nclass MyClass {}");

    expect($fixer->isCandidate($tokens))->toBeFalse();
});

it('removes the final keyword from class', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $tokens = Tokens::fromCode("<?php\nfinal class MyClass {}");

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->not->toContain('final');
});

it('removes the final keyword from file with multiple classes', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $tokens = Tokens::fromCode(<<<PHP
    <?php
    final class Foo {}
    final class Bar {}
    PHP);

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toBe(<<<PHP
    <?php
    class Foo {}
    class Bar {}
    PHP);
});

it('removes the final keyword from function', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $tokens = Tokens::fromCode(<<<PHP
    <?php
    class MyClass {
        final public function foo() {}
    }
    PHP);

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toBe(<<<PHP
    <?php
    class MyClass {
        public function foo() {}
    }
    PHP);
});

it('annotates as class final when annotation is set', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $fixer->configure(['annotate' => 'final']);
    $tokens = Tokens::fromCode("<?php\nfinal class MyClass {}");

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toContain(<<<PHP
    <?php
    /**
     * @final
     */
    class MyClass {}
    PHP);
});

it('annotates class as final when configuration is set and multi-line doc block already exists', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $fixer->configure(['annotate' => 'final']);
    $tokens = Tokens::fromCode(<<<PHP
    <?php
    /**
     * Foo
     */
    final class MyClass {}
    PHP);

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toContain(<<<PHP
    <?php
    /**
     * Foo
     * @final
     */
    class MyClass {}
    PHP);
});

it('annotates class as final when configuration is set and single line doc block already exists', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $fixer->configure(['annotate' => 'final']);
    $tokens = Tokens::fromCode(<<<PHP
    <?php
    // Foo
    final class MyClass {}
    PHP);

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toContain(<<<PHP
    <?php
    // Foo
    /**
     * @final
     */
    class MyClass {}
    PHP);
});

it('annotates method as final when configuration is set', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $fixer->configure(['annotate' => 'final']);
    $tokens = Tokens::fromCode(<<<PHP
    <?php
    class MyClass {
        final public function foo() {}
    }
    PHP);

    $fixer->applyFix(new SplFileInfo('test.php'), $tokens);

    expect($tokens->generateCode())->toContain(<<<PHP
    <?php
    class MyClass {
        /**
         * @final
         */
        public function foo() {}
    }
    PHP);
});

it('is not risky', function () {
    $fixer = new RemoveFinalKeywordFixer();
    expect($fixer->isRisky())->toBeFalse();
});

it('supports PHP files', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $file = new SplFileInfo('test.php');

    expect($fixer->supports($file))->toBeTrue();
});

it('does not support non-PHP files', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $file = new SplFileInfo('test.txt');

    expect($fixer->supports($file))->toBeFalse();
});

it('has correct priority', function () {
    $fixer = new RemoveFinalKeywordFixer();
    expect($fixer->getPriority())->toBe(0);
});

it('has correct configuration definition', function () {
    $fixer = new RemoveFinalKeywordFixer();
    $configDefinition = $fixer->getConfigurationDefinition();

    $options = $configDefinition->getOptions();

    expect($options)->toHaveCount(1);
    expect($options[0]->getName())->toBe('annotate');
});

