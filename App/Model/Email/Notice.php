<?php

namespace App\Model\Email;

class Notice extends \App\Tools\EMail
	{
	public function __construct(\App\Record\MemberNotice $notice, \App\DB\Interface\EmailData $data)
		{
		parent::__construct();
		$this->setBody(\App\Tools\TextHelper::processText($notice->body, $data->toArray()));
		$this->setSubject(\App\Tools\TextHelper::processText($notice->title, $data->toArray()));
		$this->setHtml();
		$member = [];

		if ($notice->memberId)
			{
			$member = $notice->member->toArray();
			}
		else
			{
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$member = $memberPicker->getMember();
			}
		$this->setFromMember($member);
		}
	}
