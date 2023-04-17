<?php

namespace App\WWW;

class System extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function auditTrail() : void
		{
		if ($this->page->addHeader('Audit Trail'))
			{
			$view = new \App\View\System\AuditTrail($this->page);
			$this->page->addPageContent($view->getTrail());
			}
		}

	public function cron() : void
		{
		if ($this->page->addHeader('Cron Jobs'))
			{
			$cronView = new \App\View\System\Cron($this->page);
			$this->page->addPageContent($cronView->list());
			}
		}

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

	public function favIcon() : void
		{
		if ($this->page->addHeader('Set FavIcon'))
			{
			$view = new \App\View\System\FavIcon($this->page);
			$this->page->addPageContent($view->editSettings());
			}
		}

	public function home() : void
		{
		if ($this->page->addHeader('System'))
			{
			$this->page->landingPage();
			}
		}

	public function importSQL() : void
		{
		if ($this->page->addHeader('Import SQL'))
			{
			$view = new \App\View\System\Import($this->page);
			$this->page->addPageContent($view->SQL());
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

	public function license() : void
		{
		if ($this->page->addHeader('License'))
			{
			$pre = new \PHPFUI\HTML5Element('pre');
			$pre->add(\file_get_contents(PROJECT_ROOT . '/License.md'));
			$this->page->addPageContent($pre);
			}
		}

	public function migrations() : void
		{
		if (\PHPFUI\Session::checkCSRF() && isset($_GET['migration']))
			{
			$model = new \PHPFUI\ORM\Migrator();
			$model->migrateTo((int)$_GET['migration']);
			$errors = $model->getErrors();

			if ($errors)
				{
				\App\Model\Session::setFlash('alert', $errors);
				}
			else
				{
				\App\Model\Session::setFlash('success', $model->getStatus());
				}

			$this->page->redirect();
			}
		elseif ($this->page->addHeader('Migrations'))
			{
			$view = new \App\View\System\Migration($this->page);
			$this->page->addPageContent($view->list());
			}
		}

	public function permission(string $reload = '') : void
		{
		if ($this->page->addHeader('Permission Reloader'))
			{
			$baseUri = '/System/permission';
			$reloadNumber = \App\Model\Session::getFlash('reload');

			if ($reload && $reloadNumber == $reload)
				{
				$this->page->getPermissions()->generatePermissionLoader();
				\App\Model\Session::setFlash('success', 'Permissions Regerated');
				$this->page->redirect($baseUri);
				}
			else
				{
				$callout = new \PHPFUI\Callout('alert');
				$callout->add('Sometimes the permissions file needs to be reloaded, like after a database restore or other permissions database work. It may result in currently logged in users to have to log out and back in to see the results.');
				$this->page->addPageContent($callout);
				$random = \random_int(0, \mt_getrandmax());
				\App\Model\Session::setFlash('reload', $random);
				$button = new \PHPFUI\Button('Reload Permission File', $baseUri . '/' . $random);
				$button->addClass('alert');
				$button->setConfirm('Reloading the permission file may cause users to need to log in again.  Are you sure?');
				$this->page->addPageContent($button);
				}
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

//	public function recaptchaTest() : void
//		{
//		if ($this->page->addHeader('Recaptcha Test'))
//			{
//			$form = new \PHPFUI\Form($this->page);
//			$form->add(new \PHPFUI\Input\Text('test', 'Test Text'));
//			$settingTable = new \App\Table\Setting();
//			$siteKey = $settingTable->value('ReCAPTCHAPublicKeyV3');
//			$secretKey = $settingTable->value('ReCAPTCHAPrivateKeyV3');
//			$submit = new \PHPFUI\Submit();
//			$recaptcha = new \PHPFUI\ReCAPTCHAv3($form, $submit, $siteKey, $secretKey, $_POST);
//
//			if (\App\Model\Session::checkCSRF() && ! empty($_POST))
//				{
//				if ($recaptcha->isValid())
//					{
//					\App\Model\Session::setFlash('success', 'ReCAPTCHA validated with ' . \print_r($recaptcha->getResults(), true));
//					}
//				else
//					{
//					\App\Model\Session::setFlash('alert', 'ReCAPTCHA error with ' . \print_r($recaptcha->getResults(), true));
//					}
//				}
//			$form->add(new \PHPFUI\Debug($_POST, \print_r($recaptcha->getResults(), true)));
//			$form->add($submit);
//			$fieldSet = new \PHPFUI\FieldSet('Test ReCAPTCHA here');
//			$fieldSet->add($form);
//			$this->page->addPageContent($fieldSet);
//			}
//		}

	public function redirects() : void
		{
		if ($this->page->addHeader('Redirects'))
			{
			$this->page->addPageContent(new \App\View\System\Redirects($this->page));
			}
		}

	public function releases() : void
		{
		$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);
		$deployer = new \App\Model\Deploy($repo);

		if (\PHPFUI\Session::checkCSRF() && isset($_GET['sha1']))
			{
			$deployer->deployTarget($_GET['sha1']);
			$this->page->redirect();

			return;
			}

		if ($this->page->addHeader('Releases'))
			{
			$view = new \App\View\System\Releases($this->page, $repo);
			$this->page->addPageContent($view->list($deployer->getReleaseTags()));
			}
		}

	public function releaseNotes() : void
		{
		if ($this->page->addHeader('Release Notes'))
			{
			$releaseNotes = new \App\View\System\ReleaseNotes();

			if ($releaseNotes->count())
				{
				$this->page->addPageContent($releaseNotes->show());
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('No Release Notes Found'));
				}
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

	public function testText() : void
		{
		if ($this->page->addHeader('Test Texting'))
			{
			$form = new \PHPFUI\Form($this->page);
			$member = \App\Model\Session::signedInMemberRecord();
			$form->add(new \PHPFUI\Input\Tel($this->page, 'From', 'From Phone Number', $member->cellPhone));
			$form->add(new \PHPFUI\Input\TextArea('Body', 'Text Body'));
			$form->setAttribute('action', '/SMS/receive');
			$submit = new \PHPFUI\Submit('Text');
			$form->add($submit);
			$this->page->addPageContent($form);
			}
		}

	public function versions(string $origin = '', string $branch = '') : void
		{
		$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);

		if (\PHPFUI\Session::checkCSRF() && isset($_GET['sha1']))
			{
			$deployer = new \App\Model\Deploy($repo);
			$deployer->deployTarget($_GET['sha1']);
			$this->page->redirect();
			}
		elseif ($this->page->addHeader('Versions'))
			{
			if (empty($branch))
				{
				$branch = $origin;
				}
			elseif ($origin)
				{
				$branch = $origin . '/' . $branch;
				}
//			$repo->run('tag', ['-d', '$(git tag)']);
			$repo->run('fetch');
			$view = new \App\View\System\Versions($this->page, $repo);
			$this->page->addPageContent($view->list($branch));
			}
		}
	}
