<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Tree;

use CliffordVickrey\Crosstabs\Exception\CrosstabOutOfBoundsException;
use RecursiveIterator;

use function count;

/**
 * @implements RecursiveIterator<int, CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode>
 * @internal
 */
class CrosstabTreeIterator implements RecursiveIterator
{
    /** @var int<0, max> */
    private int $key = 0;

    /**
     * @param list<CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode> $nodes
     */
    public function __construct(private readonly array $nodes)
    {
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->key++;
    }

    /**
     * @inheritDoc
     */
    public function key(): ?int
    {
        return $this->valid() ? $this->key : null;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->nodes[$this->key]);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @return CrosstabTreeIterator|null
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function getChildren(): ?self
    {
        if (!$this->hasChildren()) {
            return null;
        }

        /** @var list<CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode> $children */
        $children = $this->current()->children;
        return new self($children);
    }

    /**
     * @inheritDoc
     */
    public function hasChildren(): bool
    {
        if (!$this->valid()) {
            return false;
        }

        return count($this->current()->children) > 0;
    }

    /**
     * @inheritDoc
     */
    public function current(): CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode
    {
        $obj = $this->nodes[$this->key] ?? null;

        if ($obj) {
            return $obj;
        }

        throw new CrosstabOutOfBoundsException('Iterator is invalid');
    }
}
