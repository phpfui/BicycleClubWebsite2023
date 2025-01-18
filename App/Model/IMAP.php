<?php

namespace App\Model;

include PROJECT_ROOT . '/Javanile/bootstrap.php';

class IMAP implements \Countable
	{
	private $mbox = null; // @phpstan-ignore missingType.property

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		$server = $this->getConnectString();

		if ($server)
			{
			$this->mbox = @\imap_open($server, $this->settingTable->value('IMAPMailBox'), $this->settingTable->value('IMAPPassword'));
			}
		}

	public function __destruct()
		{
		if (null !== $this->mbox)
			{
			$errors = $this->getErrors();

			if ($errors)
				{
				\App\Tools\Logger::get()->debug($errors);
				}
			@\imap_close($this->mbox, CL_EXPUNGE);
			}
		}

	public function count() : int
		{
		if (null === $this->mbox)
			{
			return 0;
			}

		return @\imap_num_msg($this->mbox);
		}

	public function delete(string $messageNumbers = '0') : self
		{
		if (null !== $this->mbox)
			{
			@\imap_delete($this->mbox, $messageNumbers);
			}

		return $this;
		}

	public function getConnectString() : string
		{
		$server = $this->settingTable->value('IMAPServer');

		if (! $server)
			{
			return $server;
			}
		$connection = '{' . $server;
		$port = $this->settingTable->value('IMAPPort');

		if ($port)
			{
			$connection .= ':' . $port;
			}
		$encryption = $this->settingTable->value('IMAPEncryption');

		if ($encryption)
			{
			$connection .= '/' . $encryption;
			}
		$connection .= '}' . $this->settingTable->value('IMAPFolder');

		return $connection;
		}

	/**
	 * @return array<string>
	 */
	public function getErrors() : array
		{
		return \imap_errors() ?: [];
		}

	public function saveBodyToFile(string $fileName, int $messageNumber) : void
		{
		@\imap_savebody($this->mbox, $fileName, $messageNumber);
		}

	public function valid() : bool
		{
		return null !== $this->mbox;
		}
	}
