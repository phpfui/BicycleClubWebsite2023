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

class ImapHelpers
	{
	/**
	 * Convert a string contain a sequence of message id to and equivalent with uid.
	 */
	public static function idToUid(Connection $imap, $messageNums) : string
		{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNums, false, ['UID']);

		$uid = [];

		foreach ($messages as $message)
			{
			$uid[] = $message->uid;
			}

		return \implode(',', $uid);
		}

	/**
	 * Convert a string contain a sequence of uid(s) to an equivalent with id(s).
	 */
	public static function uidToId(Connection $imap, $messageUid) : string
		{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageUid, true, ['UID']);

		$id = [];

		foreach ($messages as $message)
			{
			$id[] = $message->id;
			}

		return \implode(',', $id);
		}
	}
