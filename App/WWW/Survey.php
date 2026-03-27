<?php

namespace App\WWW;

class Survey extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Survey $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Survey($this->page);
		}

	public function crossTab(\App\Record\SurveyCrossTab $crossTab = new \App\Record\SurveyCrossTab()) : void
		{
		$view = new \App\View\Survey\CrossTab($this->page);

		$response = (string)$view->crossTab($crossTab);

		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}

	public function delete(\App\Record\Survey $survey = new \App\Record\Survey()) : void
		{
		if ($this->page->addHeader('Delete Survey'))
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Survey has been deleted.'));
			$survey->delete();
			}
		}

	public function download(\App\Record\Survey $survey = new \App\Record\Survey()) : void
		{
		if ($this->page->isAuthorized('Download Survey'))
			{
			$surveyFileModel = new \App\Model\SurveyFile();
			$surveyFileModel->download($survey->surveyId, '.csv', $survey->name . '.csv');

			exit;
			}
		}

	public function edit(\App\Record\Survey $survey = new \App\Record\Survey()) : void
		{
		$title = $survey->surveyId ? 'Edit' : 'Add';

		if ($this->page->addHeader($title . ' Survey'))
			{
			$this->page->addPageContent($this->view->edit($survey));
			}
		}

	public function editCrossTab(\App\Record\SurveyCrossTab $crossTab = new \App\Record\SurveyCrossTab()) : void
		{
		if ($this->page->addHeader('Edit CrossTabs'))
			{
			$view = new \App\View\Survey\CrossTab($this->page);

			$this->page->addPageContent($view->editCrossTab($crossTab));
			}
		}

	public function editCrossTabs(\App\Record\Survey $survey = new \App\Record\Survey()) : void
		{
		if ($this->page->addHeader('Edit CrossTabs'))
			{
			$view = new \App\View\Survey\CrossTab($this->page);

			$this->page->addPageContent($view->edit($survey));
			}
		}

	public function filter(\App\Record\SurveyQuestion $surveyQuestion) : void
		{
		$container = new \PHPFUI\Container();

		if ($this->page->isAuthorized('Edit Survey'))
			{
			$view = new \App\View\Survey\Column($this->page);
			$container->add($view->filter($surveyQuestion));
			}
		else
			{
			$container->add(new \PHPFUI\Header('Not Authorized'));
			}
		$this->page->setRawResponse(\json_encode("{$container}", JSON_PRETTY_PRINT));
		}

	public function list() : void
		{
		if ($this->page->addHeader('Surveys'))
			{
			$surveyTable = new \App\Table\Survey()->addOrderBy('uploaded', 'desc');
			$this->page->addPageContent($this->view->list($surveyTable));
			}
		}

	public function quickStats(\App\Record\SurveyQuestion $surveyQuestion = new \App\Record\SurveyQuestion()) : void
		{
		$view = new \App\View\Survey\Column($this->page);

		$response = (string)$view->quickStats($surveyQuestion);

		$this->page->setRawResponse(\json_encode($response, JSON_PRETTY_PRINT));
		}

	public function show(\App\Record\Survey $survey = new \App\Record\Survey()) : void
		{
		if ($this->page->addHeader('Survey Results'))
			{
			$this->page->addPageContent($this->view->show($survey));
			}
		}
	}
