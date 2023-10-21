<?php

namespace App;

use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use RuntimeException;

/** @mixin \PhpCsFixer\AbstractFixer */
trait ResolvesVisibilityProperties
{
    /**
     * Get the visibility properties for a new token.
     */
    public function getVisibilityProperties(string $visibility): array
    {
        return match($visibility) {
            'public' =>  [T_PUBLIC, 'public'],
            'protected' => [T_PROTECTED, 'protected'],
            default => throw new RuntimeException("Cannot change method visibility. Visibility [$visibility] is invalid.")
        };
    }

    /**
     * Create the configuration definition.
     */
    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('visibility', 'The new visibility to apply.'))
                ->setAllowedValues(['protected', 'public'])
                ->getOption(),
        ]);
    }
}
