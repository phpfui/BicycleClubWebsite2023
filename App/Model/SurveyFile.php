<?php

namespace App\Model;

class SurveyFile extends \App\Model\File
	{
	public function __construct()
		{
		parent::__construct('../files/surveys');
		}

	public function get(string | int $filename) : string
		{
		return parent::get($filename) . '.csv';
		}

	public function getCrossTabStats(\App\Record\SurveyCrossTab $crossTab) : string
		{
		$data = $this->getDataColumns($crossTab->rowSurveyQuestion, $crossTab->columnSurveyQuestion);

		if (! $crossTab->columnSurveyQuestion->loaded())
			{
			$total = 0;

			$surveyQuestion = $crossTab->rowSurveyQuestion;
			$responses = [];

			foreach ($data as $row)
				{
				$value = $row[$surveyQuestion->columnName];
				++$total;

				if ('' === $value)
					{
					$value = 'No Answer';
					}

				if (isset($responses[$value]))
					{
					++$responses[$value];
					}
				else
					{
					$responses[$value] = 1;
					}
				}

			\arsort($responses);

			$table = new \PHPFUI\Table();
			$headers = ['Response', 'Count', ];

			if ($crossTab->percent)
				{
				$headers[] = 'Percent';
				}
			$table->setHeaders($headers);
			$table->addColumnAttribute('Count', ['style' => 'width:10%']);
			$table->addColumnAttribute('Percent', ['style' => 'width:10%']);

			$percentTotal = 0.0;

			foreach ($responses as $name => $count)
				{
				$percent = $count / $total * 100;
				$percentTotal += $percent;
				$table->addRow(['Response' => $name, 'Count' => $count, 'Percent' => \number_format($percent, 1) . '%']);
				}
			$table->addRow(['Response' => '<b>Total</b>', 'Count' => $total, 'Percent' => \number_format($percentTotal, 1) . '%']);

			$fieldSet = new \PHPFUI\FieldSet("Totals For <b>{$surveyQuestion->displayName}</b>");
			$fieldSet->add($table);

			return "{$fieldSet}";
			}

		$builder = new \CliffordVickrey\Crosstabs\CrosstabBuilder();
		$builder->setRawData($data);
		$builder->setTitle($crossTab->name);

		$builder->setColVariableName($crossTab->columnSurveyQuestion->columnName);
		$builder->setColVariableDescription($crossTab->columnName);
		$builder->setRowVariableName($crossTab->rowSurveyQuestion->columnName);
		$builder->setRowVariableDescription($crossTab->rowName);

		if ($crossTab->percent)
			{
			$builder->setShowPercent(true);
			$builder->setPercentType(\CliffordVickrey\Crosstabs\Options\CrosstabPercentType::Row);
			}

		return $builder->build()->write();
		}

	/**
	 * @return array<array<string,string>>
	 */
	public function getDataColumns(\App\Record\SurveyQuestion $rowQuestion, \App\Record\SurveyQuestion $columnQuestion) : array
		{
		$surveyFileModel = new \App\Model\SurveyFile();
		$csvReader = new \App\Tools\CSV\FileReader($surveyFileModel->get($rowQuestion->surveyId));

		$data = [];

		foreach ($csvReader as $line)
			{
			$rowRaw = $line[$rowQuestion->columnName];
			$columnRaw = $columnQuestion->loaded() ? $line[$columnQuestion->columnName] : null;

			if ($rowQuestion->separator)
				{
				$row = \explode($rowQuestion->separator, $rowRaw);
				}
			else
				{
				$row = [$rowRaw];
				}

			if (null !== $columnRaw)
				{
				$column = [$columnRaw];

				if ($columnQuestion->separator)
					{
					$column = \explode($columnQuestion->separator, $columnRaw);
					}
				}

			if (null === $columnRaw)
				{
				foreach ($row as $value)
					{
					$data[] = [$rowQuestion->columnName => $value];
					}
				}
			else
				{
				foreach ($column as $columnValue)
					{
					foreach ($row as $rowValue)
						{
						$data[] = [$rowQuestion->columnName => $rowValue, $columnQuestion->columnName => $columnValue];
						}
					}
				}
			}

		return $data;
		}
	}
