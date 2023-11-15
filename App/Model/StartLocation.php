<?php

namespace App\Model;

class StartLocation
	{
	/**
	 * @param array<string,string> $startLocation
	 */
	public function add(array $startLocation) : void
		{
		$location = new \App\Record\StartLocation();
		$location->setFrom($startLocation);
		$id = $location->insert();
		$author = \App\Model\Session::getSignedInMember();
		$settingTable = new \App\Table\Setting();
		$email = new \App\Tools\EMail();
		$message = "The following start location was added by {$author['firstName']} {$author['lastName']}:<p>";
		$server = $settingTable->value('homePage');
		$message .= "<a href='{$server}/Locations/edit/{$id}'>{$startLocation['name']}</a>";
		$email->setBody($message);
		$email->setHtml();
		$email->setSubject('New start location added');
		$memberPicker = new \App\Model\MemberPicker('Rides Chair');
		$email->addToMember($memberPicker->getMember());
		$email->setFromMember($author);
		$email->send();
		}
	}
