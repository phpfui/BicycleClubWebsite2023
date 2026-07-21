<?php

namespace App\Table;

class PollResponse extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PollResponse::class;

	public function getVotes(int $pollId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect(new \PHPFUI\ORM\Literal('count(pollResponse.answer)'), 'count');
		$this->addSelect('pollAnswer.answer');
		$condition = new \PHPFUI\ORM\Condition('pollAnswerId', new \PHPFUI\ORM\Literal('pollResponse.answer'));
	  $condition->and('pollResponse.pollId', $pollId);
		$this->setWhere($condition);
		$this->setJoin('pollAnswer');
		$this->setGroupBy('pollResponse.answer');
		$this->setOrderBy('count', 'desc');

		return $this->getDataObjectCursor();
		}

	public function setMyMembershipVotesQuery() : static
		{
		$this->addJoin('poll');

		$pollAnswerCondition = new \PHPFUI\ORM\Condition('pollAnswer.pollId', new \PHPFUI\ORM\Field('pollResponse.pollId'));
		$pollAnswerCondition->and('pollResponse.answer', new \PHPFUI\ORM\Field('pollAnswer.pollAnswerId'));
		$this->addJoin('pollAnswer', $pollAnswerCondition);

		$memberCondition = new \PHPFUI\ORM\Condition('pollResponse.membershipId', \App\Model\Session::signedInMembershipId());

		$this->setWhere($memberCondition);

		$this->addSelect('poll.pollId');
		$this->addSelect('poll.endDate');
		$this->addSelect('poll.question');
		$this->addSelect('pollAnswer.answer');
		$this->addSelect('pollResponse.memberId');

		return $this;
		}

	public function setMyVotesQuery() : static
		{
		$this->addJoin('poll');

		$pollAnswerCondition = new \PHPFUI\ORM\Condition('pollAnswer.pollId', new \PHPFUI\ORM\Field('pollResponse.pollId'));
		$pollAnswerCondition->and('pollResponse.answer', new \PHPFUI\ORM\Field('pollAnswer.pollAnswerId'));
		$this->addJoin('pollAnswer', $pollAnswerCondition);

		$memberCondition = new \PHPFUI\ORM\Condition('pollResponse.membershipId', \App\Model\Session::signedInMembershipId());
		$memberCondition->and('pollResponse.memberId', \App\Model\Session::getSignedInMemberId(), new \PHPFUI\ORM\Operator\NotEqual());

		$whereCondition = new \PHPFUI\ORM\Condition('pollResponse.memberId', \App\Model\Session::getSignedInMemberId());
		$this->setWhere($whereCondition);

		$this->addSelect('poll.pollId');
		$this->addSelect('poll.endDate');
		$this->addSelect('poll.question');
		$this->addSelect('pollAnswer.answer');
		$this->addSelect('pollResponse.memberId');

		return $this;
		}

	public function setVotersQuery(int $pollId) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('pollResponse.pollId', $pollId));
		$this->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition('pollAnswer.pollId', new \PHPFUI\ORM\Field('pollResponse.pollId'));
		$condition->and('pollAnswer.pollAnswerId', new \PHPFUI\ORM\Field('pollResponse.answer'));
		$this->addJoin('pollAnswer', $condition);
		$this->addSelect('pollAnswer.answer', 'answer');
		$this->addSelect('member.firstName', 'firstName');
		$this->addSelect('member.lastName', 'lastName');
		$this->addSelect('pollResponse.memberId', 'memberId');
		$this->addSelect('pollResponse.membershipId', 'membershipId');

		return $this;
		}
	}
