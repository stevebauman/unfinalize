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
        return $tokens->isAnyTokenKindsFound([T_FINAL]);
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

            if (! $this->configuration['annotate']) {
                continue;
            }

            $this->annotate($tokens, $index, $this->configuration['annotate']);
        }
    }

    /**
     * Annotate the construct with a doc block.
     */
    protected function annotate(Tokens $tokens, int $index, string $annotation): void
    {
        $spaces = $this->getIndentSpacing($tokens, $index);

        $docBlock = $tokens[$docIndex = $tokens->getPrevNonWhitespace($index)];

        // Modify the current docblock and add the annotation to it.
        if ($docBlock->isGivenKind(T_DOC_COMMENT)) {
            $docblock = $tokens[$docIndex];

            $originalDocBlock = $docblock->getContent();

            $modifiedDocBlock = $this->addAnnotation($originalDocBlock, $spaces, $annotation);

            if ($originalDocBlock !== $modifiedDocBlock) {
                $tokens[$docIndex] = new Token([T_DOC_COMMENT, $modifiedDocBlock]);
            }

            return;
        }

        // Insert a new doc block and add the annotation to it.
        $tokens->insertAt(--$index, new Token([
            T_DOC_COMMENT,
            "/**\n $spaces* @$annotation\n$spaces */"
        ]));

        $tokens->insertAt(++$index, new Token([T_WHITESPACE, "\n".$spaces]));
    }

    /**
     * Get the indent spacing for the construct.
     */
    protected function getIndentSpacing(Tokens $tokens, int $index): string
    {
        $previousWhitespaceIndex = $tokens->getPrevTokenOfKind($index, [[T_WHITESPACE]]);

        $previousWhitespaceContent = $previousWhitespaceIndex ? $tokens[$previousWhitespaceIndex]->getContent() : '';

        $lastLineBreakPos = strrpos($previousWhitespaceContent, "\n");

        // Extract the substring after the last line break.
        $substring = substr($previousWhitespaceContent, $lastLineBreakPos + 1);

        // Use a regular expression to match spaces.
        preg_match_all('/\s/', $substring, $matches);

        return implode($matches[0] ?? []);
    }

    /**
     * Add the annotation to the doc block.
     */
    protected function addAnnotation(string $docBlock, string $spaces, string $annotation): string
    {
        if (str_contains($docBlock, "@$annotation")) {
            return $docBlock;
        }

        // Add the annotation before the closing "*/".
        return preg_replace('/\s*\*\/\s*$/', "\n $spaces* @$annotation\n$spaces */", $docBlock);
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
            (new FixerOptionBuilder('annotate', 'Annotate final classes and methods with a doc block.'))
                ->setDefault(null)
                ->getOption(),
        ]);
    }
}
