<?php

namespace App\WWW\System;

class Info extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function debug() : void
		{
		if ($this->page->addHeader('Debug Status'))
			{
			$view = new \App\View\System\Debug($this->page);
			$this->page->addPageContent($view->Home());
			}
		}

	public function docs() : void
		{
		if ($this->page->addHeader('PHP Documentation'))
			{
			$fileManager = new \PHPFUI\InstaDoc\FileManager();
			$namespaces = [
				'App',
				'BaconQrCode',
				'cebe',
				'DASPRiD',
				'DebugBar',
				'DeepCopy',
				'Endroid',
				'Flow',
				'Gitonomy',
				'GuzzleHttp',
				'Highlight',
				'ICalendarOrg',
				'Ifsnop',
				'Intervention',
				'League',
				'Maknz',
				'Mpdf',
				'PayPalCheckoutSdk',
				'PayPalHttp',
				'phpDocumentor',
				'PHPFUI',
				'PHPMailer',
				'Psr',
				'ReCaptcha',
				'RideWithGPS',
				'SparkPost',
				'Symfony',
				'Soundasleep',
				'Tinify',
				'Twilio',
				'voku',
				'ZBateson',
			];

			foreach ($namespaces as $namespace)
				{
				$fileManager->addNamespace($namespace, '../' . $namespace, true);
				}
			$fileManager->load();
			\PHPFUI\InstaDoc\ChildClasses::load(PROJECT_ROOT . '/ChildClasses.serial');
			$controller = new \PHPFUI\InstaDoc\Controller($fileManager);
			$controller->setHomeUrl('/');
			$controller->setPageTitle('PHP Documentation');
			$controller->setGitRoot(\getcwd() . '/../');

			$controller->getControllerPage()->addCSS('code{tab-size:2;-moz-tab-size:2}');

			foreach (\glob(PROJECT_ROOT . '/docs/*.md') as $file)
				{
				$controller->addHomePageMarkdown($file);
				}

			// just display docs, don't host in normal page
			echo $controller->display();

			exit;
			}
		}

	public function inputNormal() : void
		{
		if ($this->page->addHeader('Input Normal'))
			{
			$form = new \PHPFUI\Form($this->page);

			if (! empty($_REQUEST))
				{
				$debug = new \PHPFUI\Debug($_REQUEST);
				$callout = new \PHPFUI\Callout('info');
				$callout->add($debug);
				$form->add($callout);
				}
			$fieldSet = new \PHPFUI\FieldSet('Input Testing');
			$multiColumn = new \PHPFUI\MultiColumn();
			$multiColumn->add(new \PHPFUI\Input\Time($this->page, 'time', 'Time Android', $_REQUEST['time'] ?? '12:30 PM'));
			$multiColumn->add(new \PHPFUI\Input\TimeDigital($this->page, 'timeDigital', 'Time Digital', $_REQUEST['timeDigital'] ?? '4:45 PM'));
			$fieldSet->add($multiColumn);
			$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'date', 'Date', $_REQUEST['date'] ?? ''));
			$fieldSet->add(new \PHPFUI\Input\DateTime($this->page, 'string', 'Date Time', $_REQUEST['datetime'] ?? ''));
			$fieldSet->add(new \PHPFUI\Input\Number('number', 'Number', (float)($_REQUEST['number'] ?? '')));

			$form->add($fieldSet);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Test'));
			$backButon = new \PHPFUI\Button('Back', '/System');
			$backButon->addClass('hollow')->addClass('secondary');
			$buttonGroup->addButton($backButon);
			$form->add($buttonGroup);
			$form->add(\PHPFUI\Link::phone('1-914-361-9059', 'Call Web Master'));
			$this->page->addPageContent("{$form}");
			}
		}

	public function inputTest() : void
		{
		if ($this->page->addHeader('Input Test'))
			{
			$page = new \PHPFUI\VanillaPage();
			$form = new \PHPFUI\Form($this->page);

			if (! empty($_REQUEST))
				{
				$debug = new \PHPFUI\Debug($_REQUEST);
				$callout = new \PHPFUI\Callout('info');
				$callout->add($debug);
				$form->add($callout);
				}
			$fields = ['time', 'date', 'string', 'number'];
			$attributes = ['type', 'name', 'placeholder'];
			$fieldSet = new \PHPFUI\FieldSet('Input Testing');

			foreach ($fields as $field)
				{
				$input = new \PHPFUI\HTML5Element('input');

				foreach ($attributes as $attribute)
					{
					$input->addAttribute($attribute, $field);
					}

				if (isset($_REQUEST[$field]))
					{
					$input->addAttribute('value', $_REQUEST[$field]);
					}

				if ('time' == $field)
					{
					$input->addAttribute('step', (string)900);
					}
				$display = new \App\UI\Display(\ucwords($field), $input);
				$fieldSet->add($display);
				}

			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Test'));
			$form->add('<br>');
			$form->add(new \PHPFUI\Button('Back', '/System'));
			$form->add('<br>');
			$form->add(\PHPFUI\Link::phone('914-361-9059', 'Call Web Master'));
			$page->add($form);

			echo $page;

			exit;
			}
		}

	public function landingPage() : void
		{
		$this->page->landingPage('System Info');
		}

	public function license() : void
		{
		if ($this->page->addHeader('License'))
			{
			$pre = new \PHPFUI\HTML5Element('pre');
			$pre->add(\file_get_contents(PROJECT_ROOT . '/License.md'));
			$this->page->addPageContent($pre);
			}
		}

	public function pHPInfo() : void
		{
		if ($this->page->addHeader('PHP Info'))
			{
			\ob_start();
			\phpinfo();
			$info = \ob_get_contents();
			$body = '<body>';
			$index = \strpos($info, $body) + \strlen($body);
			$info = \substr($info, $index);
			$body = '</body>';
			$index = \strpos($info, $body);
			$info = \substr($info, 0, $index);
			$this->page->addPageContent($info);
			$this->page->addPageContent(\date('Y-m-d H:i:s'));
			\ob_end_clean();
			}
		}

	public function sessionInfo() : void
		{
		if ($this->page->addHeader('Session Info'))
			{
			$purgeAll = new \PHPFUI\Submit('Logout All Users', 'purgeAll');
			$form = new \PHPFUI\Form($this->page, $purgeAll);

			if ($form->isMyCallback())
				{
				\App\Tools\SessionManager::purgeOld(0);
				$this->page->setResponse('All Users Logged Out');
				}
			else
				{
				$form->add($purgeAll);
				$this->page->addPageContent($form);
				$this->page->addPageContent('<pre>');
				$this->page->addPageContent(\print_r($_SESSION, true));
				$this->page->addPageContent('</pre>');
				}
			}
		}
	}
