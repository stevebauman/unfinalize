<?php

declare(strict_types=1);

namespace App\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Comment\Doc;

final class RemoveFinalKeywordVisitor extends NodeVisitorAbstract
{
    public function __construct(
       private bool $markFinal = false,
       private bool $markReadOnly = false
    ) {
    }
    public function beforeTraverse(array $nodes): mixed
    {
        return null;
    }

    public function enterNode(Node $node): mixed
    {
        return null;
    }

    public function leaveNode(Node $node): mixed
    {
        if (! $node instanceof ClassMethod && ! $node instanceof Class_) {
            return null;
        }

        $docComment = $node->getDocComment()?->getText();

        if ($node->flags & Class_::MODIFIER_FINAL) {
            // remove final keyword
            $node->flags = $node->flags & ~ Class_::MODIFIER_FINAL;

            // Add @final to docblock
            if ($this->markFinal) {
                if (! $docComment) {
                    $docComment = "/**\n * @final\n */";
                } else {
                    $docComment = str_replace('*/', '*'.PHP_EOL.' * @final'.PHP_EOL.' */', $docComment);
                }

                $node->setDocComment(new Doc($docComment));
            }

        }

        if ($node->flags & Class_::MODIFIER_READONLY) {
            // remove readonly keyword
            $node->flags = $node->flags & ~ Class_::MODIFIER_READONLY;

            // Add @readonly to docblock
            if ($this->markReadOnly)
            {
                if (! $docComment) {
                    $docComment = "/**\n * @readonly\n */";
                } else {
                    $docComment = str_replace('*/', '*'.PHP_EOL.' * @readonly'.PHP_EOL.' */', $docComment);
                }

                $node->setDocComment(new Doc($docComment));
            }
        }

        return $node;
    }

    public function afterTraverse(array $nodes): mixed
    {
        return null;
    }
}
