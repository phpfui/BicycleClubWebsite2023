<?php

namespace App\Cron\EMailProcessors;

include PROJECT_ROOT . '/DI\functions.php';

class System
	{
	public function process(\ZBateson\MailMimeParser\Message $message) : bool
		{
		$tempFiles = [];
		$to = $message->getHeader('To');
		$cc = $message->getHeader('Cc');
		$found = false;
		$email = new \App\Tools\EMail();
		$fromBox = 'webmaster';

		$systemEmailTable = new \App\Table\SystemEmail();

		foreach ($systemEmailTable->getRecordCursor() as $system)
			{
			$emailAddress = $system->mailbox . '@' . \emailServerName();

			if (($to && $to->hasAddress($emailAddress)) || ($cc && $cc->hasAddress($emailAddress)))
				{
				$fromBox = $system->mailbox;
				$email->addTo($system->email, $system->name);
				$found = true;
				}
			}

		if (! $found)
			{
			return false;
			}
		$fromEmail = \App\Model\Member::cleanEmail($message->getHeaderValue('from'));
		$fromName = $message->getHeader('from')->getPersonName();
		$email->setFrom($fromBox . '@' . \emailServerName(), $fromName);
		$email->setReplyTo($fromEmail, $fromName);
		$text = $message->getTextContent() ?? '';
		$html = \App\Tools\TextHelper::htmlentities($message->getHtmlContent() ?? '');

		if (! \strlen($text) && \strlen($html))
			{
			$text = \Soundasleep\Html2Text::convert($html, ['drop_links' => 'href', 'ignore_errors' => true]);
			}

		if (\strlen($html))
			{
			$email->setBody($html);
			$email->setHtml();
			}
		else
			{
			$email->setBody($text);
			}

		foreach ($message->getAllAttachmentParts() as $mimePart)
			{
			$attachmentHeader = $mimePart->getHeader('Content-Disposition');

			if ($attachmentHeader && (\strpos((string)$attachmentHeader, 'attachment;') || \strpos((string)$attachmentHeader, 'inline;')))
				{
				$parts = [];

				foreach (\explode(';', (string)$attachmentHeader) as $part)
					{
					$sections = \explode('=', \str_replace('"', '', \trim($part)));

					if (\count($sections) > 1)
						{
						$parts[$sections[0]] = $sections[1];
						}
					}

				$fileName = $parts['filename'] ?? 'unknown';

				$fileName = \str_replace(' ', '_', $fileName);
				$fileName = \preg_replace('/[^a-zA-Z0-9\.\-\_()]/', '', $fileName);
				$extIndex = \strrpos($fileName, '.');
				$ext = '';

				if ($extIndex++)
					{
					$length = \strlen($fileName);

					for ($index = $extIndex; $index < $length; ++$index)
						{
						$chr = $fileName[$index];

						if (! \ctype_alnum($chr))
							{
							break;
							}
						$ext .= $chr;
						}
					$fileName = \substr($fileName, 0, $extIndex) . $ext;
					}

				// for some reason TempFile will not work here
				$mimePart->saveContent($fileName);
				$email->addAttachment($fileName, $fileName);
				$tempFiles[] = $fileName;
				}
			}
		$subject = $message->getHeaderValue('subject') ?? '';

		if ($subject)
			{
			$email->setSubject($subject);
			$email->send();
			}

		foreach ($tempFiles as $file)
			{
			\App\Tools\File::unlink($file);
			}

		return true;
		}
	}
