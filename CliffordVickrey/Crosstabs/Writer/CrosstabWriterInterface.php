<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Writer;

use CliffordVickrey\Crosstabs\Crosstab\CrosstabInterface;

interface CrosstabWriterInterface
{
    /**
     * Writes a crosstab to a string
     * @param CrosstabInterface $crosstab
     * @param array<string, mixed> $options
     * @return string
     */
    public function write(CrosstabInterface $crosstab, array $options = []): string;

    /**
     * Writes a crosstab to a file
     * @param CrosstabInterface $crosstab
     * @param non-empty-string|null $filename If empty, a temporary file will be created
     * @param array<string, mixed> $options
     * @return non-empty-string
     */
    public function writeToFile(CrosstabInterface $crosstab, ?string $filename = null, array $options = []): string;
}
