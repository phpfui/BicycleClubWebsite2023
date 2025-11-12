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
