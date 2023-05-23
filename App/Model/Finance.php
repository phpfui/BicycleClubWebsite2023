<?php

namespace App\Model;

class Finance extends \App\Model\File
	{
	private array $errors = [];

	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string, 2: string, 3: string}
	 */
	private array $requiredTaxFields = ['State', 'ZipCode', 'TaxRegionName', 'EstimatedCombinedRate'];

	public function __construct()
		{
		parent::__construct('../files/taxrates');
		}

  public function getErrors() : array
		{
		if ($this->getLastError())
			{
			$this->errors[] = $this->getLastError();
			}

		return $this->errors;
		}

	public function getTaxImportFields() : array
		{
	return $this->requiredTaxFields;
		}

	public function processFile(string | int $path) : string
		{
		$csvReader = new \App\Tools\CSVReader($path);
		$row = $csvReader->current();

		foreach ($this->requiredTaxFields as $required)
			{
			if (! isset($row[$required]))
				{
				$this->errors[] = "Field {$required} was not found and is required.";
				}
			}

		if ($this->errors)
			{
			\App\Tools\File::unlink($path);

			return $this->errors[0];
			}
		$ziptaxTable = new \App\Table\Ziptax();
		$ziptaxTable->setWhere(new \PHPFUI\ORM\Condition('zipstate', $row['State']));
		$ziptaxTable->delete();

		foreach ($csvReader as $row)
			{
			$fields = ['zip_code' => $row['ZipCode'],
				'zip_tax_rate' => (float)$row['EstimatedCombinedRate'] * 100.0,
				'zipcounty' => $row['TaxRegionName'],
				'zipstate' => $row['State'],
			];
			$ziptax = new \App\Record\Ziptax();
			$ziptax->setFrom($fields);
			$ziptax->insert();
			}

		return '';
		}
	}
