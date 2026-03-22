<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Options;

use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;

/**
 * Represents one of a variable's categories
 */
final readonly class CrosstabCategory
{
    public string $description;

    /**
     * @param non-empty-string $name
     * @param string|null $description
     */
    public function __construct(public string $name, ?string $description = null)
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
        return new self($name, $description);
    }
}
