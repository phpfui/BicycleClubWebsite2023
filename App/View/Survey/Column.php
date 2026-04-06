<?php

namespace App\View\Survey;

class Column
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function filter(\App\Record\SurveyQuestion $surveyQuestion) : \PHPFUI\FieldSet
		{
		$surveyFileModel = new \App\Model\SurveyFile();
		$reader = new \App\Tools\CSV\FileReader($surveyFileModel->get($surveyQuestion->surveyId));

		$table = new \PHPFUI\Table();
		$responses = [];

		foreach ($reader as $row)
			{
			$value = $row[$surveyQuestion->columnName];

				if ('' === $value)
					{
					$value = 'No Answer';
					}

				if (isset($responses[$value]))
					{
					++$responses[$value];
					}
				else
					{
					$responses[$value] = 1;
					}
				}
			$answers = $responses;

			\ksort($answers);
			\arsort($responses);

			$table = new \PHPFUI\Table();
			$headers = ['Response', 'Count', 'Replace With'];

			$table->setHeaders($headers);

			$index = 0;

			foreach ($responses as $name => $count)
				{
				$input = new \PHPFUI\Input\Select("replace[{$index}]");

				foreach ($answers as $value => $junk)
					{
					$input->addOption($value, $value, $name === $value);
					}
				$hidden = new \PHPFUI\Input\Hidden("original[{$index}]", $name);
				$table->addRow(['Response' => $name, 'Count' => $count, 'Replace With' => $input . $hidden]);
				++$index;
				}

			$fieldSet = new \PHPFUI\FieldSet("Remap values For <b>{$surveyQuestion->displayName}</b>");
			$fieldSet->add($table);
			$fieldSet->add(new \PHPFUI\Input\Hidden('surveyQuestionId', (string)$surveyQuestion->surveyQuestionId));

			return $fieldSet;
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
			$table->addCustomColumn('edit', static fn (array $survey) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'edit', '/Surveys/edit/' . $survey[$recordId]));
			}

		if ($this->page->isAuthorized('Edit Survey'))
			{
			$headers[] = 'del';
			$table->addCustomColumn('del', static fn (array $survey) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'trash-alt', '#')->addAttribute('onclick', $delete->execute([$recordId => $survey[$recordId]])));
			}

		$table->setHeaders($headers);

		$table->addCustomColumn('name', static fn (array $survey) : \PHPFUI\Link => new \PHPFUI\Link('/Surveys/show/' . $survey[$recordId], $survey['name'], false));

		$container = new \PHPFUI\Container();
		$container->add($table);

		if ($this->page->isAuthorized('Add Survey'))
			{
			$container->add(new \PHPFUI\Button('Add Survey', '/Surveys/edit/0')->addClass('success'));
			}

		return $container;
		}

	public function quickStats(\App\Record\SurveyQuestion $surveyQuestion) : string
		{
		$surveyFileModel = new \App\Model\SurveyFile();
		$crossTab = new \App\Record\SurveyCrossTab();
		$crossTab->percent = 1;
		$crossTab->rowSurveyQuestion = $surveyQuestion;
		$crossTab->rowName = $surveyQuestion->displayName;

		return $surveyFileModel->getCrossTabStats($crossTab);
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{

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

				default:

					$this->page->redirect();

				}
			}
		}
	}
