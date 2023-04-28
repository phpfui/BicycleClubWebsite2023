<?php

namespace App\View\Ride;

class Settings
	{
	private array $optionalFields = [
		'regroupingOption' => 'Regrouping Policy',
		'targetPaceOption' => 'Target Pace',
		'maxRidersOption' => 'Rider Limit'];

	private array $optionalTypes = ['Hidden', 'Visible', 'Required'];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->settingTable = new \App\Table\Setting();
		}

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
					$maxRiders = new \PHPFUI\Input\Number('maxRiders', 'Rider Limit', $ride->maxRiders);
					$maxRiders->addAttribute('max', (string)99)->addAttribute('min', (string)0);
					$maxRiders->setToolTip('You can limit the number of riders, zero is unlimited riders');

					return $maxRiders;
					}

				return null;

			case 'targetPaceOption':
				$targetPace = new \PHPFUI\Input\Number('targetPace', 'Expected Target Pace', ! $ride->targetPace ? '' : $ride->targetPace);
				$targetPace->addAttribute('min', (string)5)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1);
				$targetPace->setRequired();
				$targetPace->setToolTip('This should be a more specific number within the ride category pace range');

				return $targetPace;

			case 'regroupingOption':
				$rideTable = new \App\Table\Ride();
				$rideTable->addSelect('regrouping')->setDistinct()->setLimit(50)->addOrderBy('rideId', 'desc');
				$choices = $rideTable->getArrayCursor();
				$regrouping = new \PHPFUI\Input\SelectAutoComplete($this->page, 'regrouping', 'Regrouping Policy', true);
				$regrouping->setToolTip('Select an existing policy or enter your own.');
				$regrouping->addOption($ride->regrouping ?? '', $ride->regrouping ?? '', true);
				$regrouping->setRequired();

				foreach ($choices as $option)
					{
					$value = $option['regrouping'];
					$regrouping->addOption($value, $value, $value == $ride->regrouping);
					}

				return $regrouping;
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

		return $requiredColumns;
		}
	}
