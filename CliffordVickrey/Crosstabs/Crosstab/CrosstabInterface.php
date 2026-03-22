<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Crosstab;

use CliffordVickrey\Crosstabs\Writer\CrosstabWriterInterface;
use Countable;
use JsonSerializable;
use Traversable;

/**
 * @extends Traversable<int, CrosstabRow>
 */
interface CrosstabInterface extends Countable, Traversable, JsonSerializable
{
    /**
     * Outputs the crosstab as a string
     * @param array<string, mixed> $options Options passed to the writer
     * @param CrosstabWriterInterface|null $writer Defaults to an HTML writer
     * @return string
     */
    public function write(array $options = [], ?CrosstabWriterInterface $writer = null): string;

    /**
     * Outputs the crosstab to a file
     * @param non-empty-string|null $filename If empty, a temporary file will be created
     * @param array<string, mixed> $options Options passed to the writer
     * @param CrosstabWriterInterface|null $writer Defaults to an HTML writer
     * @return string
     */
    public function writeToFile(
        ?string $filename = null,
        array $options = [],
        ?CrosstabWriterInterface $writer = null
    ): string;
}
