<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Tree;

use Countable;

use function count;

/**
 * @template-covariant TPayload of object
 * @internal
 */
abstract class CrosstabTreeNode implements Countable
{
    /**
     * @param TPayload $payload
     * @param int<0, max> $siblingCount
     * @param int<0, max> $yAxisDescendantCount
     * @param list<CrosstabTreeNode<object>> $children
     * @noinspection PhpDocSignatureInspection
     */
    public function __construct(
        public object $payload,
        public int $siblingCount = 0,
        public int $yAxisDescendantCount = 0,
        public array $children = []
    ) {
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->payload = clone $this->payload;

        $clonedChildren = [];

        foreach ($this->children as $child) {
            $clonedChildren[] = clone $child;
        }

        $this->children = $clonedChildren;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->children);
    }
}
