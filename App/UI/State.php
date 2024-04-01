<?php

namespace App\UI;

class State extends \PHPFUI\Input\SelectAutoComplete
	{
	/**
	 * @var array<string,string>
	 */
	private static array $states = [
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	];

	public function __construct(\PHPFUI\Page $page, string $name, string $title, string $value = '')
		{
		parent::__construct($page, $name, $title);
		$this->addOption('Please select a state', '', empty($value));
		$this->setArray('states');

		foreach (self::$states as $abbrev => $state)
			{
			$this->addOption($abbrev . ' ' . $state, $abbrev, $value == $abbrev);
			}
		}

	public static function getAbbrevation(string $state) : string
		{
		if (\strlen($state) <= 2)
			{
			return \strtoupper($state);
			}

		$state = \ucwords($state);

		foreach (self::$states as $abbrev => $name)
			{
			if ($name == $state)
				{
				return $abbrev;
				}
			}

		return $state;
		}
	}
