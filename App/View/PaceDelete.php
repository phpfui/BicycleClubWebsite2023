<?php

namespace App\View;

class PaceDelete implements \Stringable
	{
	private readonly \App\Table\Pace $paceTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Pace $paceToDelete)
		{
		$this->paceTable = new \App\Table\Pace();

		if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
			{
			if ('Delete' == $_POST['submit'])
				{
				\App\Table\Ride::changePace($_POST['deletePace'], $_POST['mergePace']);
				$pace = new \App\Record\Pace((int)$_POST['deletePace']);
				$pace->delete();
				$this->page->redirect('/Leaders/categories');
				}
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);

		if (! $this->paceToDelete->empty())
			{
			$form->add("<h3>Are you sure you want to delete pace {$this->paceToDelete->pace}?</h3>");
			$alert = new \PHPFUI\Callout('info');
			$alert->add('In order to delete a pace, you need to merge it into another pace to preserve any rides associated with it.
				Any ride with the old pace will now have the new pace you specify.');
			$form->add($alert);
			$form->add(new \PHPFUI\Input\Hidden('deletePace', (string)$this->paceToDelete->paceId));

			$pacePicker = new \PHPFUI\Input\Select('mergePace', "Pace to merge {$this->paceToDelete->pace} into");
			$paces = $this->paceTable->getPaceOrder($this->paceToDelete->categoryId);

			foreach ($paces as $pace)
				{
				if ($pace['paceId'] != $this->paceToDelete->paceId)
					{
					$pacePicker->addOption($pace['pace'], $pace['paceId']);
					}
				}
			$form->add($pacePicker);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Delete'));
			$cancel = new \PHPFUI\Button('Cancel', '/Leaders/categories');
			$cancel->addClass('hollow')->addClass('alert');
			$buttonGroup->addButton($cancel);
			$form->add($buttonGroup);
			}
		else
			{
			$form->add(new \PHPFUI\SubHeader('Invalid Pace'));
			}

		return (string)"{$form}";
		}
	}
