<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Writer;

use CliffordVickrey\Crosstabs\Crosstab\CrosstabInterface;
use CliffordVickrey\Crosstabs\Exception\CrosstabRuntimeException;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;

abstract class AbstractCrosstabWriter implements CrosstabWriterInterface
{
    /**
     * @inheritDoc
     */
    public function writeToFile(CrosstabInterface $crosstab, ?string $filename = null, array $options = []): string
    {
        $output = $this->prepareOutputForFile($this->write($crosstab, $options), $options);

        $filename = $filename ?? $this->getTemporaryFilename();

        $this->filePutContents($filename, $output);

        return $filename;
    }

    /**
     * @param string $output
     * @param array<string, mixed> $options
     * @return string
     * @codeCoverageIgnore
     */
    protected function prepareOutputForFile(string $output, array $options): string
    {
        return $output;
    }

    /**
     * @return non-empty-string
     * @codeCoverageIgnore
     */
    protected function getTemporaryFilename(): string
    {
        $tempDir = sys_get_temp_dir();

        $tempNam = tempnam($tempDir, 'tab');

        if (false === $tempNam || '' === $tempNam) {
            throw new CrosstabRuntimeException('Could not create temporary filename');
        }

        return $tempNam;
    }

    /**
     * @param non-empty-string $filename
     * @param string $contents
     * @return void
     */
    protected function filePutContents(string $filename, string $contents): void
    {
        if (false === file_put_contents($filename, $contents)) {
            // @codeCoverageIgnoreStart
            throw new CrosstabRuntimeException("Could not write to $filename");
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function fileGetContents(string $filename): string
    {
        $contents = file_get_contents($filename);

        if (false === $contents) {
            // @codeCoverageIgnoreStart
            throw new CrosstabRuntimeException("Could not open $filename for reading");
            // @codeCoverageIgnoreEnd
        }

        return $contents;
    }
}
