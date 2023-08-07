<?php

namespace App\UI;

class GearCalculator
	{
	private \App\UI\CassettePicker $cassettePicker;

	private \App\Model\GearCalculator $model;

	private \App\UI\TirePicker $tirePicker;

	private \PHPFUI\Input\Select $units;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->model = new \App\Model\GearCalculator($_GET);

		$this->tirePicker = new \App\UI\TirePicker('t', 'Tire Size', $this->model->t ?? '678~28-622');
		$this->tirePicker->addAttribute('onChange', 'update()');

		$this->cassettePicker = new \App\UI\CassettePicker('c', 'Cassette', $this->model->getCassette());
		$this->cassettePicker->addAttribute('onChange', 'updateCassette()');

		$u = $this->model->u ?? '0';
		$this->units = new \PHPFUI\Input\Select('u', 'Units');
		$this->units->addAttribute('onChange', 'update()');
		$this->units->addOption('Gear inches', '0', '0' == $u);
		$this->units->addOption('Gear Ratio', '1', '1' == $u);
		$this->units->addOption('Meters Development', '2', '2' == $u);

		$rpms = [40, 60, 80, 90, 100, 110, 120];
		$units = ['M', 'K'];

		foreach ($units as $unit)
			{
			foreach ($rpms as $rpm)
				{
				$value = $rpm . '~' . $unit;
				$this->units->addOption("{$unit}PH @ {$rpm} RPM", $value, $value == $u);
				}
			}
		}

	public function show() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$id = $form->getId();
//		$form->add(new \PHPFUI\Debug($this->model));
		$js = <<<JAVASCRIPT
function update(){reloadPage($('#{$id}').serialize())};
function print(){window.location.assign('/File/gears'+'?'+$('#{$id}').serialize())};
function reloadPage(parms){window.location.assign(window.location.pathname+'?'+parms.slice(0,parms.indexOf('&csrf=')))};
function updateCassette(parms){var params=$('#{$id}').serialize();window.location.assign(window.location.pathname+'?uc=1&'+params)};
function addField(field){var params=$('#{$id}').serialize();reloadPage(field+'=33&'+params)};
function deleteField(field){var params=$('#{$id}').serialize();reloadPage(params.replace(target='&'+field+'='+$('input[name="'+field+'"]').val(),''))};
JAVASCRIPT;
		$this->page->addJavaScript($js);

		$form->setAreYouSure(false);

		$form->add($this->tirePicker);
		$form->add($this->cassettePicker);
		$form->add($this->units);

		$printButton = new \PHPFUI\Button('Print');
		$printButton->setAttribute('onClick', 'print()');

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($printButton);
		$form->add($buttonGroup);

		$table = new \PHPFUI\Table();

		$ringSizes = $this->model->getRings();

		$headers = ['&nbsp;'];

		foreach ($ringSizes as $ring => $teeth)
			{
			$headers[] = 'Ring ' . ($ring + 1);
			}
		$table->setHeaders($headers);
		$cogs = $this->model->getCogs();

		$row = ['&nbsp;' => '<b>Cogs</b>'];

		foreach ($ringSizes as $ring => $teeth)
			{
			$row['Ring ' . ($ring + 1)] = $this->getInput('ring' . (string)($ring + 1), $teeth, $ring == \count($ringSizes) - 1);
			}
		$table->addRow($row);

		foreach ($cogs as $cogIndex => $cog)
			{
			$row = [];
			$row['&nbsp;'] = $this->getInput('cog' . $cogIndex, $cog, $cogIndex == \count($cogs) - 1);

			foreach ($ringSizes as $ring => $teeth)
				{
				$row['Ring ' . ($ring + 1)] = $this->model->computeGear($teeth, $cog);
				}
			$table->addRow($row);
			}

		$form->add($table);

		return $form;
		}

	private function getInput(string $field, int $value, bool $last) : \PHPFUI\GridX
		{
		$gridX = new \PHPFUI\GridX();
		$cellA = new \PHPFUI\Cell(11);
		$input = new \PHPFUI\Input\Text($field, '', (string)$value);
		$input->addAttribute('onChange', 'update()');
		$cellA->add($input);

		$cellB = new \PHPFUI\Cell();
		$minusIcon = new \PHPFUI\IconBase('fa-solid fa-square-minus');
		$minusIcon->addClass('alert');
		$minusIcon->setAttribute('onClick', 'deleteField("' . $field . '")');
		$cellB->add($minusIcon);

		if ($last)
			{
			$plusIcon = new \PHPFUI\IconBase('fa-solid fa-square-plus');
			$plusIcon->addClass('success');
			$name = \preg_replace('/[^a-zA-Z]+/', '', $field);
			$number = (int)\preg_replace('/[^0-9]+/', '', $field) + 1;
			$plusIcon->setAttribute('onClick', 'addField("' . $name . $number . '")');
			$cellB->add($plusIcon);
			}
		$cellB->setAuto();
		$gridX->add($cellA);
		$gridX->add($cellB);

		return $gridX;
		}
	}
