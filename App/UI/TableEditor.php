<?php

namespace App\UI;

class TableEditor
	{
	private string $primaryKey;

	private ?\PHPFUI\ORM\Table $relatedTable = null;

	/**
	 * @param array<string,string> $headers
	 */
	public function __construct(private \App\View\Page $page, private \PHPFUI\ORM\Table $table, private array $headers = [])
		{
		$this->primaryKey = $this->table->getPrimaryKeys()[0];
		}

	public function edit() : \PHPFUI\Form | string
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
					$fieldName = $this->primaryKey;
					$record = $this->table->getRecord();
					$record->{$fieldName} = (int)$_POST[$fieldName];
					$record->delete();
					$this->page->setResponse($_POST[$fieldName]);

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
			$delete = new \PHPFUI\AJAX('deleteRecord', 'Permanently delete this?');
			$delete->addFunction('success', '$("#' . $this->primaryKey . '-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$table = new \PHPFUI\Table();
			$table->addAttribute('style', 'width: 100%;');
			$table->setRecordId($this->primaryKey);
			$table->setHeaders($this->headers);

			$records = $this->table->getArrayCursor();

			foreach ($records as $row)
				{
				$id = $row[$this->primaryKey];
				$hidden = new \PHPFUI\Input\Hidden("{$this->primaryKey}[{$id}]", $id);

				foreach ($this->headers as $field => $name)
					{
					if ('delete' === $field)
						{
						continue;
						}
					$input = new \PHPFUI\Input\Text($field . "[{$id}]", '', $row[$field]);
					$row[$field] = $input . $hidden;
					$hidden = null;
					}
				$deleteable = true;

				if ($this->relatedTable)
					{
					$this->relatedTable->setWhere(new \PHPFUI\ORM\Condition($this->primaryKey, $row[$this->primaryKey]));
					$deleteable = 0 == $this->relatedTable->count();
					}

				if ($deleteable)
					{
					$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$icon->addAttribute('onclick', $delete->execute([$this->primaryKey => $id]));
					$row['delete'] = $icon;
					}
				$table->addRow($row);
				}

			$form->add($table);
			$add = new \PHPFUI\Button('Add');
			$add->addClass('success');

			if (\count($records))
				{
				$form->saveOnClick($add);
				$form->add($submit);
				}

			$this->addModal($add);
			$form->add($add);
			}

		return $form;
		}

	/**
	 * @param array<string,string> $headers
	 */
	public function setHeaders(array $headers) : self
		{
		$this->headers = $headers;

		return $this;
		}

	public function setRelatedTable(\PHPFUI\ORM\Table $relatedTable) : self
		{
		$this->relatedTable = $relatedTable;

		return $this;
		}

	private function addModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add ' . \ucfirst($this->table->getTableName()));
		$multiColumn = new \PHPFUI\MultiColumn();

		foreach ($this->headers as $field => $name)
			{
			if ('delete' === $field)
				{
				continue;
				}
			$multiColumn->add(new \PHPFUI\Input\Text($field, $this->headers[$field]));
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
