<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Crosstab;

use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;
use JsonSerializable;

use function array_key_exists;
use function is_array;

/**
 * Encapsulate the data for a cell in a contingency table matrix
 */
class CrosstabDataItem implements JsonSerializable
{
    public const EXPECTED_FREQUENCY = 'expectedFrequency';
    public const EXPECTED_PERCENT = 'expectedPercent';
    public const IS_TOTAL = 'isTotal';
    public const FREQUENCY = 'frequency';
    public const PARAMS = 'params';
    public const PERCENT = 'percent';
    public const WEIGHTED_EXPECTED_FREQUENCY = 'weightedExpectedFrequency';
    public const WEIGHTED_EXPECTED_PERCENT = 'weightedExpectedPercent';
    public const WEIGHTED_PERCENT = 'weightedPercent';
    public const WEIGHTED_FREQUENCY = 'weightedFrequency';

    /**
     * @param int|float|null $expectedFrequency
     * @param float|null $expectedPercent
     * @param int|float|null $frequency
     * @param bool $isTotal
     * @param array<string, string|null> $params
     * @param float|null $percent
     * @param int|float|null $weightedExpectedFrequency
     * @param float|null $weightedExpectedPercent
     * @param int|float|null $weightedFrequency
     * @param float|null $weightedPercent
     */
    public function __construct(
        public int|float|null $expectedFrequency,
        public float|null $expectedPercent,
        public int|float|null $frequency,
        public bool $isTotal,
        public array $params,
        public float|null $percent,
        public int|float|null $weightedExpectedFrequency,
        public float|null $weightedExpectedPercent,
        public int|float|null $weightedFrequency,
        public float|null $weightedPercent
    ) {
    }

    /**
     * @return self
     */
    public static function createForLeafNode(): self
    {
        return new self(
            expectedFrequency: null,
            expectedPercent: null,
            frequency: null,
            isTotal: false,
            params: [],
            percent: null,
            weightedExpectedFrequency: null,
            weightedExpectedPercent: null,
            weightedFrequency: null,
            weightedPercent: null
        );
    }

    /**
     * @param array<array-key, mixed> $an_array
     * @return self
     */
    public static function __set_state(array $an_array): self
    {
        /** @var array<string, string|null> $params */
        $params = (array_key_exists(self::PARAMS, $an_array) && is_array($an_array[self::PARAMS]))
            ? $an_array[self::PARAMS]
            : [];

        return new self(
            CrosstabExtractionUtilities::extractNumeric(self::EXPECTED_FREQUENCY, $an_array),
            CrosstabExtractionUtilities::extractFloat(self::EXPECTED_PERCENT, $an_array),
            CrosstabExtractionUtilities::extractNumeric(self::FREQUENCY, $an_array),
            (bool)($an_array[self::IS_TOTAL] ?? null),
            $params,
            CrosstabExtractionUtilities::extractFloat(self::PERCENT, $an_array),
            CrosstabExtractionUtilities::extractNumeric(self::WEIGHTED_EXPECTED_FREQUENCY, $an_array),
            CrosstabExtractionUtilities::extractFloat(self::WEIGHTED_EXPECTED_PERCENT, $an_array),
            CrosstabExtractionUtilities::extractNumeric(self::WEIGHTED_FREQUENCY, $an_array),
            CrosstabExtractionUtilities::extractFloat(self::WEIGHTED_PERCENT, $an_array)
        );
    }

    /**
     * @return array{
     *     expectedFrequency: float|int|null,
     *     expectedPercent: float|null,
     *     frequency: float|int|null,
     *     isTotal: bool,
     *     params: array<string, string|null>,
     *     percent: float|null,
     *     weightedExpectedFrequency: float|int|null,
     *     weightedExpectedPercent: float|null,
     *     weightedFrequency: float|int|null,
     *     weightedPercent: float|null
     * }
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{
     *     expectedFrequency: float|int|null,
     *     expectedPercent: float|null,
     *     frequency: float|int|null,
     *     isTotal: bool,
     *     params: array<string, string|null>,
     *     percent: float|null,
     *     weightedExpectedFrequency: float|int|null,
     *     weightedExpectedPercent: float|null,
     *     weightedFrequency: float|int|null,
     *     weightedPercent: float|null
     * }
     */
    public function toArray(): array
    {
        return [
            self::EXPECTED_FREQUENCY => $this->expectedFrequency,
            self::EXPECTED_PERCENT => $this->expectedPercent,
            self::FREQUENCY => $this->frequency,
            self::IS_TOTAL => $this->isTotal,
            self::PARAMS => $this->params,
            self::PERCENT => $this->percent,
            self::WEIGHTED_EXPECTED_FREQUENCY => $this->weightedExpectedFrequency,
            self::WEIGHTED_EXPECTED_PERCENT => $this->weightedExpectedPercent,
            self::WEIGHTED_FREQUENCY => $this->weightedFrequency,
            self::WEIGHTED_PERCENT => $this->weightedPercent
        ];
    }

    /**
     * @return array{
     *     expectedFrequency: float|int|null,
     *     expectedPercent: float|null,
     *     frequency: float|int|null,
     *     isTotal: bool,
     *     params: array<string, string|null>,
     *     percent: float|null,
     *     weightedExpectedFrequency: float|int|null,
     *     weightedExpectedPercent: float|null,
     *     weightedFrequency: float|int|null,
     *     weightedPercent: float|null
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        /** @var array<string, string|null> $params */
        $params = (array_key_exists(self::PARAMS, $data) && is_array($data[self::PARAMS])) ? $data[self::PARAMS] : [];

        $this->expectedFrequency = CrosstabExtractionUtilities::extractNumeric(self::EXPECTED_FREQUENCY, $data);
        $this->expectedPercent = CrosstabExtractionUtilities::extractFloat(self::EXPECTED_PERCENT, $data);
        $this->isTotal = (bool)($data[self::IS_TOTAL] ?? null);
        $this->frequency = CrosstabExtractionUtilities::extractNumeric(self::FREQUENCY, $data);
        $this->params = $params;
        $this->percent = CrosstabExtractionUtilities::extractFloat(self::PERCENT, $data);
        $this->weightedExpectedFrequency = CrosstabExtractionUtilities::extractNumeric(
            self::WEIGHTED_EXPECTED_FREQUENCY,
            $data
        );
        $this->weightedExpectedPercent = CrosstabExtractionUtilities::extractFloat(
            self::WEIGHTED_EXPECTED_PERCENT,
            $data
        );
        $this->weightedFrequency = CrosstabExtractionUtilities::extractNumeric(self::WEIGHTED_FREQUENCY, $data);
        $this->weightedPercent = CrosstabExtractionUtilities::extractFloat(self::WEIGHTED_PERCENT, $data);
    }
}
