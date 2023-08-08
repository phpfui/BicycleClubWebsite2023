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
		$this->page->setPageName($this->model->getPageName());

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
function addField(field,number){var params=$('#{$id}').serialize();reloadPage(field+'='+number+'&'+params)};
function deleteField(field){var params=$('#{$id}').serialize();reloadPage(params.replace(target='&'+field+'='+$('input[name="'+field+'"]').val(),''))};
JAVASCRIPT;
		$this->page->addJavaScript($js);

		$form->setAreYouSure(false);

		$form->add($this->tirePicker);
		$form->add($this->cassettePicker);
		$form->add($this->units);

		$printButton = new \PHPFUI\Button('Print');
		$printButton->setAttribute('onClick', 'print()');
		$shareButton = new \PHPFUI\Button('<i class="fa-solid fa-share-nodes"></i> Share');
		$shareButton->addClass('success');
		$copiedButton = new \PHPFUI\Button('URL Copied!');
		$copiedButton->addClass('success hide hollow');
		$this->page->addCopyToClipboard($this->model->getURL(), $shareButton, $copiedButton);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($printButton);
		$buttonGroup->addButton($shareButton);
		$buttonGroup->addButton($copiedButton);
		$form->add($buttonGroup);

		$table = new \PHPFUI\Table();

		$ringSizes = $this->model->getRings();

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addClass('tiny');

		if (! $this->model->getFrontInternal())
			{
			$frontInternal = new \PHPFUI\Button('Front Hub');
			$frontInternal->addClass('warning');
			$frontInternal->setAttribute('onClick', 'addField("fh0",1.0)');
			$buttonGroup->addButton($frontInternal);
			}

		if (! $this->model->getRearInternal())
			{
			$rearInternal = new \PHPFUI\Button('Rear Hub');
			$rearInternal->addClass('warning');
			$rearInternal->setAttribute('onClick', 'addField("rh0",1.0)');
			$buttonGroup->addButton($rearInternal);
			}

		//$headers = ['&nbsp;' => $buttonGroup];
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
			$row['Ring ' . ($ring + 1)] = $this->getInput('ring' . (string)($ring + 1), (string)$teeth, $ring == \count($ringSizes) - 1);
			}
		$table->addRow($row);

		foreach ($cogs as $cogIndex => $cog)
			{
			$row = [];
			$row['&nbsp;'] = $this->getInput('cog' . $cogIndex, (string)$cog, $cogIndex == \count($cogs) - 1);

			foreach ($ringSizes as $ring => $teeth)
				{
				$row['Ring ' . ($ring + 1)] = $this->model->computeGear($teeth, $cog);
				}
			$table->addRow($row);
			}

		if ($this->model->getFrontInternal() || $this->model->getRearInternal())
			{
			$tabs = new \PHPFUI\Tabs();
			$tabs->addTab('Gears', $table, true);

			if ($this->model->getFrontInternal())
				{
				$tabs->addTab('Front Internal', $this->getInternal($this->model->getFrontInternal(), 'fh'));
				}

			if ($this->model->getRearInternal())
				{
				$tabs->addTab('Rear Internal', $this->getInternal($this->model->getRearInternal(), 'rh'));
				}
			$form->add($tabs);
			}
		else
			{
			$form->add($table);
			}

		return $form;
		}

	private function getInput(string $field, string $value, bool $last) : \PHPFUI\GridX
		{
		$gridX = new \PHPFUI\GridX();
		$cellA = new \PHPFUI\Cell(11);
		$input = new \PHPFUI\Input\Text($field, '', $value);
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
			$plusIcon->setAttribute('onClick', 'addField("' . $name . $number . '",' . $value . ')');
			$cellB->add($plusIcon);
			}
		$cellB->setAuto();
		$gridX->add($cellA);
		$gridX->add($cellB);

		return $gridX;
		}

	/**
	 * @param array<float> $ratios
	 */
	private function getInternal(array $ratios, string $type) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add('Enter gear ratios as decimal fractions');

		$multiColumn = new \PHPFUI\MultiColumn();

		foreach ($ratios as $index => $ratio)
			{
			$multiColumn->add($this->getInput($type . $index, \number_format($ratio, 3), $index == \count($ratios) - 1));
			}
		$container->add($multiColumn);

		$table = new \PHPFUI\Table();
		$container->add($table);

		return $container;
		}
	}
