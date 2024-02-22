<?php

namespace App\Model;

class FallbackEmailProcessor
  {
  /**
   * @return array<string>
   */
  public function getAddresses(?\ZBateson\MailMimeParser\Header\IHeader $header, string $url) : array
		{
		$addresses = [];

		if ($header)
			{
			foreach ($header->getParts() as $part)
				{
				$address = $part->getValue();

				if (false !== ($index = \stripos($address, '@' . $url)))
					{
					$addresses[] = \substr($address, 0, $index);
					}
				}
			}

		return $addresses;
		}

	/**
	 * @return array<string,string>
	 */
  public function getValidEmailAddresses() : array
		{
		$forumTable = new \App\Table\Forum();
		$valid = ['webmaster', ];

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			$valid[$forum->email] = $forum->email;
			}
		$systemEmailTable = new \App\Table\SystemEmail();

		foreach ($systemEmailTable->getRecordCursor() as $result)
			{
			$valid[$result->mailbox] = $result->mailbox;
			}

		return $valid;
		}

  public function process(\ZBateson\MailMimeParser\IMessage $message) : bool
		{
		$settingTable = new \App\Table\Setting();
		$abbrev = $settingTable->value('clubAbbrev');
		$url = \emailServerName();
		$from = \App\Model\Member::cleanEmail($message->getHeaderValue('from'));
		$title = $message->getHeaderValue('subject') ?? '';
		$badReplies = ['Auto-Reply', 'Auto Reply', 'Automatic Reply'];

		foreach ($badReplies as $reply)
			{
			if (\stristr($title, $reply))
				{
				return true;
				}
			}
		$member = new \App\Record\Member(['email' => $from]);

		if (! $member->loaded())
			{
			// Not a member, bail
			return true;
			}

		$addresses = $this->getAddresses($message->getHeader('To'), $url);
		$addresses += $this->getAddresses($message->getHeader('Cc'), $url);

		$blacklist = \explode("\n", $settingTable->value('BlackListedEmails'));

		foreach ($blacklist as $partial)
			{
			if (false !== \stripos($from, \trim($partial)))
				{
				return true;
				}
			}

		// Member, send a nice email
		$email = new \App\Tools\EMail();
		$email->setSubject("Invalid {$abbrev} email address");
		$email->addTo($from);
		$validAddresses = $this->getValidEmailAddresses();
		$invalidAddresses = [];

		foreach ($addresses as $address)
			{
			$address = \strtolower($address);

			if (! isset($validAddresses[$address]))
				{
				$invalidAddresses[] = $address;
				}
			}

		if ($invalidAddresses)
			{
			$body = "Your email recent email titled <b>{$title}</b> contains the following invalid email address:<p>";
			$ul = new \PHPFUI\UnorderedList();

			foreach ($invalidAddresses as $address)
				{
				$ul->addItem(new \PHPFUI\ListItem($address . '@' . $url));
				}
			$body .= $ul;
			$body .= '<br>You probably replied to the sender address. Please reply to the Reply To address.<br>Thank you.<br><br>' .
				'If you have additional questions, please email our ' . \PHPFUI\Link::email('webmaster@' . \emailServerName(), 'Web Master', 'Email issue');
			$email->setBody($body);
			$email->setHtml();
			$email->send();
			}

		return true;
		}
	}
