<?php

declare(strict_types=1);

namespace CliffordVickrey\Crosstabs\Options;

use CliffordVickrey\Crosstabs\Exception\CrosstabUnexpectedValueException;
use Traversable;

use function count;
use function get_object_vars;
use function is_array;
use function is_string;
use function iterator_to_array;

/**
 *
 */
class CrosstabOptions
{
    /** @var non-empty-string|null */
    protected ?string $colVariableDescription = null;
    /** @var non-empty-string|null */
    protected ?string $colVariableName = null;
    /** @var list<string|array{name: non-empty-string, description?: string}|CrosstabCategory> */
    protected array $colVariableCategories = [];
    /** @var non-empty-string|null */
    protected ?string $keyFrequency = 'n';
    /** @var non-empty-string|null */
    protected ?string $keyWeightedFrequency = 'weight';
    /** @var list<CrosstabVariable> */
    protected array $layers = [];
    /** @var non-empty-string */
    protected string $locale = 'en_US';
    /** @var positive-int */
    protected int $mathematicalScale = 14;
    /** @var non-empty-string */
    protected string $messageExpectedFrequency = 'Frequency (Expected)';
    /** @var non-empty-string */
    protected string $messageExpectedPercent = '% (Expected)';
    /** @var non-empty-string */
    protected string $messageFrequency = 'Frequency';
    /** @var non-empty-string */
    protected string $messageNil = '-';
    /** @var non-empty-string */
    protected string $messageNoData = 'There is no data to display';
    /** @var non-empty-string */
    protected string $messagePercent = '%';
    /** @var non-empty-string */
    protected string $messageTotal = 'Total';
    /** @var non-empty-string */
    protected string $messageWeightedExpectedFrequency = 'Expected Frequency (Weighted)';
    /** @var non-empty-string */
    protected string $messageWeightedExpectedPercent = 'Expected % (Weighted)';
    /** @var non-empty-string */
    protected string $messageWeightedFrequency = 'Frequency (Weighted)';
    /** @var non-empty-string */
    protected string $messageWeightedPercent = '% (Weighted)';
    protected CrosstabPercentType $percentType = CrosstabPercentType::Total;
    /** @var iterable<array-key, mixed> */
    protected iterable $rawData = [];
    /** @var non-empty-string|null */
    protected ?string $rowVariableDescription = null;
    /** @var non-empty-string|null */
    protected ?string $rowVariableName = null;
    /** @var list<string|array{name: non-empty-string, description?: string}|CrosstabCategory> */
    protected array $rowVariableCategories = [];
    /** @var int<0, max> */
    protected int $scaleDecimal = 2;
    /** @var int<0, max> */
    protected int $scalePercent = 2;
    protected bool $showExpectedFrequency = false;
    protected bool $showExpectedPercent = false;
    protected bool $showFrequency = true;
    protected bool $showPercent = false;
    protected bool $showWeightedExpectedFrequency = false;
    protected bool $showWeightedExpectedPercent = false;
    protected bool $showWeightedFrequency = false;
    protected bool $showWeightedPercent = false;
    /** @var non-empty-string|null */
    protected ?string $title = null;

    /**
     * Adds a layer variable to the crosstab
     * @param CrosstabVariable|array<string, mixed>|string $layer
     * @param string|null $description
     * @param list<array{name: non-empty-string, description?: string}>|list<CrosstabCategory>|null $categories
     * @return void
     */
    public function addLayer(
        CrosstabVariable|array|string $layer,
        ?string $description = null,
        ?array $categories = []
    ): void {
        if (is_array($layer)) {
            if (null !== $description && '' !== $description) {
                $layer['description'] = $description;
            }

            $layer = CrosstabVariable::__set_state($layer);
        }

        if (is_string($layer)) {
            if ('' === $layer) {
                throw CrosstabUnexpectedValueException::fromValue($layer, 'CrosstabLayer, array, or non-empty-string');
            }

            $layer = new CrosstabVariable($layer, $description);
        } elseif (null !== $description && '' !== $description) {
            $layer = new CrosstabVariable($layer->name, $description);
        }

        if (!(null === $categories || 0 === count($categories))) {
            $layer = CrosstabVariable::__set_state([
                'name' => $layer->name,
                'description' => $layer->description,
                'categories' => $categories
            ]);
        }

        $this->layers[] = $layer;
    }

    /**
     * Exposes the options as an array
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Sets the label of a column variable. If none is set, use the name
     * @param non-empty-string|null $colVariableDescription
     */
    public function setColVariableDescription(?string $colVariableDescription): void
    {
        $this->colVariableDescription = $colVariableDescription;
    }

    /**
     * Sets the name of the column variable in the raw data
     * @param non-empty-string|null $colVariableName
     */
    public function setColVariableName(?string $colVariableName): void
    {
        $this->colVariableName = $colVariableName;
    }

