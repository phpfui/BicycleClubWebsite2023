<?php

namespace App\UI;

class TableEditor
	{
	private array $headers = ['name' => 'Name', 'shortName' => 'Short Name', 'abbrev' => 'Abbrevation', 'delete' => 'Del'];

	private string $name;

	private \PHPFUI\ORM\Table $table;

	public function __construct(private \App\View\Page $page, string $table)
		{
		$class = "\\App\\Table\\{$table}";
		$this->table = new $class();
		$this->name = \lcfirst($table);
		}

	public function edit()
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->setAreYouSure();

		if ($form->isMyCallback())
			{
			$this->table->updateFromTable($_POST);
			$this->page->setResponse('Saved');

			return '';
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteRecord':
					$fieldName = $this->name . 'Id';
					$id = (int)$_POST[$fieldName];
					$this->table->setWhere(new \PHPFUI\ORM\Condition($fieldName, $id))->delete();
					$this->page->setResponse((string)$id);

					break;

				case 'Add':
					$record = $this->table->getRecord();
					$record->setFrom($_POST)->insert();
					$this->page->redirect();

					break;

				default:
					$this->page->redirect();
				}
			}
		else
			{
			$recordId = $this->name . 'Id';
			$types = $this->table->addOrderBy('name')->getArrayCursor();
			$delete = new \PHPFUI\AJAX('deleteRecord', 'Permanently delete this?');
			$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast")');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->addAttribute('style', 'width: 100%;');
			$table->setRecordId($recordId);
			$table->setHeaders($this->headers);

			foreach ($types as $row)
				{
				$id = $row[$recordId];
				$name = new \PHPFUI\Input\Text("name[{$id}]", '', $row['name']);
				$hidden = new \PHPFUI\Input\Hidden("{$recordId}[{$id}]", $id);
				$row['name'] = $name . $hidden;

				if (isset($this->headers['shortName']))
					{
					$row['shortName'] = new \PHPFUI\Input\Text("shortName[{$id}]", '', $row['shortName']);
					}

				if (isset($this->headers['abbrev']))
					{
					$row['abbrev'] = new \PHPFUI\Input\Text("abbrev[{$id}]", '', $row['abbrev']);
					}
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
				$row['delete'] = $icon;
				$table->addRow($row);
				}

			$form->add($table);
			$add = new \PHPFUI\Button('Add');

			if (\count($types))
				{
				$form->saveOnClick($add);
				$form->add($submit);
				}

			$this->addModal($add);
			$form->add(' &nbsp; ');
			$form->add($add);
			}

		return $form;
		}

	public function setHeaders(array $headers) : self
		{
		$this->headers = $headers;

		return $this;
		}

	private function addModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add ' . \ucfirst($this->name));
		$name = new \PHPFUI\Input\Text('name', 'Name');
		$fieldSet->add($name);
		$multiColumn = new \PHPFUI\MultiColumn();

		if (isset($this->headers['shortName']))
			{
			$multiColumn->add(new \PHPFUI\Input\Text('shortName', 'Short Name'));
			}

		if (isset($this->headers['abbrev']))
			{
			$abbrev = new \PHPFUI\Input\Text('abbrev', 'Abbreviation');
			}

		if (\count($multiColumn))
			{
			$fieldSet->add($multiColumn);
			}
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
