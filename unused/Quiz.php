<?php

namespace App\View;

class Quiz
	{
	public function __construct(private \App\View\Page $page)
		{
		}

	public function getQuiz(int $page) : \PHPFUI\Container
		{
		return new \PHPFUI\Container();
		}

	public function bikeQuestions(array $data) : \PHPFUI\HTML5Element
		{
		$fieldSet = new \PHPFUI\FieldSet('Your Bike(s)');
		$fieldSet->add('Please answer these questions about your bike(s). Use your best bike for all questions.');
		$fieldSet->add($this->getNumber('bikeCount', 'Number of operational bikes you own', $data['bikeCount']));
		$fieldSet->add($this->getRadioGroup('frame', 'Frame material', ['Carbon', 'Titanium', 'Aluminum', 'Steel', 'Wood/Bamboo', ], $data));
		$fieldSet->add($this->getRadioGroup('handlebars', 'Type of handlebars', ['Drop', 'Flat', ], $data));
		$fieldSet->add($this->getRadioGroup('wheels', 'Wheel material', ['Carbon', 'Alloy', ], $data));
		$fieldSet->add($this->getRadioGroup('tubeType', 'Tire Tube Type', ['Tubeless', 'Tubes', 'Tubular', ], $data));
		$fieldSet->add($this->getRadioGroup('valveType', 'Valve Type', ['Presta', 'Schrader', ], $data));
		$fieldSet->add($this->getRadioGroup('shifting', 'Shifting', ['Electronic', 'Mechanical', ], $data));
		$fieldSet->add($this->getRadioGroup('brakes', 'Brake Type', ['Disc', 'Rim', ], $data));
		$fieldSet->add($this->getRadioGroup('pedals', 'Pedal Type', ['Clipless', 'Toe Clips', 'Platform', ], $data));
		$fieldSet->add($this->getRange('gears', 'Number of Cogs on Cassette', 13, 5, $data));
		$fieldSet->add($this->getRange('year', 'Year Bike Manufactured', \date('Y'), 1970, $data));

		return $fieldSet;
		}

	public function experienceQuestions(array $data) : \PHPFUI\HTML5Element
		{
		$fieldSet = new \PHPFUI\FieldSet('Your Other Experiences');
		$fieldSet->add('Please answer these questions about your other atheletic experiences.  Check all that apply.');

		$fieldSet->add($this->checkBox('stationary', 'Regular Stationary (Spinning) Bike Rider', $data));
		$fieldSet->add($this->checkBox('hiker', 'Regular Hiker', $data));
		$fieldSet->add($this->checkBox('swimmer', 'Regular Swimmer', $data));
		$fieldSet->add($this->checkBox('runner', 'Regular Runner', $data));
		$fieldSet->add($this->checkBox('10KRunner', 'Completed a 10K running race', $data));
		$fieldSet->add($this->checkBox('20KRunner', 'Completed a 20K running race', $data));
		$fieldSet->add($this->checkBox('sprintTri', 'Completed Sprint Triathlon', $data));
		$fieldSet->add($this->checkBox('olympicTri', 'Completed Olympic Triathlon', $data));
		$fieldSet->add($this->checkBox('halfIronTri', 'Completed Half Iron Man Triathlon or more', $data));
		$fieldSet->add($this->checkBox('skateSkier', 'Skate Skier', $data));
		$fieldSet->add($this->checkBox('speedSkater', 'Speed Skater', $data));

		return $fieldSet;
		}

	public function cyclingQuestions(array $data) : \PHPFUI\HTML5Element
		{
		$fieldSet = new \PHPFUI\FieldSet('Your Cycling Experience');
		$fieldSet->add('Please answer these questions about your cycling experiences.  Check all that apply.');

		$yearsRiding = $this->getNumber('yearsRiding', 'Number of years riding as an adult', $data, 60);
		$longestRide = $this->getNumber('longestRide', 'Longest Ride One Day Ride (in miles)', $data, 200);
		$monthlyRides = $this->getNumber('monthlyRides', 'Rides you do per month (0 for less than 12 a year)', $data, 30);
		$fieldSet->add(new \PHPFUI\MultiColumn($yearsRiding, $longestRide, $monthlyRides));
		$fieldSet->add($this->checkBox('otherClub', 'Ridden with another cycling club', $data));
		$fieldSet->add($this->checkBox('training', 'Ridden on a regular training ride', $data));
		$fieldSet->add($this->checkBox('paceLine', 'Ride pace lines regularly', $data));
		$fieldSet->add($this->checkBox('peloton', 'Ridden in a cycling peloton (not the stationary bike)', $data));
		$fieldSet->add($this->checkBox('cat4Racer', 'Cat 4 Bicycle Racer or better', $data));

		return $fieldSet;
		}

	public function personalQuestions(array $data) : \PHPFUI\HTML5Element
		{
		$fieldSet = new \PHPFUI\FieldSet('Your Personal Information');
		$fieldSet->add('We only this information for computing a ride category. Leave unanswered if you want.');

		$fieldSet->add($this->getRadioGroup('sex', 'Sex', ['', 'Male', 'Female'], $data));
		$fieldSet->add($this->getRadioGroup('ageGroup', 'Age Group', ['', '34 or Under', '35 - 44', '45 - 54', '55 - 64', '65 - 74', '75 or Over', ], $data));
		$fieldSet->add($this->getRadioGroup('bmi', 'BMI Range', ['', '17 or Under', '18 - 20', '21 - 23', '24 - 26', '27 - 29', '30 - 32', '33 - 35', '36 or Over', ], $data));

		return $fieldSet;
		}

	private function getNumber(string $field, string $label, array $data, int $max = 0) : \PHPFUI\Input\Number
		{
		$count = new \PHPFUI\Input\Number($field, $label, $data[$field]);
		$count->addAttribute('minval', 0);
		$count->addAttribute('step', 1);

		if ($max)
			{
			$count->addAttribute('maxval', $max);
			}

		return $count;
		}

	private function getRange(string $field, string $label, int $max, int $min, array $data) : \PHPFUI\Input\Select
		{
		$select = new \PHPFUI\Input\Select($field, $label);
		$select->addOption('No Idea', 0);

		while ($max >= $min)
			{
			$select->addOption($max--);
			}

		return $select;
		}

	private function getRadioGroup(string $field, string $label, array $options, array $data) : \PHPFUI\Input\RadioGroup
		{
		$radioGroup = new \PHPFUI\Input\RadioGroup($field, $label, $data[$field]);
		$unknown = "Don't Know";

		foreach ($options as $option)
			{
			if (empty($option))
				{
				$unknown = '';
				}
			$radioGroup->addButton($option);
			}

		if ($unknown)
			{
			$radioGroup->addButton($unknown);
			}

		return $radioGroup;
		}

	private function checkBox(string $field, string $label, array $data) : \PHPFUI\Input\CheckBox
		{
		return new \PHPFUI\Input\CheckBox($field, $label, $data[$field]);
		}
	}