    /**
     * Explicitly defines the categories of the column variable in the raw data; otherwise, they are inferred. Useful
     * for relabeling/recoding categorical values
     * @param list<string|array{name: non-empty-string, description?: string}|CrosstabCategory> $colVariableCategories
     */
    public function setColVariableCategories(array $colVariableCategories): void
    {
        $this->colVariableCategories = $colVariableCategories;
    }

    /**
     * Sets the key in the source data representing the number of cases in a row. If this information is absent, each
     * row will be treated as a single case
     * @param non-empty-string|null $keyFrequency
     * @return void
     */
    public function setKeyFrequency(?string $keyFrequency): void
    {
        $this->keyFrequency = $keyFrequency;
    }

    /**
     * Sets the key in the source data representing row weight. If this information is absent, each row will be weighed
     * equally
     * @param non-empty-string|null $keyWeightedFrequency
     * @return void
     */
    public function setKeyWeightedFrequency(?string $keyWeightedFrequency): void
    {
        $this->keyWeightedFrequency = $keyWeightedFrequency;
    }

    /**
     * Sets multiple layer variables
     * @param CrosstabVariableCollection|iterable<array-key, mixed> $layers
     * @return void
     */
    public function setLayers(CrosstabVariableCollection|iterable $layers = []): void
    {
        $this->layers = [];
        $this->addLayers($layers);
    }

    /**
     * Adds multiple layer variables
     * @param CrosstabVariableCollection|iterable<array-key, mixed> $layers
     * @return void
     */
    public function addLayers(CrosstabVariableCollection|iterable $layers): void
    {
        if (!($layers instanceof CrosstabVariableCollection)) {
            if ($layers instanceof Traversable) {
                $layersArr = iterator_to_array($layers);
            } else {
                $layersArr = $layers;
            }

            $layers = CrosstabVariableCollection::__set_state(['variables' => $layersArr]);
        }

        foreach ($layers as $layer) {
            $this->layers[] = clone $layer;
        }
    }

    /**
     * Sets the locale used for number formatting. Defaults to "en_US." See the intl extension documentation
     * @param non-empty-string $locale
     * @return void
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Sets the scale used for floating point math. Defaults to "14," which is the precision of floats in most builds of
     * PHP
     * @param positive-int $mathematicalScale
     */
    public function setMathematicalScale(int $mathematicalScale): void
    {
        $this->mathematicalScale = $mathematicalScale;
    }

    /**
     * Sets the label to use for the expected frequency table cell
     * @param non-empty-string $messageExpectedFrequency
     * @return void
     */
    public function setMessageExpectedFrequency(string $messageExpectedFrequency): void
    {
        $this->messageExpectedFrequency = $messageExpectedFrequency;
    }

    /**
     * Sets the label to use for the expected percentage table cell
     * @param non-empty-string $messageExpectedPercent
     * @return void
     */
    public function setMessageExpectedPercent(string $messageExpectedPercent): void
    {
        $this->messageExpectedPercent = $messageExpectedPercent;
    }

    /**
     * Sets the label to use for the frequency table cell
     * @param non-empty-string $messageFrequency
     * @return void
     */
    public function setMessageFrequency(string $messageFrequency): void
    {
        $this->messageFrequency = $messageFrequency;
    }

    /**
     * Sets the label to use for NULL values in the table
     * @param non-empty-string $messageNil
     */
    public function setMessageNil(string $messageNil): void
    {
        $this->messageNil = $messageNil;
    }

    /**
     * Sets the label to use for empty tables
     * @param non-empty-string $messageNoData
     */
    public function setMessageNoData(string $messageNoData): void
    {
        $this->messageNoData = $messageNoData;
    }

    /**
     * Sets the label to use for percentage cells
     * @param non-empty-string $messagePercent
     * @return void
     */
    public function setMessagePercent(string $messagePercent): void
    {
        $this->messagePercent = $messagePercent;
    }

    /**
     * Sets the label to use for total cells
     * @param non-empty-string $messageTotal
     */
    public function setMessageTotal(string $messageTotal): void
    {
        $this->messageTotal = $messageTotal;
    }

    /**
     * Sets the label to use for expected frequency cells
     * @param non-empty-string $messageWeightedExpectedFrequency
     */
    public function setMessageWeightedExpectedFrequency(string $messageWeightedExpectedFrequency): void
    {
        $this->messageWeightedExpectedFrequency = $messageWeightedExpectedFrequency;
    }

    /**
     * Sets the label to use for weighted expected percentage cells
     * @param non-empty-string $messageWeightedExpectedPercent
     */
    public function setMessageWeightedExpectedPercent(string $messageWeightedExpectedPercent): void
    {
        $this->messageWeightedExpectedPercent = $messageWeightedExpectedPercent;
    }

    /**
     * Sets the label to use for weighted frequency cells
     * @param non-empty-string $messageWeightedFrequency
     * @return void
     */
    public function setMessageWeightedFrequency(string $messageWeightedFrequency): void
    {
        $this->messageWeightedFrequency = $messageWeightedFrequency;
    }

