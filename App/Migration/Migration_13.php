<?php

namespace App\Migration;

class Migration_13 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Member Notices';
		}

	public function down() : bool
		{
		return $this->dropTable('memberNotice');
		}

	public function up() : bool
		{
		$this->runSQL('delete from membership where town is null;');
		$this->runSQL('delete from member where membershipid not in (select membershipid from membership);');
		$this->runSQL('update membership set lastRenewed = null where lastRenewed=joined;');
		$this->dropTable('memberNotice');
		$this->runSQL('CREATE TABLE `memberNotice` (`memberNoticeId` int NOT NULL AUTO_INCREMENT primary key,`body` mediumtext,`title` varchar(255) not null,memberId int,`overridePreferences` int default 0,`field` varchar(30) not null,`dayOffsets` varchar(255) not null, summary int not null default 1) ENGINE=InnoDB;');

		$settingTable = new \App\Table\Setting();
		$memberPicker = new \App\Model\MemberPicker('Membership Chair');
		$membershipChair = $memberPicker->getMember()['memberId'] ?? 0;

		$emails = [
			'newMember' => ['Welcome to the ~clubName~', 'joined', -1],
			'renewedMsg' => ['Thanks for renewing your ~clubName~  membership', 'lastRenewed', -1],
			'expirngMsg' => ['Your ~clubName~  membership expires on ~expires~', 'expires', -30, -15, 0],
			// 'subscriptionMsg',
			'expireMsg' => ['Your ~clubName~ membership has lapsed. Renew today!', 'expires', 1, 15, 30],
		];

		$settingTable = new \App\Table\Setting();

		foreach ($emails as $body => $fields)
			{
			$memberNotice = new \App\Record\MemberNotice();
			$memberNotice->body = $settingTable->value($body);
			$memberNotice->title = \array_shift($fields);
			$betterTitle = $settingTable->value($memberNotice->title);

			if ($betterTitle)
				{
				$memberNotice->title = $betterTitle;
				}
			$memberNotice->field = \array_shift($fields);
			$memberNotice->dayOffsets = \implode(',', $fields);
			$memberNotice->memberId = $membershipChair;
			$memberNotice->insert();
			}

		return true;
		}
	}
