# CliffordVickrey\Crosstabs

Highly customizable abstraction for generating SPSS-like tabulations, cross-tabulations (also known as crosstabs or
contingency tables), and layered cross-tabulations in PHP. These are useful in showing the relationship between two
or more categorical variables.

[![Build Status](https://github.com/cliffordvickrey/crosstabs/actions/workflows/build.yml/badge.svg)](https://github.com/cliffordvickrey/crosstabs/actions)

## Requirements

* PHP 8.2 or higher

## Suggested Requirements

Ensure that the ``intl`` and ``bcmath`` extensions are installed for, respectively, international number formatting and
better mathematical precision.

## Installation

Run the following to install this library:

```bash
$ composer require cliffordvickrey/crosstabs
```

## Basic usage

Here, we generate a crosstab that shows the browsers of a website's visitors, as well as client operating system.

```php
<?php

// some data. If "n" is omitted, each row is treated as a single case
$rawData = [
    ['Device Type' => 'Desktop', 'Browser' => 'Chrome', 'Platform' => 'Linux', 'n' => '256'],
    ['Device Type' => 'Tablet', 'Browser' => 'Safari', 'Platform' => 'iOS', 'n' => '6'],
    ['Device Type' => 'Desktop', 'Browser' => 'Chrome', 'Platform' => 'MacOSX', 'n' => '227'],
    ['Device Type' => 'Desktop', 'Browser' => 'IE', 'Platform' => 'Windows', 'n' => '35'],
    ['Device Type' => 'Desktop', 'Browser' => 'Chrome', 'Platform' => 'Windows', 'n' => '221'], 
    ['Device Type' => 'Desktop', 'Browser' => 'Firefox', 'Platform' => 'MacOSX', 'n' => '38'], 
    ['Device Type' => 'Mobile Device', 'Browser' => 'Safari', 'Platform' => 'iOS', 'n' => '21'],
    ['Device Type' => 'Desktop', 'Browser' => 'Netscape', 'Platform' => 'Windows', 'n' => '21'],
    ['Device Type' => 'Desktop', 'Browser' => 'Safari', 'Platform' => 'MacOSX', 'n' => '38'],
    ['Device Type' => 'Mobile Device', 'Browser' => 'Edge', 'Platform' => 'iOS', 'n' => '72'],
    ['Device Type' => 'Desktop', 'Browser' => 'Safari', 'Platform' => 'Windows', 'n' => '15'],
    ['Device Type' => 'Desktop', 'Browser' => 'Firefox', 'Platform' => 'Linux', 'n' => '27'],
    ['Device Type' => 'Desktop', 'Browser' => 'Firefox', 'Platform' => 'Windows', 'n' => '12'],
    ['Device Type' => 'Desktop', 'Browser' => 'Edge', 'Platform' => 'Windows', 'n' => '11']
];

// the builder does exactly what it says. Set a bunch of options and call the "build" method
$builder = new \CliffordVickrey\Crosstabs\CrosstabBuilder();
$builder->setRawData($rawData);
$builder->setTitle('Browser Usage by Platform');
$builder->setColVariableName('Browser');
$builder->setRowVariableName('Platform');
$builder->setShowPercent(true);
$builder->setPercentType((\CliffordVickrey\Crosstabs\Options\CrosstabPercentType::Column);

$crosstab = $builder->build();

// display the crosstab as HTML (see example output below)
echo $crosstab->write();

// if you use a Bootstrap layout and want a table with all the fancy utility classes, etc., you can override the default
// writer like so:
echo $crosstab->write(writer: new \CliffordVickrey\Crosstabs\Writer\CrosstabBootstrapHtmlWriter());

// some inferential stats. Degrees of freedom are equal to the number of columns (minus 1) multiplied by the number of
// rows (minus 1). Chi-squared is a test statistic, comparing actual values with ones we'd expect if no relationship
// existed between the row and column variables
var_dump($crosstab->getDegreesOfFreedom()); // 15
var_dump($crosstab->getChiSquared()); // 598.35 (clearly significant!)

// now: let's add third dimension: device type
$builder->setTitle('Browser Usage by Platform by Device Type');
$builder->addLayer('Device Type');
// percentages will be of columns within each layer category; great for visualizing the effects of control variables
$builder->setPercentType(\CliffordVickrey\Crosstabs\Options\CrosstabPercentType::ColumnWithinLayer);
$crosstab = $builder->build();
echo $crosstab->write();

// want a simpler display? Let's just show a frequency distribution of browsers
$builder = new \CliffordVickrey\Crosstabs\CrosstabBuilder();
$builder->setRawData($rawData);
$builder->setTitle('Browser Usage');
$builder->setRowVariableName('Browser');
$builder->setShowPercent(true);
echo $crosstab->write();
```

![Example Output](/assets/example.png "Example Output")

## Class API

### The builder: `\CliffordVickrey\Crosstab\CrosstabBuilder`

The builder is used to configure and create the desired table. When configuring, you're always going to want to set
`rawData` and `rowVariableName`. In most cases, you'll also want to set `colVariableName` (if you're visualizing two or
more categorical variables).

#### `@build(): void`

Builds the table. Throws `\CliffordVickrey\Crosstab\Exception\CrosstabInvalidArgumentException` when the options are
invalid

#### `@addLayer(CrosstabVariable|array|string $layer, ?string $description = null, ?array $categories = []): void`

Adds a layer variable to the crosstab

#### `@addLayers(CrosstabVariableCollection|iterable $layers): void`

Adds multiple layer variables

#### `@setColVariableDescription(?string $colVariableDescription): void`

Sets the label of a column variable. If none is set, use the name

#### `@setColVariableName(?string $colVariableName): void`

Sets the name of the column variable in the raw data

#### `@setColVariableCategories(array $colVariableCategories): void`

Explicitly defines the categories of the column variable in the raw data; otherwise, they are inferred. Useful for
relabeling/recoding categorical values

#### `@setKeyFrequency(?string $keyFrequency): void`

Sets the key in the source data representing the number of cases in a row. If this information is absent, each row will
be treated as a single case. Defaults to "n"

#### `@setKeyWeightedFrequency(?string $keyWeightedFrequency): void`

Sets the key in the source data representing row weight. If this information is absent, each row will be weighed
equally. Defaults to "weight"

#### `@setLayers(CrosstabVariableCollection|iterable $layers = []): void`

Sets multiple layer variables

#### `@setLocale(string $locale): void`

Sets the locale used for number formatting. Defaults to "en_US." See the intl extension documentation

#### `@setMathematicalScale(int $mathematicalScale): void`

Sets the scale used for floating point math. Defaults to "16," roughly the precision of floats in most builds of PHP

#### `@setMessageExpectedFrequency(string $messageExpectedFrequency): void`

Sets the label to use for the expected frequency table cell. Defaults to "Frequency (Expected)"

#### `@setMessageExpectedPercent(string $messageExpectedPercent): void`

Sets the label to use for the expected percentage table cell. Defaults to "% (Expected)"

#### `@setMessageFrequency(string $messageFrequency): void`

Sets the label to use for the frequency table cell. Defaults to "Frequency"

#### `@setMessageNil(string $messageNil): void`

Sets the label to use for NULL values in the table. Defaults to "-"

#### `@setMessageNoData(string $messageNoData): void`

Sets the label to use for empty tables. Defaults to "There is no data to display"

#### `@setMessagePercent(string $messagePercent): void`

Sets the label to use for percentage cells. Defaults to "%"

#### `@setMessageTotal(string $messageTotal): void`

Sets the label to use for total cells. Defaults to "Total"

#### `@setMessageWeightedExpectedFrequency(string $messageWeightedExpectedFrequency): void`

Sets the label to use for weighted expected percentage cells. Defaults to "Expected Frequency (Weighted)"

#### `@setMessageWeightedExpectedPercent(string $messageWeightedExpectedPercent): void`

Sets the label to use for weighted expected percentage cells. Defaults to "Expected % (Weighted)"

#### `@setMessageWeightedFrequency(string $messageWeightedFrequency): void`

Sets the label to use for weighted frequency cells. Defaults to "Frequency (Weighted)"

#### `@setMessageWeightedPercent(string $messageWeightedPercent): void`

Sets the label to use for weighted percentage cells. Defaults to "% (Weighted)

#### `@setPercentType(CrosstabPercentType $percentType): void`

Sets the percent type (row, column, total, etc.). See the `\CliffordVickrey\Crosstabs\Options\CrosstabPercentType` enum
for a list of allowable options. Defaults to `CrosstabPercentType::Total`

#### `@setRawData(iterable $rawData): void`

The raw data to tabulate. Should be an iterable of iterables (rows). Rows are cast to arrays. The "n" and "weight" keys
in each array are optionally used in computation, should you need to represent more than one case per row or capture
survey weighting

#### `@setrowVariableDescription(?string $rowVariableDescription): void`

Sets the label of a row variable. If none is set, use the name

#### `@setrowVariableName(?string $rowVariableName): void`

Sets the name of the row variable in the raw data

#### `@setrowVariableCategories(array $rowVariableCategories): void`

Explicitly defines the categories of the row variable in the raw data; otherwise, they are inferred. Useful for
relabeling/recoding categorical values

#### `@setScaleDecimal(int $scaleDecimal): void`

Sets the scale of formatted decimal values in the table. Defaults to 2

#### `@setScalePercent(int $scalePercent): void`

Sets the scale of formatted percentage values in the table. Defaults to 2

#### `@setShowExpectedFrequency(bool $showExpectedFrequency): void`

Sets whether to display expected frequencies (e.g., the values we'd expect if no relationship existed between X and Y)
in the table. Defaults to `FALSE`

#### `@setShowExpectedPercent(bool $showExpectedPercent): void`

Sets whether to display expected percentages (e.g., the values we'd expect if no relationship existed between X and Y)
in the table. Defaults to `FALSE`

#### `@setShowFrequency(bool $showFrequency): void`

Sets whether to display frequencies in the table. Defaults to `TRUE`

#### `@setShowPercent(bool $showPercent): void`

Sets whether to display percentages in the table. Defaults to `FALSE`

#### `@setShowWeightedExpectedFrequency(bool $showWeightedExpectedFrequency): void`

Sets whether to display weighted expected frequencies (e.g., the values we'd expect if no relationship existed between X
and Y) in the table. Defaults to `FALSE`

#### `@setShowWeightedExpectedPercent(bool $showWeightedExpectedPercent): void`

Sets whether to display weighted expected percentages (e.g., the values we'd expect if no relationship existed between X
and Y) in the table. Defaults to `FALSE`

#### `@setShowWeightedFrequency(bool $showWeightedFrequency): void`

Sets whether to display weighted frequencies in the table. Defaults to `FALSE`

#### `@setShowWeightedPercent(bool $showWeightedPercent): void`

Sets whether to display weighted frequencies in the table. Defaults to `FALSE`

#### `@setTitle(?string $title): void`

Sets an optional title to appear in the table header. Defaults to `NULL` (i.e., display no title)

### The crosstab: `CliffordVickrey\Crosstabs\Crosstab`

Encapsulates the data and presentation elements of a crosstab. Implements `\Traversable`; traversal will return row
objects (`CliffordVickrey\Crosstabs\Crosstab\CrosstabRow`), which themselves provide cell objects
(`CliffordVickrey\Crosstabs\Crosstab\CrosstabRow`) when traversed. These cells contain the table's presentation data,
whereas the matrix (exposed by a getter) contains the tabulated data.

#### `@getCell(int $x, int $y): ?CrosstabCell`

Returns a cell at specified Cartesian coordinates. If none exists, returns NULL

#### `@getDegreesOfFreedom(): int`

Gets the number of independent values used to compute a chi-squared test statistic, etc. The more degrees there are,
the harder it is for a test statistic to achieve significance. Formula is `(rowCount - 1) * (colCount - 1)`

#### `@getChiSquared(bool $weighted = false, ?int $scale = null): float`

Gets the chi-squared test statistic. The higher the value, and the lower the number of cells used as factors to compute
the statistic, the more likely there is to be a relationship between the population parameters of the row and column
variables

#### `@getMatrix(): array`

Gets a rectangular matrix of value objects, representing data within the crosstab

#### `@write(array $options = [], ?CrosstabWriterInterface $writer = null): string`

Convenience method for writing a crosstab to a string. If no writer provided, the default HTML writer
(`CliffordVickrey\Crosstabs\Writer\CrosstabHtmlWriter`) will be used. See the class constants of the method for output
options. Returns the string output

#### `@writeToFile(array $options = [], ?CrosstabWriterInterface $writer = null): string`

Convenience method for writing a crosstab to a file. If no writer provided, the default HTML writer
(`CliffordVickrey\Crosstabs\Writer\CrosstabHtmlWriter`) will be used. See the class constants of the method for output
options. If no filename is provided, a temporary file will be created. Returns the filename written to
