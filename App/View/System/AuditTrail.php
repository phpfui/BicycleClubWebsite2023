<?php

namespace App\View\System;

class AuditTrail
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function getTrail() : \PHPFUI\Form
		{
		$deleteChecked = new \PHPFUI\Submit('Delete Selected');
		$deleteChecked->setConfirm('Delete all selected audit trail entries?');
		$deleteChecked->addClass('alert');
		$grid = new \PHPFUI\GridX();
		$grid->add($deleteChecked)->addClass('align-right');

		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);

		if ($form->isMyCallback($deleteChecked))
			{
			$ids = [];

			foreach ($_POST['ca'] ?? [] as $auditTrailId => $delete)
				{
				if ((int)$delete)
					{
					$ids[] = $auditTrailId;
					}
				}

			if (\count($ids))
				{
				$auditTrailTable = new \App\Table\AuditTrail();
				$auditTrailTable->setWhere(new \PHPFUI\ORM\Condition('auditTrailId', $ids, new \PHPFUI\ORM\Operator\In()));
				$auditTrailTable->delete();
				}
			$this->page->redirect(parameters:$_SERVER['QUERY_STRING']);

			return $form;
			}

		$form->add($grid);
		$checkAll = new \App\UI\CheckAll('.checkAll');

		$headers = [
			'time' => 'Time',
			'statement' => 'Statement',
			'input' => 'JSON Input',
			'additional' => 'Additional',
			'firstName' => 'First Name',
			'lastName' => 'Last Name',
			'ca' => (string)$checkAll,
		];

		$auditTable = new \App\Table\AuditTrail();
		$auditTable->addJoin('member');

		$view = new \App\UI\ContinuousScrollTable($this->page, $auditTable);
		$view->setHeaders($headers);
		unset($headers['firstName'], $headers['lastName'], $headers['ca']);
		$view->setSearchColumns($headers)->setSortableColumns(\array_keys($headers));
		$view->addCustomColumn('statement', static fn (array $trail) : string => \str_replace(',', ',<wbr>', (string)$trail['statement']));
		$view->addCustomColumn('input', static fn (array $trail) : string => \str_replace(',', ',<wbr>', (string)$trail['input']));
		$view->addCustomColumn('additional', static fn (array $trail) : string => \str_replace('#', '<br>#', (string)$trail['additional']));
		$view->addCustomColumn('ca', static function(array $trail) : \PHPFUI\Input\CheckBoxBoolean {$cb = new \PHPFUI\Input\CheckBoxBoolean("ca[{$trail['auditTrailId']}]");$cb->addClass('checkAll');

return $cb;});
		$form->add($view);

		return $form;
		}
	}
