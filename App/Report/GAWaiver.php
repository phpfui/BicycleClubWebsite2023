<?php

namespace App\Report;

class GAWaiver extends \Mpdf\Mpdf
	{
	public function __construct()
		{
		$config = [];
		$config['format'] = 'Letter';
		$config['mode'] = 'utf-8';

		parent::__construct($config);
		}

	public function generate(\App\Record\GaRider $rider) : bool
		{
		$this->AddPage();

		$event = $rider->gaEvent;

		$text = $event->waiver;

		if (empty($text))
			{
			return false;
			}
		$this->writeHTML($text);

		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader('The above was signed as follows:'));
		$table = new \PHPFUI\Table();
		$table->addRow($this->format('Name:', $rider->fullName()));

		if (isset($rider->phone))
			{
			$table->addRow($this->format('Phone:', $rider->phone));
			}

		$table->addRow($this->format('Emergency Contact:', $rider->contact ?? 'none'));
		$table->addRow($this->format('Emergency Contact Phone:', $rider->contactPhone ?? 'none'));
		$table->addRow($this->format('email:', $rider->email ?? ''));
		$table->addRow($this->format('Signed At:', \date('l jS \of F Y h:i:s A', \strtotime((string)$rider->signedUpOn))));

		$table->addRow($this->format('Printed At:', \date('l jS \of F Y h:i:s A')));
		$container->add($table);

		$this->writeHTML($container);

		return true;
		}

	/**
	 * @return array<string>
	 */
	private function format(string $label, string $value) : array
		{
		return ["<strong>{$label}</strong>", $value];
		}
	}
