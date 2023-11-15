<?php

namespace App\Report;

class MemberWaiver extends \Mpdf\Mpdf
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$config = [];
		$config['format'] = 'Letter';
		$config['mode'] = 'utf-8';

		parent::__construct($config);
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @param array<string,string> $member
	 */
	public function generate(array $member, string $header = '', string $text = '') : void
		{
		$this->AddPage();

		if (empty($header))
			{
			$header = $this->settingTable->value('WaiverHeader');
			}
		$this->writeHTML($header);

		if (empty($text))
			{
			$text = $this->settingTable->value('WaiverText');
			}
		$this->writeHTML($text);

		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader('The above was signed as follows:'));
		$table = new \PHPFUI\Table();
		$table->addRow($this->format('Name:', $member['firstName'] . ' ' . $member['lastName']));

		if (isset($member['guardian']))
			{
			$table->addRow($this->format('Responsible Adult:', $member['guardian']));
			}

		if (isset($member['phone']))
			{
			$table->addRow($this->format('Phone:', $member['phone']));
			}

		if (isset($member['cellPhone']))
			{
			$table->addRow($this->format('Cell Phone:', $member['cellPhone']));
			}
		$table->addRow($this->format('Emergency Contact:', $member['emergencyContact'] ?? 'none'));
		$table->addRow($this->format('Emergency Contact Phone:', $member['emergencyPhone'] ?? 'none'));
		$table->addRow($this->format('email:', $member['email'] ?? ''));
		$table->addRow($this->format('Signed At:', \date('l jS \of F Y h:i:s A', \strtotime((string)$member['acceptedWaiver']))));

		if (isset($member['lastLogin']))
			{
			$table->addRow($this->format('Signed In At:', \date('l jS \of F Y h:i:s A', \strtotime((string)$member['lastLogin']))));
			}
		$table->addRow($this->format('Printed At:', \date('l jS \of F Y h:i:s A')));
		$container->add($table);

		$this->writeHTML($container);
		}

	public function generateMinorRelease() : void
		{
		$this->AddPage();
		$this->SetMargins(15, 15, 15);
		$settingTable = new \App\Table\Setting();
		$this->WriteHtml($settingTable->value('MinorWaiverText'));
		}

	/**
	 * @return array<string>
	 */
	private function format(string $label, string $value) : array
		{
		return ["<strong>{$label}</strong>", $value];
		}
	}
