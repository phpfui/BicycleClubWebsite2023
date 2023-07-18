<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\MailAttachment> $MailAttachmentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\MailPiece> $MailPieceChildren
 */
class MailItem extends \App\Record\Definition\MailItem
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'MailPieceChildren' => [\PHPFUI\ORM\Children::class, \App\Table\MailPiece::class],
		'MailAttachmentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\MailAttachment::class],
	];
	}
