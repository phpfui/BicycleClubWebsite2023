<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\GifResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

final readonly class GifWriter implements WriterInterface, ValidatingWriterInterface
{
    public function __construct(
        private GdWriter $gdWriter = new GdWriter(),
    ) {
    }

    public function write(QrCodeInterface $qrCode, ?LogoInterface $logo = null, ?LabelInterface $label = null, array $options = []): ResultInterface
    {
        $gdResult = $this->gdWriter->write($qrCode, $logo, $label, $options);

        return new GifResult($gdResult->getMatrix(), $gdResult->getImage());
    }

    public function validateResult(ResultInterface $result, string $expectedData): void
    {
        $this->gdWriter->validateResult($result, $expectedData);
    }
}
