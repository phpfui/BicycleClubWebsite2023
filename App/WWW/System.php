<?php

namespace App\WWW;

class System extends \App\Common\WWW\System
	{
	public function testText() : void
		{
		if ($this->page->addHeader('Test Texting'))
			{
			$form = new \PHPFUI\Form($this->page);
			$member = \App\Model\Session::signedInMemberRecord();
			$form->add(new \App\UI\TelUSA($this->page, 'From', 'From Phone Number', $member->cellPhone));
			$form->add(new \PHPFUI\Input\TextArea('Body', 'Text Body'));
			$form->setAttribute('action', '/SMS/receive');
			$submit = new \PHPFUI\Submit('Text');
			$form->add($submit);
			$this->page->addPageContent($form);
			}
		}
	}
