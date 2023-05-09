<?php

namespace App;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use SplFileInfo;

class RemoveFinalKeywordFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /**
     * Get the name of the fixer.
     */
    public function getName(): string
    {
        return 'Unfinalize/remove_final_keyword';
    }

    /**
     * Get the definition of the fixer.
     */
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Removes "final" keyword from classes and methods.',
            [
                new CodeSample("<?php\n\nfinal class MyClass {}\n"),
                new CodeSample("<?php\n\nfinal public function my_method() {} \n")
            ]
        );
    }

    /**
     * Determine if the fixer is a candidate for given Tokens collection.
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FINAL);
    }

    /**
     * Apply the changes to the file.
     */
    public function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_FINAL)) {
                continue;
            }

            $tokens->clearAt($index);
            $tokens->clearAt(++$index);

            if ($this->configuration['mark_internal']) {
                $this->markAsInternal($tokens, $index);
            }
        }
    }

    /**
     * Mark the construct as internal.
     */
    protected function markAsInternal(Tokens $tokens, int $index): void
    {
        $docToken = $tokens->getPrevTokenOfKind($index, [[T_DOC_COMMENT]]);

        if ($docToken) {
            $docblock = $tokens[$docToken];

            // Modify the doc block as needed
            $originalDocBlock = $docblock->getContent();

            $modifiedDocBlock = $this->addInternalAnnotation($originalDocBlock);

            if ($originalDocBlock !== $modifiedDocBlock) {
                $tokens[$docToken] = new Token([T_DOC_COMMENT, $modifiedDocBlock]);
            }
        } else {
            $tokens->insertAt(--$index, new Token([
                T_DOC_COMMENT,
                "/**\n * @internal\n */"
            ]));

            $tokens->insertAt(++$index, new Token([T_WHITESPACE, "\n"]));
        }
    }

    /**
     * Add an "@internal" annotation to the doc block.
     */
    protected function addInternalAnnotation(string $docBlock): string
    {
        if (str_contains($docBlock, '@internal')) {
            return $docBlock;
        }

        // Add @internal before the closing "*/"
        return preg_replace('/\s*\*\/\s*$/', "\n * @internal\n */", $docBlock);
    }

    /**
     * Determine if the fixer is risky.
     */
    public function isRisky(): bool
    {
        return false;
    }

    /**
     * Get the priority of the fixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Determine if the file is supported by the fixer.
     */
    public function supports(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'php';
    }

    /**
     * Create the configuration definition.
     */
    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('mark_internal', 'Mark final classes as internal.'))
                ->setAllowedValues([true, false])
                ->setDefault(false)
                ->getOption(),
        ]);
    }
}
