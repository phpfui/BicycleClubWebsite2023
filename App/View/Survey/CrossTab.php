<?php

namespace App\View\Survey;

class CrossTab
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function crossTab(\App\Record\SurveyCrossTab $crossTab) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$surveyFileModel = new \App\Model\SurveyFile();

		$container->add(new \PHPFUI\Header($crossTab->name, 4));
		$container->add($crossTab->description);

		$container->add($surveyFileModel->getCrossTabStats($crossTab));

		return $container;
		}

	public function edit(\App\Record\Survey $survey = new \App\Record\Survey()) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$recordId = 'surveyCrossTabId';

		if ($form->isMyCallback())
			{
			$order = 1;
			$post = $_POST;

			foreach ($post[$recordId] ?? [] as $index => $value)
				{
				$post['ordering'][$index] = $order++;
				}

			new \App\Table\SurveyCrossTab()->updateFromTable($post);

			$this->page->setResponse('Saved');

			return $form;
			}


		if ($survey->name)
			{
			$form->add(new \PHPFUI\SubHeader($survey->name));
			}
		$fieldSet = new \PHPFUI\FieldSet('Cross Tabs');

		$table = new \PHPFUI\OrderableTable($this->page);
		$table->setRecordId($recordId);
		$table->addHeader('name', 'CrossTab Name');
		$table->addHeader('rowName', 'Row Name');
		$table->addHeader('columnName', 'Column Name');
		$table->addHeader('edit', 'Edit');
		$table->addHeader('stats', 'View');
		$table->addHeader('del', 'Delete');

		$delete = new \PHPFUI\AJAX('deleteCrossTab', 'Permanently delete this crosstab?');
		$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($delete->getPageJS());

		foreach ($survey->CrossTabChildren as $surveyCrossTab)
			{
			$row = $surveyCrossTab->toArray();
			$id = $row[$recordId];
			$row['columnName'] .= new \PHPFUI\Input\Hidden("{$recordId}[{$id}]", "{$id}");
			$row['stats'] = $this->getCrossTabReveal($surveyCrossTab, new \PHPFUI\FAIcon('fas', 'table-cells'));
			$row['edit'] = new \PHPFUI\FAIcon('far', 'edit', '/Survey/editCrossTab/' . $id);
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
			$row['del'] = $icon;
			$table->addRow($row);
			}
		$fieldSet->add($table);

		$add = new \PHPFUI\Button('Add CrossTab');
		$add->addClass('success');
		$this->addCrossTabModal($add, $survey);
		$fieldSet->add($add);

		$form->add($fieldSet);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton(new \PHPFUI\Button('Edit Survey', '/Survey/edit/' . $survey->surveyId)->addClass('info'));
		$buttonGroup->addButton(new \PHPFUI\Button('All Surveys', '/Survey/list')->addClass('secondary'));

		$form->add($buttonGroup);

		return $form;
		}

	public function editCrossTab(\App\Record\SurveyCrossTab $crossTab) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \App\UI\ErrorFormSaver($this->page, $crossTab, $submit);
		$survey = $crossTab->survey;
		$fieldSet = new \PHPFUI\FieldSet('CrossTab Editor');
		$fieldSet->add(new \PHPFUI\Input\Hidden('surveyId', (string)$survey->surveyId));

		if ($form->save())
			{
			return $form;
			}

		$fieldSet->add($this->getCrossTabFields($survey, $crossTab));
		$form->add($fieldSet);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$testButton = new \PHPFUI\Button('Test CrossTab')->addClass('success');
		$form->saveOnClick($testButton);
		$this->getCrossTabReveal($crossTab, $testButton);
		$buttonGroup->addButton($testButton);
		$buttonGroup->addButton(new \PHPFUI\Button('All CrossTabs', '/Survey/editCrossTabs/' . $survey->surveyId)->addClass('info'));
		$buttonGroup->addButton(new \PHPFUI\Button('All Surveys', '/Survey/list')->addClass('secondary'));

		$form->add($buttonGroup);

		return $form;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{

				case 'deleteCrossTab':
					$crossTab = new \App\Record\SurveyCrossTab((int)$_POST['surveyCrossTabId']);
					$crossTab->delete();
					$this->page->setResponse($_POST['surveyCrossTabId']);

					break;

				case 'Add CrossTab':

					$surveyCrossTab = new \App\Record\SurveyCrossTab();
					$surveyCrossTab->setFrom($_POST);

					if (empty($surveyCrossTab->rowName))
						{
						$surveyCrossTab->rowName = $surveyCrossTab->rowSurveyQuestion->displayName;
						}

					if (empty($surveyCrossTab->columnName))
						{
						$surveyCrossTab->columnName = $surveyCrossTab->columnSurveyQuestion->displayName;
						}
					$surveyCrossTab->insert();
					$this->page->redirect();

					break;

				default:

					$this->page->redirect();

				}
			}
		}

	private function addCrossTabModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Survey $survey) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');

		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Add CrossTab');
		$fieldSet->add($this->getCrossTabFields($survey));

		$form->add($fieldSet);

		$form->setAreYouSure(false);

		$submit = new \PHPFUI\Submit('Add CrossTab', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getCrossTabFields(\App\Record\Survey $survey, \App\Record\SurveyCrossTab $crossTab = new \App\Record\SurveyCrossTab()) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$columnQuestionSelect = new \PHPFUI\Input\Select('columnSurveyQuestionId', 'Column Question');
		$rowQuestionSelect = new \PHPFUI\Input\Select('rowSurveyQuestionId', 'Row Question');
		$columnQuestionSelect->addOption('Row Totals Only', '', 0 == $crossTab->rowSurveyQuestionId);

		foreach ($survey->QuestionChildren as $question)
			{
			$columnQuestionSelect->addOption($question->displayName, (string)$question->surveyQuestionId, $question->surveyQuestionId == $crossTab->columnSurveyQuestionId);
			$rowQuestionSelect->addOption($question->displayName, (string)$question->surveyQuestionId, $question->surveyQuestionId == $crossTab->rowSurveyQuestionId);
			}

		$container->add(new \PHPFUI\Input\Hidden('surveyId', (string)$survey->surveyId));
		$container->add(new \PHPFUI\Input\Text('name', 'Name of the CrossTab', $crossTab->name));

		$fieldSet = new \PHPFUI\FieldSet('Row Question (Required)');
		$fieldSet->add($rowQuestionSelect);
		$fieldSet->add(new \PHPFUI\Input\Text('rowName', 'Display Row Name', $crossTab->rowName));
		$fieldSet->add(new \PHPFUI\Input\CheckBoxBoolean('percent', 'Display Percentages', (bool)$crossTab->percent));
		$container->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Column Question (Optional)');
		$fieldSet->add($columnQuestionSelect);
		$fieldSet->add(new \PHPFUI\Input\Text('columnName', 'Display Column Name', $crossTab->columnName));
		$container->add($fieldSet);

		$descriptionField = new \App\UI\TextAreaImage('description', 'Description of displayed crosstab', $crossTab->description ?? '');
		$descriptionField->setToolTip('This will be shown above the crosstab whenb displayed');
		$descriptionField->htmlEditing($this->page, new \App\Model\TinyMCETextArea($crossTab->getLength('description'), ['height' => '"20em"']));
		$container->add($descriptionField);

		return $container;
		}

	private function getCrossTabReveal(\App\Record\SurveyCrossTab $surveyCrossTab, \PHPFUI\HTML5Element $opener) : \PHPFUI\HTML5Element
		{
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$container = new \PHPFUI\HTML5Element('div');
		$reveal->add($container);
		$reveal->add($reveal->getCloseButton());
		$reveal->loadUrlOnOpen('/Survey/crossTab/' . $surveyCrossTab->surveyCrossTabId, $container->getId());

		return $opener;
		}
	}
