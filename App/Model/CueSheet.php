<?php

namespace App\Model;

class CueSheet
	{
	private readonly \App\Table\CueSheetVersion $cueSheetVersionTable;

	private readonly \App\Model\CueSheetFiles $fileModel;

	public function __construct()
		{
		$this->cueSheetVersionTable = new \App\Table\CueSheetVersion();
		$this->fileModel = new \App\Model\CueSheetFiles();
		}

	public function approve(\App\Record\CueSheet $cuesheet, \App\View\CueSheet $view) : void
		{
		$cuesheet->pending = 0;

		if (empty($cuesheet->memberId))
			{
			$cuesheet->memberId = \App\Model\Session::signedInMemberId();
			}
		$cuesheet->update();
		$email = new \App\Model\Email('acceptCue', new \App\Model\Email\CueSheet($cuesheet));
		$email->setToMember($cuesheet->member->toArray());
		$email->setFromMember(\App\Model\Session::getSignedInMember());
		$email->send();
		}

	public function canDelete(\App\Record\CueSheet $cuesheet) : bool
		{
		if (\App\Table\Ride::getCueSheetRideCount($cuesheet))
			{
			return false;
			}

		$this->cueSheetVersionTable->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cuesheet->cueSheetId));

		return ! $this->cueSheetVersionTable->count();
		}

	public function delete(\App\Record\CueSheet $cuesheet, bool $overrideTest = false) : void
		{
		if ($overrideTest || $this->canDelete($cuesheet))
			{
			$cuesheet->delete();

			// may need to delete multiple versions from rejection
			$this->cueSheetVersionTable->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cuesheet->cueSheetId));

			foreach ($this->cueSheetVersionTable->getRecordCursor() as $version)
				{
				$this->fileModel->delete($version->cueSheetVersionId);
				}
			$this->cueSheetVersionTable->delete();
			}
		}

	public function download(\App\Record\CueSheetVersion $cueSheetVersion) : string
		{
		if ($cueSheetVersion->loaded())
			{
			$cuesheet = $cueSheetVersion->cueSheet;
			$name = $cuesheet->name;
			$name = \str_replace([' ', '.', ], ['_', '', ], $name);
			$extension = $cueSheetVersion->extension ?? '';
			$version = '_v' . $cueSheetVersion->cueSheetVersionId;
			$downloadName = $name . $version . $extension;
			$error = $this->fileModel->download($cueSheetVersion->cueSheetVersionId, $extension, $downloadName);

			if ($error)
				{
				\http_response_code(404);

				return "File not found: {$error}";
				}
			}
		else
			{
			\http_response_code(404);

			return "{$cueSheetVersion->cueSheetVersionId} is not a valid cue sheet number";
			}

		return '';
		}

	public function merge(int $from, int $to) : void
		{
		if ($from == $to)
			{
			return;
			}
		$input = ['from' => $from, 'to' => $to, ];
		$sql = 'update ride set cueSheetId=:to where cueSheetId=:from';
		\PHPFUI\ORM::execute($sql, $input);
		$sql = 'update cueSheetVersion set cueSheetId=:to where cueSheetId=:from';
		\PHPFUI\ORM::execute($sql, $input);
		$this->delete(new \App\Record\CueSheet($from));
		}

	public function reject(\App\Record\CueSheet $cuesheet, string $message) : void
		{
		$submitter = $cuesheet->member;

		if ($submitter->loaded())
			{
			$email = new \App\Model\Email('rejectCue', new \App\Model\Email\CueSheet($cuesheet, $message));
			$email->setToMember($submitter->toArray());
			$email->setFromMember(\App\Model\Session::getSignedInMember());
			$email->send();
			}
		$this->delete($cuesheet, true);
		}
	}
