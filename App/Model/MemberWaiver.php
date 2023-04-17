<?php

namespace App\Model;

class MemberWaiver extends \App\Model\File
	{
	public function __construct(private readonly \App\Record\Member $member)
		{
		parent::__construct('../files/memberWaiver');
		}

	public function downloadGenerated() : string
		{
		if (! $this->member->loaded())
			{
			return 'invalid member';
			}

		$waiverPath = $this->get($this->member->memberId . '.pdf');

		if (! \file_exists($waiverPath))
			{
			$this->generate();
			}

		return $this->download($this->member->memberId, '.pdf', $this->getPrettyFileName());
		}

	public function emailConfirmation(bool $regenerate = false) : void
		{
		if (! $this->member->memberId)
			{
			return;
			}

		if ($regenerate)
			{
			$this->generate();
			}

		$settingTable = new \App\Table\Setting();
		$email = new \App\Tools\EMail();
		$email->setSubject($settingTable->value('WaiverTitle'));
		$memberPicker = new \App\Model\MemberPicker('Membership Chair');
		$fromMember = $memberPicker->getMember();
		$email->setFromMember($fromMember);
		$email->setHtml();
		$email->setBody(\App\Tools\TextHelper::processText($settingTable->value('WaiverText'), $this->member->toArray()));
		$email->setToMember($this->member->toArray());
		$file = $this->member->memberId . '.pdf';
		$email->addAttachment($this->get($file), $this->prettify($this->getPrettyFileName()));
		$email->send();
		}

	public function generate() : bool
		{
		if (! $this->member->memberId)
			{
			return false;
			}

		$file = $this->member->memberId . '.pdf';
		$filePath = $this->get($file);
		$report = new \App\Report\MemberWaiver();
		$report->generate($this->member->toArray());
		\file_put_contents($filePath, $report->output('', \Mpdf\Output\Destination::STRING_RETURN));

		return true;
		}

	public function getPrettyFileName() : string
		{
		$file = "Waiver_{$this->member->lastName}_{$this->member->firstName}_" . \date('Y-m-d', \strtotime($this->member->acceptedWaiver)) . '.pdf';

		return $file;
		}
	}
