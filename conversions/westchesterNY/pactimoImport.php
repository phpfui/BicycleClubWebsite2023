<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$validSizes = ['2XS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
function expandSizes(string $sizes) : array
  {
  global $validSizes;

  if (\str_contains($sizes, 'One Size'))
	{
	return [$sizes];
	}

  $parts = \explode(' ', $sizes);

  $base = '';

  if (\count($parts) > 1)
	{
	$base = \array_shift($parts) . ' ';
	}

  [$start, $end] = \explode('-', $parts[0]);

  $retVal = [];
  $found = false;

  foreach ($validSizes as $size)
	{
	if ($size == $start)
	  {
			$found = true;
	  }

	if ($found)
	  {
			$retVal[] = $size;
	  }

	if ($size == $end)
	  {
			return $retVal;
	  }
	}

  return $retVal;
  }

function getStoreOption(string $optionName, string $options) : \App\Record\StoreOption
	{
	$storeOption = new \App\Record\StoreOption();

	if (empty($options) || 'N/A' == \strtoupper($options))
		{
		return $storeOption;
		}

	$values = \explode(',', $options);

	foreach ($values as $index => $value)
		{
		$values[$index] = \trim($value);
		}

	$optionValues = \implode(',', $values);

	$storeOptionTable = new \App\Table\StoreOption();
	$condition = new \PHPFUI\ORM\Condition('optionName', $optionName);
	$condition->and(new \PHPFUI\ORM\Condition('optionValues', $optionValues));
	$storeOptionTable->setWhere($condition);
	$cursor = $storeOptionTable->getRecordCursor();

	if (\count($cursor))
		{
		return $cursor->current();
		}

	$storeOption->optionName = $optionName;
	$storeOption->optionValues = $optionValues;
	$storeOption->insert();

	return $storeOption;
	}

$reader = new \App\Tools\CSV\FileReader(PROJECT_ROOT . '/conversions/westchesterNY/Pactimo_WCC_new_DA_10.06.22.csv');

foreach ($reader as $row)
  {
	$storeItem = new \App\Record\StoreItem();
	$storeItem->active = 0;
	$storeItem->clothing = 1;
	$storeItem->cut = '';
	$storeItem->description = $row['Description'];
	$storeItem->noShipping = 1;
	$storeItem->payByPoints = 1;
	$storeItem->pickupZip = '10583';
	$storeItem->pointsOnly = 1;
	$storeItem->price = (float)\str_replace('$', '', $row['Price']);
	$storeItem->shipping = 0.0;
	$storeItem->taxable = 1;
  $storeItem->title = 'Pactimo ' . $row['Title'];
	$storeItem->type = 0;
	$storeItem->insert();

	$sizes = \expandSizes($row['Size']);

	if (\count($sizes))
		{
		$row['Size'] = \implode(',', $sizes);
		}

	$columns = ['Size', 'Sex', 'Fit', 'Color', 'Length'];
	$sequence = 0;

	foreach ($columns as $column)
		{
		$storeOption = \getStoreOption($column, $row[$column]);

		if ($storeOption->empty())
			{
			continue;
			}
		$storeItemOption = new \App\Record\StoreItemOption();
		$storeItemOption->sequence = ++$sequence;
		$storeItemOption->storeOptionId = $storeOption->storeOptionId;
		$storeItemOption->storeItemId = $storeItem->storeItemId;
		$storeItemOption->insert();
		}
	}
