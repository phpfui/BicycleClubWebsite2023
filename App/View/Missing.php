<?php

namespace App\View;

class Missing extends \App\View\Page implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		// @phpstan-ignore-next-line
		parent::__construct($controller);
		$this->setPageName('Missing!');
		$this->setPublic();
		$output = '';

		foreach ($controller->getErrors() as $key => $value)
			{
			if (\is_numeric($key))
				{
				$output .= "<b>{$value}</b><br>";
				}
			else
				{
				$output .= "<b>{$key}:</b> {$value}<br>";
				}
			}

		if (! empty($_SERVER['HTTP_REFERER']))
			{
			$output .= "<br>HTTP_REFERER: {$_SERVER['HTTP_REFERER']}<p>";
			}
		$message = $_SERVER['REQUEST_URI'] . ' is MISSING!';

		$email = new \App\Tools\EMail();
		$email->setHtml();
		$email->setSubject('Missing errror on ' . $_SERVER['SERVER_NAME']);
		$webMaster = $this->settingTable->value('webMaster');
		$email->setFrom($webMaster, 'Web Master');
		$email->setTo($webMaster, 'Web Master');

		if (isset($_POST['message']))
			{
			$this->addPageContent(new \PHPFUI\Header('Web Master Notified'));
			$this->addPageContent('Thanks for your feedback.');
			$message . '<p>' . \str_replace("\n", '<br>', (string)$_POST['message']) . "<br><br>{$output}";
			$email->setBody($message);
			$email->send();
			$this->redirect('/', '', 2);
			}
		else
			{
			if (\strpos($output, 'Exception:'))
				{
				$body = $message . '<p>' . $output;
				$email->setBody($body);
				$email->send();
				}
			\http_response_code(404);
			$this->addPageContent(new \PHPFUI\Header($message));
			$this->addPageContent('<img style="margin:0 auto;display:block;" src="/images/missing.jpg"/>');
			$this->addPageContent("<br><p><strong>Opps!</strong> I swear this was just here, but I don't think it was locked up correctly.");
			$this->addPageContent("<p>Anyway, look around and see if you can find it.  If not, contact the web master with your question.  And we'll lock it up better next time.<p>");
			$webmasterButton = new \PHPFUI\Button('Report this missing link to the web master');
			$modal = new \PHPFUI\Reveal($this, $webmasterButton);
			$emailButton = new \PHPFUI\Submit('Email Web Master');
			$form = new \PHPFUI\Form($this);
			$form->setAreYouSure(false);
			$fieldSet = new \PHPFUI\FieldSet($_SERVER['REQUEST_URI'] . ' was not found');
			$name = new \PHPFUI\Input\Text('name', 'Your Name');
			$name->setRequired();
			$fieldSet->add($name);
			$email = new \PHPFUI\Input\Email('email', 'Your email address');
			$email->setToolTip('So we can contact you in case we can help you find what you are looking for');
			$email->setRequired();
			$fieldSet->add($email);
			$textArea = new \PHPFUI\Input\TextArea('message', 'Message to the Web Master');
			$textArea->setToolTip('Like what you were looking for in the first place');
			$textArea->setRequired();
			$fieldSet->add($textArea);
			$form->add($fieldSet);
			$form->add($emailButton);
			$modal->add($form);
			$row = new \PHPFUI\GridX();
			$row->add($webmasterButton);
			$this->addPageContent($row);
			$nerdButton = new \PHPFUI\Button("I'm a nerd, show me the details");
			$modal = new \PHPFUI\Reveal($this, $nerdButton);
			$modal->add(new \PHPFUI\Header('So you really understand this?', 5));
			$modal->add($output);
			$modal->add('<hr>');
			$modal->add(new \PHPFUI\Cancel());
			$row = new \PHPFUI\GridX();
			$row->add($nerdButton);
			$this->addPageContent($row);
			}

		if ($this->settingTable->value('ReportMissingURLs'))
			{
			$errorReporter = new \App\Model\Errors();
			$message = \implode("\n", [$message, $_SERVER['HTTP_USER_AGENT'] ?? 'No HTTP_USER_AGENT', $_SERVER['HTTP_REFERER'] ?? 'No HTTP_REFERER']);
			$errorReporter->sendText($message);
			}
		}
	}
