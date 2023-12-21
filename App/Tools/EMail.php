<?php

namespace App\Tools;

class EMail
	{
	/** @var array<string,string> */
	protected array $attachments = [];

	/** @var array<string,array<string,string>> */
	protected array $bcc = [];

	protected string $body = '';

	/** @var array<string,array<string,string>> */
	protected array $cc = [];

	protected string $fromEmail = '';

	/** @var array<string,mixed> */
	protected array $fromMember = [];

	protected string $fromName = '';

	protected bool $html = false;

	protected ?\App\Tools\Logger $logger = null;

	protected ?string $replyToEmail = null;

	protected ?string $replyToName = null;

	protected string $server;

	protected string $subject = '';

	/** @var array<string,array<string,string>> */
	protected array $to = [];

	protected bool $useSMTPServer = true;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(bool $logErrors = true)
		{
		if ($logErrors)
			{
			$this->logger = new \App\Tools\Logger();
			}
		$this->settingTable = new \App\Table\Setting();
		$this->server = \strtolower($_SERVER['SERVER_NAME'] ?? 'localhost.com');

		if ('www.' == \substr($this->server, 0, 4))
			{
			$this->server = \substr($this->server, 4);
			}

		if (! \str_contains($this->server, '.'))
			{
			$this->server .= '.localhost';
			}
		}

	public function addAttachment(string $fileName, string $prettyName = '') : static
		{
		if (\strlen($fileName) > 255) // attachment as binary string
			{
			if (empty($prettyName))
				{
				$prettyName = 'attachment';
				}
			}
		elseif (\file_exists($fileName))
			{
			if (empty($prettyName))
				{
				$prettyName = $fileName;
				}
			}
		$this->attachments[$prettyName] = $fileName;

		return $this;
		}

	public function addBCC(?string $email, ?string $name = '', int $memberId = 0) : EMail
		{
		return $this->add($this->bcc, $email, $name, $memberId);
		}

	/** @param array<string,mixed> $member */
	public function addBCCMember(array $member) : EMail
		{
		if (empty($member['memberId']))
			{
			$member['memberId'] = 0;
			}

		return $this->add($this->bcc, $member['email'] ?? '', ($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? ''), $member['memberId']);
		}

	public function addCC(?string $email, ?string $name = '', int $memberId = 0) : EMail
		{
		return $this->add($this->cc, $email, $name, $memberId);
		}

	/** @param array<string,mixed> $member */
	public function addCCMember(array $member) : EMail
		{
		if (empty($member['memberId']))
			{
			$member['memberId'] = 0;
			}

		return $this->add($this->cc, $member['email'], ($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? ''), $member['memberId']);
		}

	public function addTo(?string $email, ?string $name = '', int $memberId = 0) : EMail
		{
		return $this->add($this->to, $email, $name, $memberId);
		}

	/** @param array<string,mixed> $member */
	public function addToMember(array $member) : static
		{
		if (empty($member['email']))
			{
			return $this;
			}

		if (empty($member['memberId']))
			{
			$member['memberId'] = 0;
			}

		return $this->add($this->to, $member['email'], ($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? ''), $member['memberId']);
		}

	public function bulkSend() : static
		{
		// nothing to do
		if (empty($this->body))
			{
			return $this;
			}

		if (empty($this->fromMember['memberId']))
			{
			$this->fromMember['memberId'] = (int)$this->settingTable->value('MembershipChair');
			}

		if ($this->html)
			{
			$this->body = \App\Tools\TextHelper::cleanEmailHtml($this->body);
			}
		$mailItem = new \App\Record\MailItem();
		$mailItem->body = $this->body;
		$mailItem->memberId = $this->fromMember['memberId'];
		$mailItem->fromEmail = $this->fromEmail;
		$mailItem->fromName = $this->via();
		$mailItem->title = $this->subject;
		$mailItem->html = (int)$this->html;
		$mailItem->replyTo = $this->replyToEmail ?? $this->fromEmail;
		$mailItem->replyToName = $this->replyToName ?? $mailItem->fromName;
		$mailItem->domain = $this->server;

		if ($this->attachments)
			{
			foreach ($this->attachments as $prettyName => $fileName)
				{
				$mailAttachment = new \App\Record\MailAttachment();
				$mailAttachment->mailItem = $mailItem;
				$mailAttachment->prettyName = $prettyName;
				$mailAttachment->fileName = $fileName;
				$mailAttachment->insert();
				}
			}
		$to = \array_merge($this->to, $this->bcc, $this->cc);
		$mailPiece = new \App\Record\MailPiece();

		foreach ($to as $email => $record)
			{
			$mailPiece->setFrom($record);
			$mailPiece->mailPieceId = 0;
			$mailPiece->mailItem = $mailItem;
			$mailPiece->email = $email;
			$mailPiece->insert();
			}

		return $this;
		}

	public static function cleanEmail(?string $email) : string
		{
		if (! $email)
			{
			return '';
			}

		$email = \str_replace([' ', ')', '('], '.', $email);
		$email = \str_replace('.@', '@', $email);

		while (\strpos($email, '..'))
			{
			$email = \str_replace('..', '.', $email);
			}

		return $email;
		}

	/**
	 * send the email immediately
	 *
	 * @return string error returned, empty is success
	 */
	public function send() : string
		{
		// nothing to do
		if (empty($this->body) || empty($this->subject))
			{
			return '';
			}
		$SMTPSettings = new \App\Model\SettingsSaver('SMTP');
		$values = $SMTPSettings->getValues();

		$mail = new \App\Tools\MyMailer();
		$mail->isSMTP();                                      // Set mailer to use SMTP

		if (! $this->useSMTPServer || empty($values['SMTPHost']) || 'localhost' == ($_SERVER['HTTP_HOST'] ?? 'localhost') || '::1' == ($_SERVER['SERVER_ADDR'] ?? ''))
			{
			$mail->Host = 'localhost';
			$mail->Port = 25;                    // TCP port to connect to
			}
		else
			{
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Host = $values['SMTPHost'];                    // Specify main and backup SMTP servers
			$mail->Username = $values['SMTPUsername'];            // SMTP username
			$mail->Password = $values['SMTPPassword'];            // SMTP password
			$mail->SMTPSecure = $values['SMTPSecure'];            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = (int)$values['SMTPPort'];                    // TCP port to connect to
			}

		$email = $this->fromEmail;

		if ($this->fromMember)
			{
			$mail->FromName = \trim($this->fromMember['firstName'] . ' ' . $this->fromMember['lastName']);

			if (! $email)
				{
				$email = $this->fromMember['email'];
				}
			$mail->addReplyTo($this->fromMember['email'], $mail->FromName);
			}

		if (empty($email))
			{
			$email = 'webmaster';
			}

		if ($pos = \strpos((string)$email, '@'))
			{
			$email = \substr((string)$email, 0, $pos);
			}
		$email = self::cleanEmail($email);
		$mail->From = \App\Tools\TextHelper::unhtmlentities("{$email}@{$this->server}");
		$this->fromName = \App\Tools\TextHelper::unhtmlentities($this->fromName ? $this->via() : 'Web Master');
		$mail->FromName = $this->fromName;

		if ($this->replyToEmail)
			{
			$mail->clearReplyTos();
			$mail->addReplyTo($this->replyToEmail, $this->replyToName);
			}


		$firstTo = '';

		foreach ($this->to as $email => $record)
			{
			$mail->addAddress($email, $record['name']);

			if (! $firstTo)
				{
				$firstTo = $email;
				}
			}

		foreach ($this->cc as $email => $record)
			{
			$mail->addCC($email, $record['name']);

			if (! $firstTo)
				{
				$firstTo = $email;
				}
			}

		foreach ($this->bcc as $email => $record)
			{
			$mail->addBCC($email, $record['name']);

			if (! $firstTo)
				{
				$firstTo = $email;
				}
			}

		$tempFiles = [];

		foreach ($this->attachments as $prettyName => $filename)
			{
			if (\strlen((string)$filename) > 255)  // if a file blob
				{
				$tempfile = new \App\Tools\TempFile();
				\file_put_contents($tempfile, $filename);
				$filename = "{$tempfile}";
				$tempFiles[] = $tempfile;
				}
			$mail->addAttachment($filename, $prettyName);
			}
		$mail->Subject = $this->subject;

		if ($this->html)
			{
			$this->body = \App\Tools\TextHelper::cleanEmailHtml($this->body);
			$mail->AltBody = \Soundasleep\Html2Text::convert($this->body, ['drop_links' => 'href', 'ignore_errors' => true]);
			}
		else	// a normal text message, but change new lines into html
			{
			$mail->AltBody = $this->body;
			$this->body = \str_replace("\n", '<br>', $this->body);
			}

		$mail->Body = $this->body;
		$mail->isHTML($this->html);
		$error = '';

		if (! empty($values['SMTPLog']))
			{
			$message = "To:{$firstTo}/{$mail->Subject} From:{$mail->FromName}";
			$this->log($message, 'email logged');
			}

		$mail->send();

		if ($mail->isError())
			{
			$error = $mail->ErrorInfo;
			$this->log($error, 'email failed');
			$this->log($this->subject, 'Subject');
			$this->log($this->body, 'body');
			}

		return $error;
		}

	public function setBody(string $body) : static
		{
		$this->body = $body;

		return $this;
		}

	public function setDomain(string $domain) : static
		{
		$this->server = $domain;

		return $this;
		}

	public function setFrom(?string $from, ?string $name = '') : static
		{
		if (! $from)
			{
			return $this;
			}

		if (! \str_contains($from, '@'))
			{
			$from .= '@' . $this->server;
			}
		$from = static::cleanEmail($from);

		if (\filter_var($from, FILTER_VALIDATE_EMAIL))
			{
			$this->fromEmail = $from;
			$this->fromName = \trim($name);
			}

		return $this;
		}

	/** @param array<string,mixed> $member */
	public function setFromMember(array $member) : static
		{
		$this->fromMember = $member;
		$name = ($this->fromMember['firstName'] ?? '') . ' ' . ($this->fromMember['lastName'] ?? '');
		$this->setReplyTo($this->fromMember['email'] ?? '', $name);

		return $this->setFrom($this->fromMember['email'] ?? '', $name);
		}

	public function setHtml(bool $html = true) : static
		{
		$this->html = $html;

		return $this;
		}

	public function setReplyTo(?string $email, ?string $name = '') : static
		{
		if (\filter_var($email, FILTER_VALIDATE_EMAIL))
			{
			$this->replyToEmail = $email;
			$this->replyToName = \trim(\App\Tools\TextHelper::unhtmlentities($name));
			}

		return $this;
		}

	public function setSubject(string $subject) : static
		{
		$this->subject = \App\Tools\TextHelper::unhtmlentities($subject);

		return $this;
		}

	public function setTo(?string $email, ?string $name = '', int $memberId = 0) : EMail
		{
		$this->to = [];

		return $this->addTo($email, $name, $memberId);
		}

	/** @param array<string,mixed> $member */
	public function setToMember(array $member) : static
		{
		$this->to = [];

		return $this->addToMember($member);
		}

	public function useSMTPServer(bool $useSMTP = true) : static
		{
		$this->useSMTPServer = $useSMTP;

		return $this;
		}

	/**
	 * @param array<string,array<string,string>> &$list
	 */
	private function add(array &$list, ?string $email, ?string $name, int $memberId = 0) : static
		{
		$email = self::cleanEmail($email);

		if (\filter_var($email, FILTER_VALIDATE_EMAIL))
			{
			$data = ['name' => $name, 'memberId' => $memberId];

			$list[$email] = $data;
			}

		return $this;
		}

	private function log(string $var, string $message) : static
		{
		if ($this->logger)
			{
			$this->logger->debug($var, $message);
			}

		return $this;
		}

	private function via() : string
		{
		if (! \strpos($this->fromName, ' via '))
			{
			return $this->fromName . ' via ' . $this->settingTable->value('clubAbbrev');
			}

		return $this->fromName;
		}
	}
