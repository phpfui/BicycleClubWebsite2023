<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ForumAttachment> $ForumAttachmentChildren
 */
class ForumMessage extends \App\Record\Definition\ForumMessage
	{
	protected static array $virtualFields = [
		'ForumAttachmentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ForumAttachment::class],
	];

	private static ?\App\Model\ForumAttachmentFiles $fileModel = null;

	public function delete() : bool
		{
		$condition = new \PHPFUI\ORM\Condition('forumMessageId', $this->forumMessageId);

		$model = self::$fileModel ?: self::$fileModel = new \App\Model\ForumAttachmentFiles();

		foreach ($this->ForumAttachmentChildren as $attachment)
			{
			$model->delete((string)$attachment->forumAttachmentId);
			}
		$forumAttachmentTable = new \App\Table\ForumAttachment();
		$forumAttachmentTable->setWhere($condition);
		$forumAttachmentTable->delete();

		return parent::delete();
		}
	}
