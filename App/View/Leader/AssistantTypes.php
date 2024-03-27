<?php

namespace App\View\Leader;

class AssistantTypes implements \Stringable
	{
	private readonly \App\Table\AssistantLeaderType $assistantLeaderTypeTable;

	private string $primaryKey = 'assistantLeaderTypeId';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->assistantLeaderTypeTable = new \App\Table\AssistantLeaderType();
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->assistantLeaderTypeTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteType':

					$assistantLeaderType = new \App\Record\AssistantLeaderType($_POST[$this->primaryKey]);
					$assistantLeaderType->delete();
					$this->page->setResponse($_POST[$this->primaryKey]);

					break;


				case 'Add':

					$assistantLeaderType = new \App\Record\AssistantLeaderType();
					$assistantLeaderType->setFrom($_POST);
					$assistantLeaderType->insert();
					$this->page->redirect();

					break;

				default:

					$this->page->redirect();

				}
			}
		else
			{
			$this->assistantLeaderTypeTable->addOrderBy('name');

			$delete = new \PHPFUI\AJAX('deleteType', 'Permanently delete this type?');
			$delete->addFunction('success', '$("#' . $this->primaryKey . '-"+data.response).css("background-color","red").hide("slow").remove();');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->setRecordId($this->primaryKey);
			$table->addHeader('name', 'Name');
			$table->addHeader('volunteerPoints', 'Volunteer Points');
			$table->addHeader('delete', 'Del');

			$assistantLeaderTable = new \App\Table\AssistantLeader();

			foreach ($this->assistantLeaderTypeTable->getRecordCursor() as $assistantLeaderType)
				{
				$row = $assistantLeaderType->toArray();
				$id = $row[$this->primaryKey];
				$name = new \PHPFUI\Input\Text("name[{$id}]", '', $assistantLeaderType->name);
				$hidden = new \PHPFUI\Input\Hidden("{$this->primaryKey}[{$id}]", $id);
				$row['volunteerPoints'] = new \PHPFUI\Input\Number("volunteerPoints[{$id}]", '', $assistantLeaderType->volunteerPoints);
				$row['name'] = $name . $hidden;
				$assistantLeaderTable->setWhere(new \PHPFUI\ORM\Condition('assistantLeaderTypeId', $assistantLeaderType->assistantLeaderTypeId));

				if (! \count($assistantLeaderTable))
					{
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$icon->addAttribute('onclick', $delete->execute([$this->primaryKey => $id]));
					$row['delete'] = $icon;
					}
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($this->assistantLeaderTypeTable))
				{
				$buttonGroup->addButton($submit);
				}
			$add = new \PHPFUI\Button('Add Assistant Type');
			$add->addClass('warning');
			$this->addTypeModal($add);
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return $output;
		}

	public function stats(\App\Record\Member $member, int $year) : ?\PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet("Assistant Leader Statistics for {$member->fullName()} in {$year}");

		$assistantLeaderTable = new \App\Table\AssistantLeader();
		$assistantLeaderTable->addJoin('assistantLeaderType');
		$assistantLeaderTable->addJoin('ride');
		$condition = new \PHPFUI\ORM\Condition('assistantLeader.memberId', $member->memberId);
		$condition->and('ride.rideDate', "{$year}-01-01", new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('ride.rideDate', "{$year}-12-31", new \PHPFUI\ORM\Operator\LessThanEqual());
		$assistantLeaderTable->setWhere($condition);
		$assistantLeaderTable->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$assistantLeaderTable->addSelect(new \PHPFUI\ORM\Field('assistantLeaderType.name'));
		$assistantLeaderTable->addGroupBy('assistantLeader.assistantLeaderTypeId');
		$assistantLeaderTable->addOrderBy('assistantLeaderType.name');
		$table = new \PHPFUI\Table();
		$table->setHeaders(['name' => 'Type', 'count' => 'Total']);

		foreach ($assistantLeaderTable->getArrayCursor() as $row)
			{
			if (empty($row['name']))
				{
				$row['name'] = 'Assistant Leader';
				}
			$table->addRow($row);
			}

		if (! $table->count())
			{
			return null;
			}

		$fieldSet->add($table);

		return $fieldSet;
		}

	private function addTypeModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add Assistant Type');
		$name = new \PHPFUI\Input\Text('name', 'Type');
		$name->setRequired();
		$fieldSet->add($name);
		$points = new \PHPFUI\Input\Number('volunteerPoints', 'Volunteer Points');
		$fieldSet->add($points);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
