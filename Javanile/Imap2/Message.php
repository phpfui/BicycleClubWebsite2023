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

class Message
{
	public static function body(Connection $imap, int $messageNum, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);

		/**
		 * TODO
		 * The information on whether the fetch operation will be performed in PEEK mode should be obtained
		 * from the $flags parameter. The $flags parameter needs to be moved to where the fetch() function is called,
		 * and the PEEK mode needs to be decided whether the $flags variable contains the FT_PEEK value.
		 */
		$messages = $client->fetch($imap->getMailboxName(), $messageNum, $isUid, ['BODY.PEEK[TEXT]']);

		if ($isUid && \is_array($messages)) {
			$messages = Functions::keyBy('uid', $messages);
		}

		return $messages[$messageNum]->bodypart['TEXT'];
	}

	public static function bodyStruct(Connection $imap, int $messageNum, $section)
	{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNum, false, ['BODY.PEEK[' . $section . ']']);

		if ($section) {
			return $messages[$messageNum]->bodypart[$section];
		}

		return $messages[$messageNum]->body;
	}

	/**
	 * Clears flags on messages.
	 *
	 *
	 * @return false|string
	 */
	public static function clearFlagFull(Connection $imap, $sequence, $flag, $options = 0)
	{
		$client = $imap->getClient();

		if (! ($options & ST_UID)) {
			$messages = $client->fetch($imap->getMailboxName(), $sequence, false, ['UID']);

			$uid = [];

			foreach ($messages as $message) {
				$uid[] = $message->uid;
			}

			$sequence = \implode(',', $uid);
		}

		$client->unflag($imap->getMailboxName(), $sequence, \strtoupper(\substr($flag, 1)));

		return false;
	}

	public static function delete(Connection $imap, int $messageNums, int $flags = 0)
	{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNums, false, ['UID']);

		$uid = [];

		foreach ($messages as $message) {
			$uid[] = $message->uid;
		}

		$client->flag($imap->getMailboxName(), \implode(',', $uid), $client->flags['DELETED']);

		return true;
	}

	public static function expunge(Connection $imap)
	{
		return $imap->getClient()->expunge($imap->getMailboxName());
	}

	public static function fetchBody(Connection $imap, int $messageNum, $section, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);
		$messages = $client->fetch($imap->getMailboxName(), $messageNum, $isUid, ['BODY.PEEK[' . $section . ']']);

		if (empty($messages)) {
			\trigger_error(Errors::badMessageNumber(\debug_backtrace(), 1), E_USER_WARNING);

			return false;
		}

		if ($isUid && \is_array($messages)) {
			$messages = Functions::keyBy('uid', $messages);
		}

		if ($section) {
			return $messages[$messageNum]->bodypart[$section];
		}

		return $messages[$messageNum]->body;
	}

	public static function fetchHeader(Connection $imap, int $messageNum, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);

		$messages = $client->fetch($imap->getMailboxName(), $messageNum, $isUid, ['BODY.PEEK[HEADER]']);

		if (empty($messages)) {
			return false;
		}

		foreach ($messages as $message) {
			return $message->bodypart['HEADER'] ?? false;
		}
	}

	public static function fetchMime(Connection $imap, int $messageNum, $section, int $flags = 0)
	{
		if ($messageNum <= 0) {
			\trigger_error(Errors::badMessageNumber(\debug_backtrace(), 1), E_USER_WARNING);

			return false;
		}

		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);

		$sectionKey = $section . '.MIME';
		$messages = $client->fetch($imap->getMailboxName(), $messageNum, $isUid, ['BODY.PEEK[' . $sectionKey . ']']);

		if (empty($messages)) {
			return '';
		}

		if ($isUid && \is_array($messages)) {
			$messages = Functions::keyBy('uid', $messages);
		}

		if ($section && isset($messages[$messageNum]->bodypart[$sectionKey])) {
			return $messages[$messageNum]->bodypart[$sectionKey];
		}

		return $messages[$messageNum]->body;
	}

	public static function fetchOverview(Connection $imap, $sequence, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);
		$messages = $client->fetch($imap->getMailboxName(), $sequence, $isUid, [
			'BODY.PEEK[HEADER.FIELDS (SUBJECT FROM TO CC REPLYTO MESSAGEID DATE SIZE REFERENCES)]',
			'UID',
			'FLAGS',
			'INTERNALDATE',
			'RFC822.SIZE',
			'ENVELOPE',
			'RFC822.HEADER'
		]);


		if (false === $messages) {
			return [];
		}

		if ('*' != $sequence && \count($messages) < Functions::expectedNumberOfMessages($sequence)) {
			return [];
		}

		$overview = [];

		foreach ($messages as $message) {
			$messageEntry = (object)[
				'subject' => $message->envelope[1],
				'from' => Functions::writeAddressFromEnvelope($message->envelope[2]),
				'to' => $message->get('to'),
				'date' => $message->envelope[0],
				'message_id' => $message->envelope[9],
				'references' => $message->references,
				'in_reply_to' => $message->envelope[8],
				'size' => $message->size,
				'uid' => $message->uid,
				'msgno' => $message->id,
				'recent' => (int)($message->flags['RECENT'] ?? 0),
				'flagged' => (int)($message->flags['FLAGGED'] ?? 0),
				'answered' => (int)($message->flags['ANSWERED'] ?? 0),
				'deleted' => (int)($message->flags['DELETED'] ?? 0),
				'seen' => (int)($message->flags['SEEN'] ?? 0),
				'draft' => (int)($message->flags['DRAFT'] ?? 0),
				'udate' => \strtotime($message->internaldate),
			];

			if (empty($messageEntry->subject)) {
				$messageEntry->subject = null;
			}

			if (empty($messageEntry->references)) {
				$messageEntry->references = null;
			}

			if (empty($messageEntry->in_reply_to)) {
				$messageEntry->in_reply_to = null;
			}

			if (empty($messageEntry->to)) {
				$messageEntry->to = null;
			}

			$overview[] = $messageEntry;
		}

		return $overview;
	}

	public static function fetchStructure(Connection $imap, int $messageNum, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);

		$messages = $client->fetch($imap->getMailboxName(), $messageNum, $isUid, ['BODYSTRUCTURE']);

		if (empty($messages)) {
			return false;
		}

		foreach ($messages as $message) {
			return BodyStructure::fromMessage($message);
		}
	}

	public static function fetchUids(Connection $imap, string $sequence, int $flags = 0)
	{
		$client = $imap->getClient();

		$isUid = (bool)($flags & FT_UID);
		$messages = $client->fetch($imap->getMailboxName(), $sequence, $isUid, ['UID']);

		if ('*' != $sequence && \count($messages) < Functions::expectedNumberOfMessages($sequence)) {
			return false;
		}

		return $messages;
	}

	public static function headerInfo(Connection $imap, int $messageNum, int $fromLength = 0, int $subjectLength = 0, $defaultHost = null)
	{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNum, false, [
			'BODY.PEEK[HEADER.FIELDS (SUBJECT FROM TO CC REPLY-TO DATE SIZE REFERENCES)]',
			'ENVELOPE',
			'INTERNALDATE',
			'UID',
			'FLAGS',
			'RFC822.SIZE',
			'RFC822.HEADER'
		]);

		if (empty($messages)) {
			return false;
		}

		foreach ($messages as $message) {
			return HeaderInfo::fromMessage($message, $defaultHost);
		}
	}

	public static function headers(Connection $imap)
	{
		$client = $imap->getClient();

		$status = $client->status($imap->getMailboxName(), ['MESSAGES']);

		if (empty($status['MESSAGES'])) {
			return [];
		}

		$sequence = '1:' . (int)($status['MESSAGES']);
		$messages = $client->fetch($imap->getMailboxName(), $sequence, false, [
			'BODY.PEEK[HEADER.FIELDS (SUBJECT FROM TO CC REPLYTO MESSAGEID DATE SIZE REFERENCES)]',
			//'UID',
			'FLAGS',
			'INTERNALDATE',
			'RFC822.SIZE',
			//'ENVELOPE',
			'RFC822.HEADER'
		]);

		if (empty($messages)) {
			return [];
		}

		$headers = [];

		foreach ($messages as $message) {
			$from = ' ';

			if ('no_host' != $message->from) {
				$from = \imap_rfc822_parse_adrlist($message->from, 'no_host');
				$from = $from[0]->personal ?? $message->from;
			}

			$date = \explode(' ', $message->internaldate);
			$subject = empty($message->subject) ? ' ' : $message->subject;
			$unseen = empty($message->flags['SEEN']) ? 'U' : ' ';
			$flagged = empty($message->flags['FLAGGED']) ? ' ' : 'F';
			$answered = empty($message->flags['ANSWERED']) ? ' ' : 'A';
			$draft = empty($message->flags['DRAFT']) ? ' ' : 'D';
			$deleted = empty($message->flags['DELETED']) ? ' ' : 'X';

			$header = ' ' . $unseen . $flagged . $answered . $draft . $deleted . ' '
					. \str_pad($message->id, 3, ' ', STR_PAD_LEFT) . ')' . $date[0] . ' ' . \str_pad($from, 20, ' ') . ' '
					. \substr($subject, 0, 25) . ' (' . $message->size . ' chars)';

			$headers[] = $header;
		}

		return $headers;
	}

	public static function msgno(Connection $imap, $messageUid)
	{
		$client = $imap->getClient();

		$msgNo = ImapHelpers::uidToId($imap, $messageUid);

		return \is_numeric($msgNo) ? (int)$msgNo : $msgNo;
	}

	public static function saveBody(Connection $imap, $file, $messageNum, $section = '', $flags = 0)
	{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNum, false, ['BODY.PEEK[' . $section . ']']);

		$body = $section ? $messages[$messageNum]->bodypart[$section] : $messages[$messageNum]->body;

		return \file_put_contents($file, $body);
	}

	/**
	 * Returns an array of messages matching the given search criteria.
	 *
	 *
	 * @return array|false|mixed
	 */
	public static function search(Connection $imap, $criteria, int $flags = SE_FREE, string $charset = '')
	{
		$client = $imap->getClient();

		$result = $client->search($imap->getMailboxName(), $criteria, $flags & SE_UID);

		if (empty($result->count())) {
			return false;
		}

		$messages = $result->get();

		foreach ($messages as &$message) {
			$message = \is_numeric($message) ? (int)$message : $message;
		}

		return $messages;
	}

	/**
	 * Sets flags on messages.
	 *
	 *
	 * @return bool
	 */
	public static function setFlagFull(Connection $imap, $sequence, int $flag, $options = 0)
	{
		$client = $imap->getClient();

		if (! ($options & ST_UID)) {
			$messages = $client->fetch($imap->getMailboxName(), $sequence, false, ['UID']);

			$uid = [];

			foreach ($messages as $message) {
				$uid[] = $message->uid;
			}

			$sequence = \implode(',', $uid);
		}

		return $client->flag($imap->getMailboxName(), $sequence, \strtoupper(\substr($flag, 1)));
	}

	public static function sort(Connection $imap, $criteria, $reverse, int $flags = 0, $searchCriteria = null, ?string $charset = null)
	{
		$client = $imap->getClient();

		$result = $client->search($imap->getMailboxName(), $criteria, $flags & SE_UID);

		if (empty($result->count())) {
			return false;
		}

		$messages = $result->get();

		foreach ($messages as &$message) {
			$message = \is_numeric($message) ? (int)$message : $message;
		}

		return $messages;
	}

	public static function uid(Connection $imap, int $messageNum) : int
	{
		$uid = ImapHelpers::idToUid($imap, $messageNum);

		return \is_numeric($uid) ? (int)$uid : $uid;
	}

	public static function undelete(Connection $imap, $messageNums, $flags = 0)
	{
		$client = $imap->getClient();

		$messages = $client->fetch($imap->getMailboxName(), $messageNums, false, ['UID']);

		foreach ($messages as $message) {
			$client->unflag($imap->getMailboxName(), $message->uid, $client->flags['DELETED']);
		}

		return true;
	}
}
