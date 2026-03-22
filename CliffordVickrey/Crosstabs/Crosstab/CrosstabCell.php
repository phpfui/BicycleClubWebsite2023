<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Crosstab;

use CliffordVickrey\Crosstabs\Utilities\CrosstabExtractionUtilities;
use JsonSerializable;

use function array_key_exists;
use function is_array;
use function is_scalar;

/**
 * Table cell in the crosstab. Roughly corresponds to an HTML <td> or <th> element. Class constants are possible "class"
 * attributes set by the builder and describing the cell's type/appearance.
 */
class CrosstabCell implements JsonSerializable
{
    public const APPEARANCE_BOTTOM_CELL = '__crosstab-bottom-cell';
    public const APPEARANCE_CELL = '__crosstab-cell';
    public const APPEARANCE_DATA_TYPE = '__crosstab-data-type';
    public const APPEARANCE_TITLE = '__crosstab-title';
    public const APPEARANCE_TOTAL = '__crosstab-total';
    public const APPEARANCE_TOTAL_LABEL = '__crosstab-total-label';
    public const APPEARANCE_X_AXIS = '__crosstab-x-axis';
    public const APPEARANCE_X_AXIS_CATEGORY_LABEL = '__crosstab-x-axis-category-label';
    public const APPEARANCE_Y_AXIS = '__crosstab-y-axis';
    public const APPEARANCE_Y_AXIS_CATEGORY_LABEL = '__crosstab-y-axis-category-label';
    public const APPEARANCE_Y_AXIS_CATEGORY_LABEL_SIMPLE = '__crosstab-y-axis-category-label-simple';
    public const APPEARANCE_Y_AXIS_VARIABLE_LABEL = '__crosstab-y-axis-variable-label';

    /** @var array<string, string> */
    public array $attributes = [];
    /** @var positive-int */
    public int $colspan = 1;
    public bool $isHeader = false;
    public bool|float|int|string|null $rawValue = null;
    /** @var positive-int */
    public int $rowspan = 1;
    public string $textContent = '';

    /**
     * @param array<array-key, mixed> $an_array
     * @return self
     */
    public static function __set_state(array $an_array): self
    {
        $self = new self();
        $self->hydrate($an_array);
        return $self;
    }

    /**
     * @param array<array-key, mixed> $data
     * @return void
     */
    private function hydrate(array $data): void
    {
        /** @var array<string, string> $attributes */
        $attributes = array_key_exists('attributes', $data) && is_array($data['attributes']) ? $data['attributes'] : [];

        $this->attributes = $attributes;
        $this->colspan = CrosstabExtractionUtilities::extractPositiveInt('colspan', $data) ?? 1;
        $this->isHeader = (bool)($data['isHeader'] ?? false);

        $rawValue = (array_key_exists('rawValue', $data) && is_scalar($data['rawValue'])) ? $data['rawValue'] : null;

        $this->rawValue = $rawValue;
        $this->rowspan = CrosstabExtractionUtilities::extractPositiveInt('rowspan', $data) ?? 1;
        $this->textContent = CrosstabExtractionUtilities::extractString('textContent', $data);
    }

    /**
     * @param string $textContent
     * @param positive-int $colspan
     * @param positive-int $rowspan
     * @param array<string, string> $attributes
     * @return self
     */
    public static function header(string $textContent, int $colspan = 1, int $rowspan = 1, array $attributes = []): self
    {
        $self = new self();
        $self->attributes = $attributes;
        $self->colspan = $colspan;
        $self->isHeader = true;
        $self->rowspan = $rowspan;
        $self->textContent = $textContent;
        return $self;
    }

    /**
     * @param string $textContent
     * @param bool|float|int|string|null $rawValue
     * @param array<string, string> $attributes
     * @return self
     */
    public static function dataCell(
        string $textContent,
        bool|float|int|string|null $rawValue = null,
        array $attributes = []
    ): self {
        $self = new self();
        $self->attributes = $attributes;
        $self->rawValue = $rawValue;
        $self->textContent = $textContent;
        return $self;
    }

    /**
     * @return array{
     *     attributes: array<string, string>,
     *     colspan: int<0, max>,
     *     isHeader: bool,
     *     rawValue: bool|float|int|string|null,
     *     rowspan: int<0, max>,
     *     textContent: string
     * }
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{
     *     attributes: array<string, string>,
     *     colspan: int<0, max>,
     *     isHeader: bool,
     *     rawValue: bool|float|int|string|null,
     *     rowspan: int<0, max>,
     *     textContent: string
     * }
     */
    public function toArray(): array
    {
        return [
            'attributes' => $this->attributes,
            'colspan' => $this->colspan,
            'isHeader' => $this->isHeader,
            'rawValue' => $this->rawValue,
            'rowspan' => $this->rowspan,
            'textContent' => $this->textContent
        ];
    }

    /**
     * @return array{
     *     attributes: array<string, string>,
     *     colspan: int<0, max>,
     *     isHeader: bool,
     *     rawValue: bool|float|int|string|null,
     *     rowspan: int<0, max>,
     *     textContent: string
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
        $this->hydrate($data);
    }
}
