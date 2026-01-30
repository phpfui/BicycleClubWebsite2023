<?php

declare(strict_types=1);

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\GdResult;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * @deprecated since 6.0, use GdWriter instead. This class will be removed in 7.0.
 */
abstract class AbstractGdWriter implements WriterInterface, ValidatingWriterInterface
{
    private GdWriter $gdWriter;

    public function __construct()
    {
        $this->gdWriter = new GdWriter();
    }

    public function write(QrCodeInterface $qrCode, ?LogoInterface $logo = null, ?LabelInterface $label = null, array $options = []): GdResult
    {
        return $this->gdWriter->write($qrCode, $logo, $label, $options);
    }

    public function validateResult(ResultInterface $result, string $expectedData): void
    {
        $this->gdWriter->validateResult($result, $expectedData);
    }
}
