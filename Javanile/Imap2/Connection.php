<?php

/*
 * This file is part of the PHP IMAP2 package.
 *
 * (c) Francesco Bianco <bianco@javanile.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Javanile\Imap2;

use Javanile\Imap2\Roundcube\ImapClient;

class Connection
{
	protected ImapClient $client;

	protected $connected;

	protected string $currentMailbox;

	protected string $host;

	protected int $port;

	protected array $registry;

	protected string $sslMode;

	public function __construct(protected $mailbox, protected string $user, protected string $password, protected int $flags = 0, protected int $retries = 0, protected array $options = [])
	{
		$this->openMailbox($mailbox);

		$this->client = new ImapClient();
	}

	public static function close(Connection $imap, int $flags = 0)
	{
		$client = $imap->getClient();

		if ($client->close()) {
			return true;
		}

		$client->closeConnection();

		return true;
	}

	public function getClient()
	{
		return $this->client;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function getLastError()
	{
		$client = $this->getClient();

		return $client->error;
	}

	public function getMailbox()
	{
		return $this->mailbox;
	}

	public function getMailboxName()
	{
		return $this->currentMailbox;
	}

	public function getRegistryValue($space, $item, $key)
	{
		if (isset($this->registry[$space][$item][$key])) {
			return $this->registry[$space][$item][$key];
		}

		return false;
	}

	public function isConnected()
	{
		return (bool)($this->connected);
	}

	public static function isValid(Connection $imap)
	{
		return $imap->isConnected();
	}

	/**
	 * Open an IMAP stream to a mailbox.
	 */
	public static function open(string $mailbox, string $user, string $password, int $flags = 0, int $retries = 0, array $options = []) : Connection | false
	{
		$connection = new Connection($mailbox, $user, $password, $flags, $retries, $options);

		$success = $connection->connect();

		if (empty($success)) {
			Errors::appendErrorCanNotOpen($connection->getMailbox(), $connection->getLastError());

			\trigger_error(Errors::couldNotOpenStream($connection->getMailbox(), \debug_backtrace(), 1), E_USER_WARNING);

			return false;
		}

		return $connection;
	}

	public function openMailbox(string $mailbox) : void
	{
		$this->mailbox = $mailbox;

		$mailboxParts = Functions::parseMailboxString($mailbox);

		$this->host = Functions::getHostFromMailbox($mailboxParts);
		$this->port = @$mailboxParts['port'];
		$this->sslMode = Functions::getSslModeFromMailbox($mailboxParts);
		$this->currentMailbox = $mailboxParts['mailbox'];
	}

	public static function ping(Connection $imap) : ?bool
	{
		$client = $imap->getClient();
		//$client->setDebug(true);
		$status = $client->status($imap->getMailboxName(), ['UIDNEXT']);

		return isset($status['UIDNEXT']) && $status['UIDNEXT'] > 0;
	}

	public static function reopen(Connection $imap, string $mailbox, int $flags = 0, int $retries = 0) : ?bool
	{
		$imap->openMailbox($mailbox);

		$success = $imap->connect();

		if (empty($success)) {
			\trigger_error('imap2_reopen(): Couldn\'t re-open stream', E_USER_WARNING);

			return false;
		}

		$imap->selectMailbox();

		return true;
	}

	public function selectMailbox() : void
	{
		$success = $this->client->select($this->currentMailbox);

		if (empty($success)) {
			$this->rewriteMailbox('<no_mailbox>');
		}
	}

	public function setRegistryValue($space, $item, $key, $value) : void
	{
		if (empty($this->registry)) {
			$this->registry = [];
		}

		if (empty($this->registry[$space])) {
			$this->registry[$space] = [];
		}

		if (empty($this->registry[$space][$item])) {
			$this->registry[$space][$item] = [];
		}

		$this->registry[$space][$item][$key] = $value;
	}

	protected function connect() : bool|static
	{
		$this->connected = false;
		$client = $this->getClient();
		//$client->setDebug(true);

		$success = $client->connect($this->host, $this->user, $this->password, [
			'port' => $this->port,
			'ssl_mode' => $this->sslMode,
			'auth_type' => $this->flags & OP_XOAUTH2 ? 'XOAUTH2' : 'IMAP',
			'timeout' => -1,
			'force_caps' => false,
		]);

		if (empty($success)) {
			return false;
		}

		if (empty($this->currentMailbox)) {
			$mailboxes = $this->client->listMailboxes('', '*');

			if (false === $mailboxes) {
				return false;
			}

			if (\in_array('INBOX', $mailboxes)) {
				$this->currentMailbox = 'INBOX';
				$this->mailbox .= 'INBOX';
			}
		}

		$this->rewriteMailbox();

		$this->connected = true;

		return $this;
	}

	protected function rewriteMailbox(?string $forceMailbox = null) : void
	{
		$mailboxParts = Functions::parseMailboxString($this->mailbox);

		$params = [];

		$params[] = 'imap';

		if ('ssl' == $this->sslMode) {
			$params[] = 'notls';
			$params[] = 'ssl';
		}
		$params[] = 'user="' . $this->user . '"';

		$mailboxName = $forceMailbox ?: $mailboxParts['mailbox'];

		$updatedMailbox = '{' . $mailboxParts['host'] . ':' . $mailboxParts['port'] . '/' . \implode('/', $params) . '}' . $mailboxName;

		$this->mailbox = $updatedMailbox;
	}
}