    /**
     * Sets the label to use for weighted percentage cells
     * @param non-empty-string $messageWeightedPercent
     * @return void
     */
    public function setMessageWeightedPercent(string $messageWeightedPercent): void
    {
        $this->messageWeightedPercent = $messageWeightedPercent;
    }

    /**
     * Sets the percent type (row, column, total, etc.)
     * @param CrosstabPercentType $percentType
     */
    public function setPercentType(CrosstabPercentType $percentType): void
    {
        $this->percentType = $percentType;
    }

    /**
     * The raw data to tabulate. Should be an iterable of iterables (rows). Rows are cast to arrays. The "n" and
     * "weight" keys in each array are optionally used in computation, should you need to represent more than one case
     * per row or capture survey weighting
     * @param iterable<array-key, mixed> $rawData
     * @return void
     */
    public function setRawData(iterable $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * Sets the label of a row variable. If none is set, use the name
     * @param non-empty-string|null $rowVariableDescription
     */
    public function setRowVariableDescription(?string $rowVariableDescription): void
    {
        $this->rowVariableDescription = $rowVariableDescription;
    }

    /**
     * Sets the name of the column variable in the raw data
     * @param non-empty-string|null $rowVariableName
     */
    public function setRowVariableName(?string $rowVariableName): void
    {
        $this->rowVariableName = $rowVariableName;
    }

    /**
     * Explicitly defines the categories of the row variable in the raw data; otherwise, they are inferred. Useful
     * for relabeling/recoding categorical values
     * @param list<string|array{name: non-empty-string, description?: string}|CrosstabCategory> $rowVariableCategories
     */
    public function setRowVariableCategories(array $rowVariableCategories): void
    {
        $this->rowVariableCategories = $rowVariableCategories;
    }

    /**
     * Sets the scale of formatted decimal values in the table
     * @param int<0, max> $scaleDecimal
     * @return void
     */
    public function setScaleDecimal(int $scaleDecimal): void
    {
        $this->scaleDecimal = $scaleDecimal;
    }

    /**
     * Sets the scale of formatted percentage values in the table
     * @param int<0, max> $scalePercent
     * @return void
     */
    public function setScalePercent(int $scalePercent): void
    {
        $this->scalePercent = $scalePercent;
    }

    /**
     * Sets whether to display expected frequencies (e.g., the values we'd expect if no relationship existed between X
     * and Y) in the table
     * @param bool $showExpectedFrequency
     * @return void
     */
    public function setShowExpectedFrequency(bool $showExpectedFrequency): void
    {
        $this->showExpectedFrequency = $showExpectedFrequency;
    }

    /**
     * Sets whether to display expected percentages (e.g., the values we'd expect if no relationship existed between X
     * and Y) in the table
     * @param bool $showExpectedPercent
     * @return void
     */
    public function setShowExpectedPercent(bool $showExpectedPercent): void
    {
        $this->showExpectedPercent = $showExpectedPercent;
    }

    /**
     * Sets whether to display frequencies in the table
     * @param bool $showFrequency
     * @return void
     */
    public function setShowFrequency(bool $showFrequency): void
    {
        $this->showFrequency = $showFrequency;
    }

    /**
     * Sets whether to display percentages in the table
     * @param bool $showPercent
     * @return void
     */
    public function setShowPercent(bool $showPercent): void
    {
        $this->showPercent = $showPercent;
    }

    /**
     * Sets whether to display weighted expected frequencies (e.g., the values we'd expect if no relationship existed
     * between X and Y) in the table
     * @param bool $showWeightedExpectedFrequency
     */
    public function setShowWeightedExpectedFrequency(bool $showWeightedExpectedFrequency): void
    {
        $this->showWeightedExpectedFrequency = $showWeightedExpectedFrequency;
    }

    /**
     * Sets whether to display weighted expected percentages (e.g., the values we'd expect if no relationship existed
     * between X and Y) in the table
     * @param bool $showWeightedExpectedPercent
     */
    public function setShowWeightedExpectedPercent(bool $showWeightedExpectedPercent): void
    {
        $this->showWeightedExpectedPercent = $showWeightedExpectedPercent;
    }

    /**
     * Sets whether to display weighted frequencies in the table
     * @param bool $showWeightedFrequency
     * @return void
     */
    public function setShowWeightedFrequency(bool $showWeightedFrequency): void
    {
        $this->showWeightedFrequency = $showWeightedFrequency;
    }

    /**
     * Sets whether to display weighted frequencies in the table
     * @param bool $showWeightedPercent
     * @return void
     */
    public function setShowWeightedPercent(bool $showWeightedPercent): void
    {
        $this->showWeightedPercent = $showWeightedPercent;
    }

    /**
     * Sets an optional title to appear in the table header
     * @param non-empty-string|null $title
     * @return void
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
