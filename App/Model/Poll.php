<?php

namespace App\Model;

class Poll
	{
	private readonly \App\Table\Poll $pollTable;

	public function __construct()
		{
		$this->pollTable = new \App\Table\Poll();
		}

	public function getRequiredPoll() : \App\Record\Poll
		{
		$requiredPolls = $this->pollTable->getRequiredPolls();

		if (! \count($requiredPolls))
			{
			return new \App\Record\Poll();
			}

		foreach ($requiredPolls as $poll)
			{
			// poll is not required if no answers, and probably saved too early.
			if (! $this->getVote($poll) && (\is_countable($poll->PollAnswerChildren) ? \count($poll->PollAnswerChildren) : 0))
				{
				return $poll;
				}
			}

		return new \App\Record\Poll();
		}

	public function getVote(\App\Record\Poll $poll) : int
		{
		$key = ['pollId' => $poll->pollId, 'membershipId' => \App\Model\Session::signedInMembershipId()];

		if (! $poll->membershipOnly)
			{
			$key['memberId'] = \App\Model\Session::signedInMemberId();
			}
		$vote = new \App\Record\PollResponse($key);

		return $vote->empty() ? 0 : $vote->answer;
		}

	public function saveVote(\App\Record\Poll $poll, array $get) : void
		{
		$key = ['pollId' => $poll->pollId, 'membershipId' => \App\Model\Session::signedInMembershipId()];

		if (! $poll->membershipOnly)
			{
			$key['memberId'] = \App\Model\Session::signedInMemberId();
			}
		$oldPollResponse = new \App\Record\PollResponse($key);
		$oldAnswer = $oldPollResponse->answer ?? 0;
		$oldPollResponse->delete();
		$key['answer'] = $get['answer'];
		$key['memberId'] = \App\Model\Session::signedInMemberId();
		$newPollResponse = new \App\Record\PollResponse();
		$newPollResponse->setFrom($key);
		$newPollResponse->insert();

		if ($poll->emailConfirmation && $oldAnswer != $key['answer'])
			{
			$settingTable = new \App\Table\Setting();
			$boardTable = new \App\Table\BoardMember();
			$answer = new \App\Record\PollAnswer(['pollAnswerId' => $key['answer'], 'pollId' => $poll->pollId]);

			$secretary = $boardTable->getPosition('Secretary');

			if ($secretary->empty())
				{
				$memberPicker = new \App\Model\MemberPicker('Membership Chair');
				$secretary = $memberPicker->getMember();
				}
			$title = $settingTable->value('clubAbbrev') . ' Vote Confirmation';
			$clubName = $settingTable->value('clubName');
			$voter = \App\Model\Session::getSignedInMember();
			$url = $settingTable->value('homePage') . '/Polls/vote/' . $poll->pollId;
			$message = "Dear {$voter['firstName']} {$voter['lastName']},<br><br>We have recorded your vote as:" .
					"<br><br><strong>{$answer['answer']}</strong><br><br>for the question:<br><br>{$poll->question}<br><br>" .
					'You can change your vote at any time till the end of ' . \App\Tools\Date::formatString('l F j, Y', $poll->endDate) .
					" <a href='{$url}'>here</a>.<br><br>If you have any questions, please reply to this email.<br><br>" .
					"Thanks for voting,<br><br>{$secretary['firstName']} {$secretary['lastName']},<br>{$clubName} Secretary";
			$email = new \App\Tools\EMail();
			$email->setSubject($title);
			$email->setHtml();
			$email->setBody($message);
			$email->setFromMember($secretary);
			$email->addBCC('voting@' . \strtolower((string)$_SERVER['SERVER_NAME']));
			$email->addToMember($voter);
			$email->send();
			}
		}
	}
