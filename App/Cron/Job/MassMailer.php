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
		$mailItemTable = new \App\Table\MailItem();

		foreach ($mailItemTable->getRecordCursor() as $mailItem)
			{
			if ($mailItem->paused)  // we are paused
				{
echo "paused\n";
				continue;
				}
			$mail = new \App\Tools\EMail();
echo "make email\n";
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
echo "attach {$mailAttachment->prettyName	}\n";
			}
			$sent = 0;

			foreach ($mailItem->MailPieceChildren as $mailPiece)
				{
echo "$sent sent\n":
				$mail->setBody(\str_replace('~unsubscribe~', 'unsubscribe/' . $mailPiece->memberId . '/' . $mailPiece->email, (string)$mailItem->body));
				$mail->setTo($mailPiece->email, $mailPiece->name);

				if ($error = $mail->send())
					{
echo $error."\n";
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

				if ($this->controller->timedOut())
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
