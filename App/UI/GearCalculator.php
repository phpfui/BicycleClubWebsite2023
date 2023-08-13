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
		$this->units->addOption('Gear Inches', '0', '0' == $u);
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
		$js = <<<JAVASCRIPT
function update(){reloadPage($('#{$id}').serialize())};
function print(){window.location.assign('/File/gearPrint'+'?'+$('#{$id}').serialize())};
function csv(){window.location.assign('/File/gearCSV'+'?'+$('#{$id}').serialize())};
function reloadPage(parms){window.location.assign(window.location.pathname+'?'+parms.slice(0,parms.indexOf('&csrf='))+window.location.hash)};
function updateCassette(){var params=$('#{$id}').serialize();window.location.assign(window.location.pathname+'?uc=1&'+params+window.location.hash)};
function updateHub(hub){var params=$('#{$id}').serialize();window.location.assign(window.location.pathname+'?u'+hub+'=1&'+params+window.location.hash)};
function addField(field,number){var params=$('#{$id}').serialize();reloadPage(field+'='+number+'&'+params)};
function deleteField(field){var params=$('#{$id}').serialize();reloadPage(params.replace(target='&'+field+'='+$('input[name="'+field+'"]').val(),''))};
JAVASCRIPT;
		$this->page->addJavaScript($js);
		$form->setAreYouSure(false);

		$title = new \PHPFUI\Input\Text('tl', 'Title', $this->model->tl ?? '');
		$title->addAttribute('onChange', 'update()');
		$form->add($title);
		$form->add($this->tirePicker);
		$form->add($this->units);

		$printButton = new \PHPFUI\Button('Print');
		$printButton->setAttribute('onClick', 'print()');
		$csvButton = new \PHPFUI\Button('CSV');
		$csvButton->addClass('warning');
		$csvButton->setAttribute('onClick', 'csv()');
		$shareButton = new \PHPFUI\Button('<i class="fa-solid fa-share-nodes"></i> Share');
		$shareButton->addClass('success');
		$copiedButton = new \PHPFUI\Button('URL Copied!');
		$copiedButton->addClass('success hide hollow');
		$this->page->addCopyToClipboard($this->model->getURL(), $shareButton, $copiedButton);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($printButton);
		$buttonGroup->addButton($csvButton);
		$buttonGroup->addButton($shareButton);
		$buttonGroup->addButton($copiedButton);
		$form->add($buttonGroup);

		$ringSizes = $this->model->getRings();

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addClass('tiny');

		if (! $this->model->getFrontHub())
			{
			$frontHub = new \PHPFUI\Button('Front Hub');
			$frontHub->addClass('warning');
			$frontHub->setAttribute('onClick', 'addField("fh0",1.0)');
			$buttonGroup->addButton($frontHub);
			}

		if (! $this->model->getRearHub())
			{
			$rearHub = new \PHPFUI\Button('Rear Hub');
			$rearHub->addClass('warning');
			$rearHub->setAttribute('onClick', 'addField("rh0",1.0)');
			$buttonGroup->addButton($rearHub);
			}

		$headers = ['&nbsp;' => $buttonGroup];
		//$headers = ['&nbsp;'];

		foreach ($ringSizes as $ring => $teeth)
			{
			$headers[] = 'Ring ' . ($ring + 1);
			}

		$table = new \PHPFUI\Table();
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

		if ($this->model->getFrontHub() || $this->model->getRearHub())
			{
			$tabs = new \PHPFUI\Tabs();
			$tabs->addTab('Gears', $this->cassettePicker . $table, true);

			if ($this->model->getFrontHub())
				{
				$tabs->addTab('Front Hub', $this->getHub($this->model->getFrontHub(), 'fh'));
				}

			if ($this->model->getRearHub())
				{
				$tabs->addTab('Rear Hub', $this->getHub($this->model->getRearHub(), 'rh'));
				}
			$form->add('<p>' . $tabs->getTabs()->addAttribute('data-deep-link', 'true'));
			$form->add($tabs->getContent() . '</p>');
			}
		else
			{
			$form->add($this->cassettePicker);
			$form->add($table);
			}

		return $form;
		}

	/**
	 * @param array<float> $ratios
	 */
	private function getHub(array $ratios, string $type) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$hubPicker = new \App\UI\HubPicker($type, ('rh' == $type ? 'Rear' : 'Front') . ' Hub', $this->getHubString($ratios));
		$hubPicker->addAttribute('onChange', 'updateHub("' . $type . '")');
		$container->add($hubPicker);

		$multiColumn = new \PHPFUI\MultiColumn();

		$ratioCount = \count($ratios);
		$count = 0;

		foreach ($ratios as $index => $ratio)
			{
			if (++$count > 6)
				{
				$container->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				$count = 1;
				}
			$multiColumn->add($this->getInput($type . $index, \number_format($ratio, 3), $index == $ratioCount - 1));
			}
		$container->add($multiColumn);

		$ringSizes = $this->model->getRings();

		$container->add($this->model->getTable());

		return $container;
		}

	/**
	 * @param array<float> $ratios
	 */
	private function getHubString(array $ratios) : string
		{
		foreach ($ratios as &$ratio)
			{
			$ratio = \number_format($ratio, 3);
			}

		return \implode('-', $ratios);
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
	}
