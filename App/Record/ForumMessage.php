<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ForumAttachment> $ForumAttachmentChildren
 * @property \App\Record\Member $lastEditor
 */
class ForumMessage extends \App\Record\Definition\ForumMessage
	{
	protected static array $virtualFields = [
		'ForumAttachmentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ForumAttachment::class],
		'lastEditor' => [\PHPFUI\ORM\RelatedRecord::class, \App\Record\Member::class],
	];

	private static ?\App\Model\ForumAttachmentFiles $fileModel = null;

	public function clean() : static
		{
		$this->htmlMessage = \App\Tools\TextHelper::cleanUserHtml($this->htmlMessage);

		return $this;
		}

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

	public function update() : bool
		{
		$this->lastEdited = \date('Y-m-d H:i:s');
		$this->lastEditorId = \App\Model\Session::signedInMemberId();

		return parent::update();
		}
	}
