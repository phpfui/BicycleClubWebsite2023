<?php

namespace App\View\Setup;

class DBInit extends \PHPFUI\Container
	{
	private array $officialTables = [
		'additionalemail' => false,
		'assistantleader' => false,
		'audittrail' => false,
		'banner' => false,
		'bikeshoparea' => false,
		'blog' => false,
		'blogitem' => false,
		'boardmember' => false,
		'calendar' => false,
		'cartitem' => false,
		'category' => false,
		'cuesheet' => false,
		'cuesheetversion' => false,
		'customer' => false,
		'discountcode' => false,
		'event' => false,
		'forum' => false,
		'forumattachment' => false,
		'forummember' => false,
		'forummessage' => false,
		'gaanswer' => false,
		'gaevent' => false,
		'gaincentive' => false,
		'gapricedate' => false,
		'garide' => false,
		'garider' => false,
		'incentive' => false,
		'invoice' => false,
		'invoiceitem' => false,
		'job' => false,
		'jobevent' => false,
		'jobshift' => false,
		'journalitem' => false,
		'mailattachment' => false,
		'mailitem' => false,
		'mailpiece' => false,
		'member' => false,
		'membercategory' => false,
		'memberofmonth' => false,
		'membership' => false,
		'migration' => false,
		'newsletter' => false,
		'pace' => false,
		'payment' => false,
		'paypalrefund' => false,
		'permissiongroup' => false,
		'permission' => false,
		'photo' => false,
		'photocomment' => false,
		'photofolder' => false,
		'phototag' => false,
		'pointhistory' => false,
		'poll' => false,
		'pollanswer' => false,
		'pollresponse' => false,
		'publicpage' => false,
		'redirect' => false,
		'reservation' => false,
		'reservationperson' => false,
		'ride' => false,
		'ridecomment' => false,
		'rideincentive' => false,
		'ridesignup' => false,
		'rwgps' => false,
		'setting' => false,
		'signinsheet' => false,
		'signinsheetride' => false,
		'startlocation' => false,
		'storeitem' => false,
		'storeitemdetail' => false,
		'story' => false,
		'systememail' => false,
		'userpermission' => false,
		'volunteerjobshift' => false,
		'volunteerpoint' => false,
		'volunteerpoll' => false,
		'volunteerpollanswer' => false,
		'volunteerpollresponse' => false,
		'ziptax' => false,
	];

	public function __construct(private readonly \PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Initialize the Database', 4));

		$tableCursor = \PHPFUI\ORM::getArrayCursor('show tables');
		$currentTables = [];

		foreach ($tableCursor as $tableArray)
			{
			$table = \strtolower((string)\array_pop($tableArray));
			$currentTables[$table] = false;
			}

		$extraTables = [];

		foreach ($currentTables as $table => $inuse)
			{
			if (isset($this->officialTables[$table]))
				{
				$this->officialTables[$table] = true;
				}
			else
				{
				$extraTables[] = $table;
				}
			}

		$missingTables = [];

		foreach ($this->officialTables as $table => $inuse)
			{
			if (! $inuse)
				{
				$missingTables[] = $table;
				}
			}

		$settings = new \App\Settings\DB();

		if (isset($_GET['extra']))
			{
			foreach ($tableCursor as $tableArray)
				{
				$table = \array_pop($tableArray);

				if (\in_array(\strtolower((string)$table), $extraTables))
					{
					\PHPFUI\ORM::execute('drop table ' . $table);
					}
				}
			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

		if (isset($_GET['init']))
			{
			$restore = new \App\Model\Restore(PROJECT_ROOT . '/Initial.schema');

			if (! $restore->run())
				{
				\App\Model\Session::setFlashList('alert', $restore->getErrors());
				}

			$this->page->redirect('/Config/wizard/' . $settings->stage);

			return;
			}

		$initDB = false;
		$dropExtra = \count($extraTables) > 0;
		$callout = '';

		if (! \count($missingTables) && ! \count($extraTables))
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('All required tables are present and no extra tables have been found.');
			$callout->add('<br><br>It is optional to the initialize database.');
			}
		elseif (\count($missingTables) == \count($this->officialTables))
			{
			$callout = new \PHPFUI\Callout('success');
			$callout->add('This appears to be an empty database, which is good!<br><br>Click on the Initialize Database button to continue.');
			$initDB = true;
			}
		elseif (! \count($missingTables) && \count($extraTables))
			{
			$callout = new \PHPFUI\Callout('warning');
			$callout->add('All required tables are present, but these tables are extra:<p>');
			$callout->add($this->list($extraTables));
			$callout->add('<p>It is optional to the initialize database or remove the extra tables.');
			}
		elseif (\count($missingTables))
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('These tables are missing:<p>');
			$callout->add($this->list($missingTables));
			$callout->add('<p>You must click on the Initialize Database button to continue.');
			$initDB = true;
			}

		$wizardBar->nextAllowed(! $initDB);

		if ($dropExtra)
			{
			$removeExtraButton = new \PHPFUI\Button('Remove Extra Tables', $this->page->getBaseURL() . '?extra');
			$removeExtraButton->addClass('warning');
			$removeExtraButton->setConfirm('Are you sure you want to remove the extra tables?');
			$wizardBar->addButton($removeExtraButton);
			}
		$initButton = new \PHPFUI\Button('Initialize Database', $this->page->getBaseURL() . '?init');

		if (! $initDB)
			{
			$initButton->addClass('warning');
			$initButton->setConfirm('Are you sure you want to initialize the database and remove all existing data?');
			}
		else
			{
			$initButton->addClass('success');
			}
		$wizardBar->addButton($initButton);
		$this->add($callout);
		$this->add($wizardBar);
		}

	private function list(array $tables) : \PHPFUI\UnorderedList
		{
		$ul = new \PHPFUI\UnorderedList();

		foreach ($tables as $table)
			{
			$ul->addItem(new \PHPFUI\ListItem($table));
			}

		return $ul;
		}
	}
