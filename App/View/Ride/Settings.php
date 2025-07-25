<?php

namespace App\View\Ride;

class Settings
	{
	/** @var array<string,string> */
	private array $optionalFields = [
		'regroupingOption' => 'Regrouping Policy',
		'restStopOption' => 'Rest Stop',
		'maxRidersOption' => 'Rider Limit',
		'targetPaceOption' => 'Target Pace',
	];

	/** @var array<string> */
	private array $optionalTypes = ['Hidden', 'Visible', 'Required'];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();
		}

	/** @return array<string> */
	public function getFieldNames() : array
		{
		return \array_keys($this->optionalFields);
		}

	public function getOptionalFields(\App\Record\Ride $ride) : \PHPFUI\MultiColumn
		{
		return $this->getFields('Visible', $ride);
		}

	public function getOptionalFieldsConfiguration() : \PHPFUI\MultiColumn
		{
		$optionalColumn = new \PHPFUI\MultiColumn();

		foreach ($this->optionalFields as $field => $label)
			{
			$select = new \PHPFUI\Input\Select($field, $label);
			$value = $this->settingTable->value($field);

			foreach ($this->optionalTypes as $type)
				{
				$select->addOption($type, $type, $type == $value);
				}
			$optionalColumn->add($select);
			}

		return $optionalColumn;
		}

	public function getRequiredFields(\App\Record\Ride $ride) : \PHPFUI\MultiColumn
		{
		return $this->getFields('Required', $ride);
		}

	private function getField(string $field, \App\Record\Ride $ride) : ?\PHPFUI\Input
		{
		switch ($field)
			{
			case 'maxRidersOption':
				if (! (int)$this->settingTable->value('RideSignupLimit'))
					{
					$maxRiders = new \PHPFUI\Input\Number('maxRiders', 'Rider Limit', $ride->maxRiders ?: (int)$this->settingTable->value('RideSignupLimitDefault'));
					$maxRiders->addAttribute('max', (string)99)->addAttribute('min', (string)0);
					$maxRiders->setToolTip('You can limit the number of riders, zero is unlimited riders');

					return $maxRiders;
					}

				return null;

			case 'targetPaceOption':
				$targetPace = new \PHPFUI\Input\Number('targetPace', 'Expected Target Pace', ! $ride->targetPace ? '' : $ride->targetPace);
				$targetPace->addAttribute('min', '0')->addAttribute('max', '25')->addAttribute('step', (string)0.1);
				$targetPace->setToolTip('This should be a more specific number within the ride category pace range');

				return $targetPace;

			case 'restStopOption':

				$restStop = new \PHPFUI\Input\Text('restStop', 'Rest Stop', $ride->restStop ?? '');
				$restStop->addAttribute('maxlength', '70');
				$restStop->setToolTip('The planned rest stop');

				return $restStop;

			case 'regroupingOption':

				$select = new \App\UI\RegroupPolicy($this->page, $ride->regrouping);

				return $select->getControl();
			}

		return null;
		}

	private function getFields(string $type, \App\Record\Ride $ride) : \PHPFUI\MultiColumn
		{
		$requiredColumns = new \PHPFUI\MultiColumn();

		foreach ($this->optionalFields as $field => $label)
			{
			$value = $this->settingTable->value($field);

			if ($type == $value)
				{
				$input = $this->getField($field, $ride);

				if ($input)
					{
					if ('Required' == $type)
						{
						$input->setRequired();
						}
					$requiredColumns->add($input);
					}
				}
			}

		if (1 == \count($requiredColumns))
			{
			$requiredColumns->add('&nbsp;');
			}

		return $requiredColumns;
		}
	}
