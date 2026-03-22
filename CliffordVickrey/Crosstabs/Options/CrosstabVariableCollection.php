<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Options;

use ArrayIterator;
use CliffordVickrey\Crosstabs\Exception\CrosstabUnexpectedValueException;
use Countable;
use IteratorAggregate;
use Traversable;

use function count;
use function is_array;
use function is_iterable;
use function is_object;
use function is_string;
use function iterator_to_array;
use function sprintf;

/**
 * Represents all variables displayed in the crosstab
 * @implements IteratorAggregate<int, CrosstabVariable>
 */
final readonly class CrosstabVariableCollection implements Countable, IteratorAggregate
{
    /**
     * @param list<CrosstabVariable> $variables
     */
    public function __construct(private array $variables)
    {
    }

    /**
     * @param array<array-key, mixed> $an_array
     * @return self
     * @psalm-suppress MixedAssignment
     */
    public static function __set_state(array $an_array): self
    {
        $arr = (isset($an_array['variables']) && is_array($an_array['variables'])) ? $an_array['variables'] : $an_array;

        $variables = [];

        foreach ($arr as $rawVariable) {
            $variables[] = self::parseRawVariable($rawVariable);
        }

        return new self($variables);
    }

    /**
     * @param mixed $rawVariable
     * @return CrosstabVariable
     */
    private static function parseRawVariable(mixed $rawVariable): CrosstabVariable
    {
        if ($rawVariable instanceof CrosstabVariable) {
            return clone $rawVariable;
        }

        if (is_string($rawVariable) && '' !== $rawVariable) {
            return new CrosstabVariable($rawVariable);
        }

        if (is_object($rawVariable) && !is_iterable($rawVariable)) {
            $rawVariable = (array)$rawVariable;
        }

        if (!is_iterable($rawVariable)) {
            throw CrosstabUnexpectedValueException::fromValue(
                $rawVariable,
                sprintf('non-empty-string, object, array, or %s', Traversable::class)
            );
        }

        if ($rawVariable instanceof Traversable) {
            $variableArray = iterator_to_array($rawVariable);
        } else {
            $variableArray = $rawVariable;
        }

        return CrosstabVariable::__set_state($variableArray);
    }

    /**
     * Do we have any variables? Do all variables have categories?
     * @return bool
     */
    public function isValid(): bool
    {
        if (0 === count($this->variables)) {
            return false;
        }

        foreach ($this->variables as $variable) {
            if (empty($variable->categories)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return ArrayIterator<positive-int, CrosstabVariable>
     */
    public function getIterator(): ArrayIterator
    {
        /** @var ArrayIterator<positive-int, CrosstabVariable> $it */
        $it = new ArrayIterator($this->variables);
        return $it;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * @return list<CrosstabVariable>
     */
    public function toArray(): array
    {
        return $this->variables;
    }
}
