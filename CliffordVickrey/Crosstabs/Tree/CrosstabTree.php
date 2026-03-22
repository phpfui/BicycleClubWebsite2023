<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Tree;

use CliffordVickrey\Crosstabs\Crosstab\CrosstabDataItem;
use CliffordVickrey\Crosstabs\Exception\CrosstabOutOfBoundsException;
use CliffordVickrey\Crosstabs\Options\CrosstabCategory;
use CliffordVickrey\Crosstabs\Options\CrosstabVariable;
use CliffordVickrey\Crosstabs\Options\CrosstabVariableCollection;
use Countable;
use OuterIterator;
use RecursiveIteratorIterator;

use function array_shift;
use function count;
use function is_int;
use function max;

/**
 * @implements OuterIterator<int, CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode>
 * @internal
 */
final class CrosstabTree implements Countable, OuterIterator
{
    private const TOTAL = '__TOTAL__';

    /** @var RecursiveIteratorIterator<CrosstabTreeIterator> */
    private RecursiveIteratorIterator $subject;
    /** @var int<0, max> */
    private int $count = 0;
    private ?CrosstabVariable $firstVariableInTree = null;
    private ?CrosstabVariable $lastVariableInTree = null;

    /**
     * @param CrosstabVariableCollection $collection
     * @param non-empty-string $messageTotal
     */
    public function __construct(CrosstabVariableCollection $collection, string $messageTotal = 'Total')
    {
        $nodes = $this->collectTreeNodes($collection->toArray(), $messageTotal, true);
        $it = new CrosstabTreeIterator([$nodes]);
        $this->subject = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Represents all variables and categories as a tree. At maximum depth, a placeholder for numeric values is added
     * @param list<CrosstabVariable> $variables
     * @param non-empty-string $messageTotal
     * @param bool $root
     * @param int|null $variableCount
     * @return CrosstabTreeVariableNode|CrosstabTreeDataItemNode
     */
    private function collectTreeNodes(
        array $variables,
        string $messageTotal,
        bool $root = false,
        ?int $variableCount = null
    ): CrosstabTreeVariableNode|CrosstabTreeDataItemNode {
        if (null === $variableCount) {
            $variableCount = count($variables);
            $this->count = $variableCount;
        }

        $children = $variables;

        $first = 0 === count($children) ? null : array_shift($children);

        if (null === $first) {
            // data leaf
            return new CrosstabTreeDataItemNode(CrosstabDataItem::createForLeafNode());
        }

        if ($root) {
            $this->firstVariableInTree = $first;
        }

        $this->lastVariableInTree = $first;

        // variable parent node
        $variableNode = new CrosstabTreeVariableNode($first, max($variableCount - 1, 0), count($children));

        $childNode = $this->collectTreeNodes($children, $messageTotal, variableCount: $variableCount);
        $childCategoryCount = self::getChildCategoryCount($children);
        $siblingCategoryCount = count($first->categories);

        foreach ($first->categories as $category) {
            // category node
            $variableNode->children[] = new CrosstabTreeCategoryNode(
                new CrosstabTreeCategoryPayload($category),
                $siblingCategoryCount,
                $childCategoryCount,
                [clone $childNode]
            );
        }

        // marginal total node
        $variableNode->children[] = new CrosstabTreeCategoryNode(
            new CrosstabTreeCategoryPayload(new CrosstabCategory(self::TOTAL, $messageTotal), true),
            $siblingCategoryCount,
            $childCategoryCount,
            [clone $childNode]
        );

        return $variableNode;
    }

    /**
     * @param list<CrosstabVariable> $variables
     * @return int<0, max>
     */
    private static function getChildCategoryCount(array $variables): int
    {
        if (0 === count($variables)) {
            return 0;
        }

        $lastKey = array_key_last($variables);

        $count = 0;

        foreach ($variables as $i => $variable) {
            if ($i === $lastKey) {
                continue;
            }

            if ($count < 1) {
                $count = 1;
            }

            $count *= (count($variable->categories) + 1);
        }

        return $count;
    }

    /**
     * @return CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode
     */
    public function current(): CrosstabTreeVariableNode|CrosstabTreeCategoryNode|CrosstabTreeDataItemNode
    {
        /** @psalm-suppress MixedAssignment */
        $current = $this->subject->current();

        if (
            ($current instanceof CrosstabTreeVariableNode)
            || ($current instanceof CrosstabTreeCategoryNode)
            || ($current instanceof CrosstabTreeDataItemNode)
        ) {
            return $current;
        }

        // @codeCoverageIgnoreStart
        throw new CrosstabOutOfBoundsException('Iterator is not valid');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->subject->next();
    }

    /**
     * @return int|null
     */
    public function key(): ?int
    {
        /** @psalm-suppress MixedAssignment */
        $key = $this->subject->key();

        if (is_int($key)) {
            return $key;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->subject->valid();
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->subject->rewind();
    }

    /**
     * @return CrosstabTreeIterator
     */
    public function getInnerIterator(): CrosstabTreeIterator
    {
        /** @var CrosstabTreeIterator $it */
        $it = $this->subject->getInnerIterator();
        return $it;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->subject->getDepth();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Returns a pair of the first and last variables in the crosstab (usually, the row and column vars, respectively)
     * @return array{0: CrosstabVariable, 1: CrosstabVariable}
     */
    public function getFirstAndLastVariablesInTree(): array
    {
        if (null === $this->firstVariableInTree) {
            throw new CrosstabOutOfBoundsException('No variables are in the tree');
        }

        $lastVariable = $this->lastVariableInTree ?? $this->firstVariableInTree;

        return [$this->firstVariableInTree, $lastVariable];
    }
}
