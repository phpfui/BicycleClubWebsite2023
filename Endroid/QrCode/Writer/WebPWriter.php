<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\Result\WebPResult;

final readonly class WebPWriter implements WriterInterface, ValidatingWriterInterface
{
    public const WRITER_OPTION_QUALITY = 'quality';

    public function __construct(
        private GdWriter $gdWriter = new GdWriter(),
    ) {
    }

    public function write(QrCodeInterface $qrCode, ?LogoInterface $logo = null, ?LabelInterface $label = null, array $options = []): ResultInterface
    {
        if (!isset($options[self::WRITER_OPTION_QUALITY])) {
            $options[self::WRITER_OPTION_QUALITY] = -1;
        }

        $gdResult = $this->gdWriter->write($qrCode, $logo, $label, $options);

        return new WebPResult($gdResult->getMatrix(), $gdResult->getImage(), $options[self::WRITER_OPTION_QUALITY]);
    }

    public function validateResult(ResultInterface $result, string $expectedData): void
    {
        $this->gdWriter->validateResult($result, $expectedData);
    }
}
