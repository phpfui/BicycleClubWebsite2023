<?php

namespace App\Cron\Job;

class MassMailer extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send any queued emails waiting to be sent.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$SMTPSettings = new \App\Model\SettingsSaver('SMTP');
		$values = $SMTPSettings->getValues();
		$limit = (int)($values['SMTPLimit'] ?? 0);
		$totalSent = 0;

		$mailItemTable = new \App\Table\MailItem();

		foreach ($mailItemTable->getRecordCursor() as $mailItem)
			{
			if ($mailItem->paused)  // we are paused
				{
				continue;
				}
			$mail = new \App\Tools\EMail();

			if (! empty($mailItem->domain))
				{
				$mail->setDomain($mailItem->domain);
				}
			$sender = $mailItem->member;
			$email = $mailItem->fromEmail ?? $sender->email ?? 'webmaster';
			$name = $mailItem->fromName ?? $sender->fromName ?? 'Web Master';
			$mail->setFrom($email, $name);
			$mail->setSubject($mailItem->title);
			$mail->setHtml($mailItem->html ?? false);

			if ($mailItem->replyTo)
				{
				$mail->setReplyTo($mailItem->replyTo, $mailItem->replyToName);
				}
			elseif (! $sender->empty())
				{
				$mail->setReplyTo($sender->email, $sender->firstName . ' ' . $sender->lastName);
				}

			$tempFiles = [];

			foreach ($mailItem->MailAttachmentChildren as $mailAttachment)
				{
				if (\strlen((string)$mailAttachment->fileName) > 255)
					{
					$tempfile = new \App\Tools\TempFile();
					\file_put_contents($tempfile, $mailAttachment->fileName);
					$mailAttachment->fileName = "{$tempfile}";
					$tempFiles[] = $tempfile;
					}
				$mail->addAttachment($mailAttachment->fileName, $mailAttachment->prettyName);
			}
			$sent = 0;

			foreach ($mailItem->MailPieceChildren as $mailPiece)
				{
				$mail->setBody(\str_replace('~unsubscribe~', 'unsubscribe/' . $mailPiece->memberId . '/' . $mailPiece->email, (string)$mailItem->body));
				$mail->setTo($mailPiece->email, $mailPiece->name);

				if ($error = $mail->send())
					{
					if ('Could not instantiate mail function.' == $error)  // bad domain, just delete it
						{
						$this->controller->log_important("Bad email: {$mailPiece->email} <{$mailPiece->name}>");
						$mailPiece->delete();
						}
					else
						{
						$this->controller->log_important('Error sending email: ' . $error);
						}
					}
				else
					{
					$mailPiece->delete();
					}
				$sent += 1;

				if ($this->controller->timedOut() || ($limit && ++$totalSent >= $limit))
					{
					return;
					}
				}

			if (! $sent)  // nothing to send, we must be done, delete the mail
				{
				$mailItem->delete();
				}
			unset($mail);
			}
		}

	public function willRun() : bool
		{
		return true;
		}
	}
