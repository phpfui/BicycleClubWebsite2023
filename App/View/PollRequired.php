<?php

namespace App\View;

class PollRequired implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Poll $poll, \App\Model\Poll $model)
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['answer'], $_POST['submit']) && 'Vote' == $_POST['submit'])
			{
			$_POST['pollId'] = $poll->pollId;
			$model->saveVote($poll, $_POST);
			}
		}

	public function __toString() : string
		{
		$view = new \App\View\Polls($this->page);
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader('Required Poll'));
		$container->add($view->changeVote($this->poll, false));

		return (string)"{$container}";
		}
	}
