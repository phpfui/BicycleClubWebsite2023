<?php

namespace App\View\System;

class Cron
	{
	private readonly \App\Cron\Controller $controller;

	private array $jobs;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->controller = new \App\Cron\Controller(5);
		$cron = new \App\Cron\Cron($this->controller);
		$this->jobs = $cron->getAllJobs();
		$this->processRequest();
		}

	public function list() : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->setHeaders(['JobName' => 'Job Name',
			'description' => 'Description',
			'runnow' => 'Run Now',
			'disable' => 'Disabled', ]);

		$server = $this->page->getSchemeHost();
		$runNow = 'function runNow(name){if(confirm("Are you sure you want to run the "+name+" job now?")){$.get("' . $server . '/cron.php?runnow="+name);}}';
		$this->page->addJavaScript($runNow);
		$toggle = "\n" . 'function toggleDisabled(name){$.post("' . $this->page->getBaseURL() . '",{toggle:name,csrf:' . \App\Model\Session::csrf('"') . '});}';
		$this->page->addJavaScript($toggle);

		foreach ($this->jobs as $job)
			{
			$row = [];
			$row['description'] = $job->getDescription();
			$row['JobName'] = $name = $job->getName();
			$icon = new \PHPFUI\FAIcon('fas', 'play', '#');
			$icon->getId();
			$icon->addAttribute('onclick', "runNow(\"{$name}\");");
			$row['runnow'] = $icon;
			$disable = new \PHPFUI\Input\CheckBoxBoolean($name, '', $job->isDisabled());
			$disable->addAttribute('onchange', "toggleDisabled(\"{$name}\");");
			$row['disable'] = $disable;
			$table->addRow($row);
			}

		return $table;
		}

	protected function processRequest() : void
		{

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['toggle'], $this->jobs[$_POST['toggle']]))
				{
				$this->controller->toggleDisabled($this->jobs[$_POST['toggle']]);
				$this->page->isDone();
				}
			}
		}
	}
