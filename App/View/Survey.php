<?php

namespace App\View;

class Survey
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function edit(\App\Record\Survey $survey = new \App\Record\Survey()) : \App\UI\ErrorFormSaver
		{
		$fieldSet = new \PHPFUI\FieldSet('Survey Information');

		$name = new \PHPFUI\Input\Text('name', 'Survey Name', $survey->name);
		$name->setRequired()->setToolTip('This is the public name, so make it clear and descriptive');

		if ($survey->loaded())
			{
			$downloadButton = new \PHPFUI\Button('Download CVS File', '/Survey/download/' . $survey->surveyId)->addClass('success');
			$uploadButton = new \PHPFUI\Button('Update CSV File')->addClass('warning');
			$this->addUpdateCSVModal($uploadButton, $survey);
			$multiColumn = new \PHPFUI\MultiColumn('<b>CSV Uploaded</b>', $survey->uploaded, $uploadButton, $downloadButton);
			$fieldSet->add($multiColumn);

			$file = null;
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $survey, $submit);
			$form->add(new \PHPFUI\Input\Hidden('surveyId', (string)$survey->surveyId));
			}
		else
			{
			$name->setRequired();
			$file = new \PHPFUI\Input\File($this->page, 'survey', 'Drag and drop CSV file to upload')
				->setRequired()->setAllowedExtensions(['csv'])->setToolTip('This must be a comma separated CSV file');
			$submit = new \PHPFUI\Submit('Add', 'action');
			$form = new \App\UI\ErrorFormSaver($this->page, $survey);
			}

		if ($form->save())
			{
			$surveyQuestionTable = new \App\Table\SurveyQuestion();
			$surveyQuestionTable->updateFromTable($_POST);

			return $form;
			}

		$fieldSet->add($name);

		$fieldSet->add($file);

		$descriptionField = new \App\UI\TextAreaImage('description', 'Description of survey', $survey->description ?? '');
		$descriptionField->setToolTip('This will be shown at the top of the survey crosstabs');
		$descriptionField->htmlEditing($this->page, new \App\Model\TinyMCETextArea($survey->getLength('description'), ['height' => '"20em"']));
		$fieldSet->add($descriptionField);

		$form->add($fieldSet);

		if ($survey->loaded())
			{
			$form->add(new \PHPFUI\Button('Edit Cross Tabs', '/Survey/editCrossTabs/' . $survey->surveyId));

			$fieldSet = new \PHPFUI\FieldSet('Survey Columns');
			$table = new \PHPFUI\Table();
			$recordId = 'surveyQuestionId';
			$table->setRecordId($recordId);
			$table->addHeader('columnName', 'Column Name');
			$table->addHeader('displayName', 'Display Name');
			$table->addHeader('separator', 'Separator');
			$table->addHeader('stats', 'Quick<wbr>Stats');
			$table->addHeader('revise', 'Revise');
			$table->addHeader('del', 'Delete');

			$delete = new \PHPFUI\AJAX('deleteColumn', 'Permanently delete this column and all associated crosstabs?');
			$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());

			foreach ($survey->QuestionChildren as $surveyQuestion)
				{
				$row = $surveyQuestion->toArray();
				$id = $row[$recordId];
				$row['columnName'] .= new \PHPFUI\Input\Hidden('surveyQuestionId[' . $id . ']', $id);
				$row['displayName'] = new \PHPFUI\Input\Text('displayName[' . $id . ']', '', $surveyQuestion->displayName);
				$row['separator'] = new \PHPFUI\Input\Text('separator[' . $id . ']', '', $surveyQuestion->separator)->addAttribute('maxLength', '1')->addClass('single-char');
				$row['stats'] = $this->getStatsReveal($surveyQuestion);
				$row['revise'] = $this->getReviseReveal($surveyQuestion);
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute([$recordId => $id]));
				$row['del'] = $icon;
				$table->addRow($row);
				}
			$fieldSet->add($table);

			$add = new \PHPFUI\Button('Add Column');
			$add->addClass('success');
			$this->addQuestionModal($add, $survey);
			$fieldSet->add($add);
			$form->add($fieldSet);
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton(new \PHPFUI\Button('All Surveys', '/Survey/list')->addClass('info'));

		$form->add($buttonGroup);

		return $form;
		}

	public function list(\App\Table\Survey $surveyTable) : \PHPFUI\Container
		{
		$recordId = 'surveyId';
		$table = new \App\UI\ContinuousScrollTable($this->page, $surveyTable);
		$table->setRecordId($recordId);
		$delete = new \PHPFUI\AJAX('deleteSurvey', 'Permanently delete this survey event and all related data and cross tabs?');
		$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($delete->getPageJS());

		$headers = ['name', 'uploaded'];
		$table->setSearchColumns($headers)->setSortableColumns($headers);

		if ($this->page->isAuthorized('Edit Survey'))
			{
			$headers[] = 'edit';
			$table->addCustomColumn('edit', static fn (array $survey) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'edit', '/Survey/edit/' . $survey[$recordId]));
			}

		if ($this->page->isAuthorized('Edit Survey'))
			{
			$headers[] = 'del';
			$table->addCustomColumn('del', static fn (array $survey) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'trash-alt', '#')->addAttribute('onclick', $delete->execute([$recordId => $survey[$recordId]])));
			}

		$table->setHeaders($headers);

		$table->addCustomColumn('name', static fn (array $survey) : \PHPFUI\Link => new \PHPFUI\Link('/Survey/show/' . $survey[$recordId], $survey['name'], false));

		$container = new \PHPFUI\Container();
		$container->add($table);

		if ($this->page->isAuthorized('Add Survey'))
			{
			$add = new \PHPFUI\Button('Add Survey');
			$add->addClass('success');
			$this->addSurveyModal($add);
			$container->add($add);
			}

		return $container;
		}

	public function show(\App\Record\Survey $survey) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$surveyFileModel = new \App\Model\SurveyFile();
		$container->add($survey->description);

		foreach ($survey->CrossTabChildren as $crossTab)
			{
			$container->add('<hr>');
			$container->add($surveyFileModel->getCrossTabStats($crossTab));
			}

		return $container;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{

				case 'Revise Data':

					$surveyQuestion = new \App\Record\SurveyQuestion($_POST['surveyQuestionId']);
					$changes = [];

					foreach ($_POST['original'] as $index => $oldValue)
						{
						$newValue = $_POST['replace'][$index];

						if ($newValue !== $oldValue)
							{
							$changes[$oldValue] = $newValue;
							}
						}

					$fileModel = new \App\Model\SurveyFile();

					$revisedCSV = '../revisedSurvey.csv';
					$originalCSV = $fileModel->get($surveyQuestion->surveyId);

					$revised = new \App\Tools\CSV\FileWriter($revisedCSV, false);
					$original = new \App\Tools\CSV\FileReader($originalCSV);

					foreach ($original as $row)
						{
						$originalValue = $row[$surveyQuestion->columnName];
						$row[$surveyQuestion->columnName] = $changes[$originalValue] ?? $originalValue;
						$revised->outputRow($row);
						}

					unset($revised);

					\copy($revisedCSV, $originalCSV);
					\unlink($revisedCSV);

					$this->page->redirect();

					break;

				case 'deleteColumn':

					$surveyQuestion = new \App\Record\SurveyQuestion((int)$_POST['surveyQuestionId']);
					$surveyQuestion->delete();
					$this->page->setResponse($_POST['surveyQuestionId']);

					break;

				case 'Add Column':

					$surveyQuestion = new \App\Record\SurveyQuestion();

					if (empty($_POST['displayName']))
						{
						$_POST['displayName'] = $_POST['colunmName'];
						}
					$surveyQuestion->setFrom($_POST);
					$surveyQuestion->insert();
					$this->page->redirect();

					break;

				case 'deleteSurvey':

					$survey = new \App\Record\Survey((int)$_POST['surveyId']);
					$survey->delete();
					$this->page->setResponse($_POST['surveyId']);

					break;

				case 'Upload New CSV File':

					$fileModel = new \App\Model\SurveyFile();

					$id = '../updatedSurvey';

					if ($fileModel->upload($id, 'survey', $_FILES, ['.csv' => 'text/csv']))
						{
						$originalFile = $fileModel->get($_POST['surveyId']);
						$updatedFile = $fileModel->get($id);
						$original = new \App\Tools\CSV\FileReader($originalFile)->current();
						$updated = new \App\Tools\CSV\FileReader($updatedFile)->current();
						$diff = \array_diff(\array_keys($original), \array_keys($updated));

						if (\count($diff))
							{
							\App\Model\Session::setFlash('warning', 'The following headers are missing:<br>' . \implode('<br>', $diff));
							}
						else
							{
							\copy($updatedFile, $originalFile);
							\App\Model\Session::setFlash('success', 'The CSV file has been updated');
							}
						\unlink($updatedFile);
						}
					else
						{
						\App\Model\Session::setFlash('alert', $fileModel->getLastError());
						}
					$this->page->redirect();

					break;

				case 'Add':

					$survey = new \App\Record\Survey();
					$survey->setFrom($_POST);
					$survey->uploaded = \App\Tools\Date::todayString();
					$id = $survey->insert();

					$fileModel = new \App\Model\SurveyFile();

					if ($fileModel->upload($id, 'survey', $_FILES, ['.csv' => 'text/csv']))
						{
						$reader = new \App\Tools\CSV\FileReader($fileModel->get($id));

						foreach ($reader->current() as $field => $value)
							{
							$surveyQuestion = new \App\Record\SurveyQuestion();
							$surveyQuestion->survey = $survey;
							$surveyQuestion->columnName = $surveyQuestion->displayName = $field;
							$surveyQuestion->insert();
							}
						$url = "/Survey/edit/{$id}";
						}
					else
						{
						$url = '';
						\App\Model\Session::setFlash('alert', $fileModel->getLastError());
						$survey->delete();
						}
					$this->page->redirect($url);

					break;

				default:

					$this->page->redirect();

				}
			}
		}

	private function addQuestionModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Survey $survey) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');

		$surveyFileModel = new \App\Model\SurveyFile();
		$csvReader = new \App\Tools\CSV\FileReader($surveyFileModel->get($survey->surveyId));
		$columnInput = new \PHPFUI\Input\Select('columnName', 'Select Column');

		foreach ($csvReader->current() as $field => $value)
			{
			$columnInput->addOption($field);
			}

		$form = new \App\UI\ErrorForm($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Add Survey Column');
		$fieldSet->add($columnInput);
		$name = new \PHPFUI\Input\Text('displayName', 'Display Name');
		$fieldSet->add($name);
		$separator = new \PHPFUI\Input\Text('separator', 'Column separator for multiple choice questions')->addAttribute('maxLength', '1')->addClass('single-char');
		$fieldSet->add($separator);
		$fieldSet->add(new \PHPFUI\Input\Hidden('surveyId', (string)$survey->surveyId));

		$form->add($fieldSet);

		$form->setAreYouSure(false);

		$submit = new \PHPFUI\Submit('Add Column', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function addSurveyModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');

		$survey = new \App\Record\Survey();
		$form = new \App\UI\ErrorForm($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Survey Information');
		$name = new \PHPFUI\Input\Text('name', 'Survey Name', $survey->name);
		$name->setRequired()->setToolTip('This is the public name, so make it clear and descriptive');
		$fieldSet->add($name);

		$file = new \PHPFUI\Input\File($this->page, 'survey', 'Drag and drop CSV file to upload')
			->setRequired()->setAllowedExtensions(['csv'])->setToolTip('This must be a comma separated CSV file');
		$fieldSet->add($file);
		$form->add($fieldSet);

		$form->setAreYouSure(false);

		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function addUpdateCSVModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Survey $survey) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Update CSV File');
		$callOut = new \PHPFUI\Callout('info');
		$callOut->add('Updated CSV file must contain all the headers of the original file');
		$fieldSet->add($callOut);
		$fieldSet->add(new \PHPFUI\Input\Hidden('surveyId', (string)$survey->surveyId));

		$file = new \PHPFUI\Input\File($this->page, 'survey', 'Drag and drop CSV file to upload')
			->setRequired()->setAllowedExtensions(['csv'])->setToolTip('This must be a comma separated CSV file');
		$fieldSet->add($file);

		$form->setAreYouSure(false);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Upload New CSV File', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getReviseReveal(\App\Record\SurveyQuestion $surveyQuestion) : \PHPFUI\HTML5Element
		{
		$opener = new \PHPFUI\FAIcon('fas', 'filter');
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$form = new \PHPFUI\Form($this->page);

		$container = new \PHPFUI\HTML5Element('div');
		$form->add($container);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Revise Data', 'action'));
		$buttonGroup->addButton($reveal->getCloseButton());
		$form->add($buttonGroup);

		$reveal->add($form);

		$reveal->loadUrlOnOpen('/Survey/filter/' . $surveyQuestion->surveyQuestionId, $container->getId());

		return $opener;
		}

	private function getStatsReveal(\App\Record\SurveyQuestion $surveyQuestion) : \PHPFUI\HTML5Element
		{
		$opener = new \PHPFUI\FAIcon('fas', 'square-poll-horizontal');
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$container = new \PHPFUI\HTML5Element('div');
		$reveal->add($container);
		$reveal->add($reveal->getCloseButton());
		$reveal->loadUrlOnOpen('/Survey/quickStats/' . $surveyQuestion->surveyQuestionId, $container->getId());

		return $opener;
		}
	}
