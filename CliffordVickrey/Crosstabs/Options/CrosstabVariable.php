<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Options;

use CliffordVickrey\Crosstabs\Exception\CrosstabUnexpectedValueException;
use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;
use Traversable;

use function array_key_exists;
use function is_iterable;
use function is_object;
use function is_string;
use function iterator_to_array;
use function sprintf;

/**
 * Represents a categorical variable
 */
final readonly class CrosstabVariable
{
    public string $description;

    /**
     * @param non-empty-string $name
     * @param string|null $description
     * @param list<CrosstabCategory> $categories
     */
    public function __construct(public string $name, ?string $description = null, public array $categories = [])
    {
        if (null === $description || '' === $description) {
            $description = $this->name;
        }

        $this->description = $description;
    }

    /**
     * @param array<array-key, mixed> $an_array
     * @return self
     */
    public static function __set_state(array $an_array): self
    {
        $name = CrosstabExtractionUtilities::extractNonEmptyStringRequired('name', $an_array);
        $description = CrosstabExtractionUtilities::extractNonEmptyString('description', $an_array);

        $categories = [];

        $rawCategories = (array_key_exists('categories', $an_array) && is_iterable($an_array['categories']))
            ? $an_array['categories']
            : null;

        if (is_iterable($rawCategories)) {
            /** @psalm-suppress MixedAssignment */
            foreach ($rawCategories as $rawCategory) {
                $categories[] = self::parseRawCategory($rawCategory);
            }
        }

        return new self($name, $description, $categories);
    }

    /**
     * @param mixed $rawCategory
     * @return CrosstabCategory
     */
    private static function parseRawCategory(mixed $rawCategory): CrosstabCategory
    {
        if ($rawCategory instanceof CrosstabCategory) {
            return clone $rawCategory;
        }

        if (is_string($rawCategory) && '' !== $rawCategory) {
            return new CrosstabCategory($rawCategory);
        }

        if (is_object($rawCategory) && !is_iterable($rawCategory)) {
            $rawCategory = (array)$rawCategory;
        }

        if (!is_iterable($rawCategory)) {
            throw CrosstabUnexpectedValueException::fromValue(
                $rawCategory,
                sprintf('non-empty-string, object, array, or %s', Traversable::class)
            );
        }

        if ($rawCategory instanceof Traversable) {
            $categoryArray = iterator_to_array($rawCategory);
        } else {
            $categoryArray = $rawCategory;
        }

        return CrosstabCategory::__set_state($categoryArray);
    }
}
