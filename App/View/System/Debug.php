<?php

namespace App\View\System;

class Debug
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function Home() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$settingTable = new \App\Table\Setting();

		if (! empty($_POST))
			{
			$message = '';

			foreach ($_POST as $value => $name)
				{
				$valueNum = (int)$value;

				if (\strpos((string)$name, ' On'))
					{
					\App\Model\Session::setDebugging($valueNum | \App\Model\Session::getDebugging());
					$message = \str_replace('Turn ', 'Turned ', (string)$name);
					}
				elseif (\strpos((string)$name, ' Off'))
					{
					\App\Model\Session::setDebugging(~$valueNum & \App\Model\Session::getDebugging());
					$message = \str_replace('Turn ', 'Turned ', (string)$name);
					}
				elseif (\strpos((string)$name, 'Maintenance Mode'))
					{
					$settingTable->save('maintenanceMode', $valueNum);
					$message = \str_replace('ctivate ', 'ctivated ', (string)$name);
					}
				else
					{
					switch ($value)
						{
						case 'deleteErrors':
							$errors = new \App\Model\Errors();
							$errors->deleteAll();
							$message = 'Errors deleted';

							break;

						case 'sendErrors':
							$controller = new \App\Cron\Controller(5);
							$error = new \App\Cron\Job\PHPErrorReporter($controller);
							$error->run();
							$message = 'Errors sent';

							break;

						case 'MySQLError':
							$sql = 'select * from errorTableNotFound';
							\PHPFUI\ORM::execute($sql);
							$message = 'Executed: ' . $sql;

							break;

						case 'PHPWarning':
							// @phpstan-ignore-next-line
							++$array['generatedWarning'];
							$message = 'Generated PHP warning';

							break;

						case 'PHPError':
							$message = 'Generated PHP Error';
							// send before we crash
							\App\Model\Session::setFlash('success', $message);
							// @phpstan-ignore-next-line
							$error = new \Unknown\Error();

							break;
						}
					}
				}
			\App\Model\Session::setFlash('success', $message);
			$this->page->redirect();
			}
		else
			{
			$form->add(new \PHPFUI\SubHeader('PHP Memory Limit'));
			$memoryLimitButton = new \PHPFUI\Button('Show');
			$memoryLimitButton->addClass('info');
			$reveal = new \PHPFUI\Reveal($this->page, $memoryLimitButton);
			$fieldSet = new \PHPFUI\FieldSet('Computed PHP Memory');
			$div = new \PHPFUI\HTML5Element('div');
			$div->add(new \App\UI\Loading());
			$fieldSet->add($div);
			$reveal->loadUrlOnOpen('/System/memory', $div->getId());
			$reveal->add($fieldSet);
			$reveal->add($reveal->getCloseButton('Close'));
			$form->add($memoryLimitButton);

			$form->add($this->getDebugButton('Debug Bar', \App\Model\Session::DEBUG_BAR));
			$form->add($this->getDebugButton('Readable HTML', \App\Model\Session::DEBUG_HTML));

			$status = (int)$settingTable->value('maintenanceMode');
			$statusText = $status ? 'On' : 'Off';
			$form->add(new \PHPFUI\SubHeader("Maintenance Mode is {$statusText}"));
			$statusText = $status ? 'Deactivate' : 'Activate';
			$submit = new \PHPFUI\Submit("{$statusText} Maintenance Mode", (string)($status ? 0 : 1));
			$form->add($submit);

			$form->add('<hr>');
			$form->add(new \PHPFUI\SubHeader('Error Reporting'));
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$deleteButton = new \PHPFUI\Submit('Delete Error Files', 'deleteErrors');
			$deleteButton->addClass('alert');
			$sendButton = new \PHPFUI\Submit('Send Errors Now', 'sendErrors');
			$sendButton->addClass('');
			$mySqlErrorButton = new \PHPFUI\Submit('Generate MySQL Error', 'MySQLError');
			$mySqlErrorButton->addClass('warning');
			$warningButton = new \PHPFUI\Submit('Generate PHP Warning', 'PHPWarning');
			$warningButton->addClass('info');
			$phpErrorButton = new \PHPFUI\Submit('Generate PHP Error', 'PHPError');
			$phpErrorButton->addClass('secondary');
			$buttonGroup->add($deleteButton);
			$buttonGroup->add($sendButton);
			$buttonGroup->add($mySqlErrorButton);
			$buttonGroup->add($warningButton);
			$buttonGroup->add($phpErrorButton);

			$form->add($buttonGroup);

			$model = new \App\Model\Errors();
			$errors = $model->getErrors();

			if ($errors)
				{
				$form->add(new \PHPFUI\SubHeader('Current Errors'));
				$pre = new \PHPFUI\HTML5Element('pre');
				$pre->add(\implode('', $errors));
				$form->add($pre);
				}
			else
				{
				$form->add(new \PHPFUI\SubHeader('No Current Errors'));
				}
			}

		return $form;
		}

	private function getDebugButton(string $type, int $flag) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$status = \App\Model\Session::getDebugging();
		$statusText = $status & $flag ? 'On' : 'Off';
		$container->add(new \PHPFUI\SubHeader("{$type} is {$statusText}"));
		$statusText = $status & $flag ? 'Off' : 'On';
		$submit = new \PHPFUI\Submit("Turn {$type} {$statusText}", (string)$flag);
		$container->add($submit);

		return $container;
		}
	}
