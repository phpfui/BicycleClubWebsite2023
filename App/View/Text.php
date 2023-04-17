<?php

namespace App\View;

class Text
	{
	private readonly \App\Model\SMS $smsModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->smsModel = new \App\Model\SMS();
		}

	public function textMember(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $member->loaded() || ! $member->allowTexting || ! $this->smsModel->enabled() || ! ($cell = $this->smsModel->cleanPhone($member->cellPhone)))
			{
			$container->add(new \PHPFUI\SubHeader('Member not found or does not have a textable number'));

			return $container;
			}

		$name = \App\Tools\TextHelper::unhtmlentities($member->fullName());
		$submitText = 'Text ' . $name;

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']) && $_POST['submit'] == $submitText)
			{
			$this->smsModel->setFromMember(\App\Model\Session::signedInMemberRecord());
			$this->smsModel->setBody($_POST['message']);
			$this->smsModel->setGeoLocation($_POST);
			$this->smsModel->textMember($member);
			\App\Model\Session::setFlash('success', "Your message was sent to {$name}");
			$this->page->redirect();

			return $container;
			}
		$container->add(new \PHPFUI\SubHeader($name));
		$flash = \App\Model\Session::getFlash('success');

		// we have a success flash, so we are done.
		if ($flash)
			{
			return $container;
			}

		$form = new \PHPFUI\Form($this->page);
		$sender = \App\Model\Session::signedInMemberRecord();

		$form->add(new \PHPFUI\Input\Hidden('memberId', (string)$sender->memberId));

		$fieldSet = new \PHPFUI\FieldSet('Text To Send');
		$message = new \PHPFUI\Input\TextArea('message');
		$message->setToolTip('So what is on your mind?');
		$message->setAttribute('maxlength', (string)1600);
		$message->setRequired();
		$fieldSet->add($message);
		$form->add($fieldSet);

		$form->add($this->getGeoFields($sender, new \PHPFUI\Submit($submitText)));

		$container->add($form);

		return $container;
		}

	public function textRide(\App\Record\Ride $ride) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $ride->loaded() || ! $this->smsModel->enabled())
			{
			$container->add(new \PHPFUI\SubHeader('Ride not found'));

			return $container;
			}

		$submitText = 'Text All Riders';
		$sender = \App\Model\Session::signedInMemberRecord();

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']) && $_POST['submit'] == $submitText)
			{
			$this->smsModel->setFromMember($sender);
			$this->smsModel->setBody($_POST['message']);
			$this->smsModel->setGeoLocation($_POST);
			$this->smsModel->textRide($ride);
			\App\Model\Session::setFlash($submitText, 'Your message was sent to everyone on the ride');
			$this->page->redirect();

			return $container;
			}

		$title = \App\Tools\Date::formatString('l, F j, Y', $ride->rideDate) . ' at ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime);
		$container->add(new \PHPFUI\SubHeader($ride->title));
		$container->add(new \PHPFUI\Header($title, 5));

		$flash = \App\Model\Session::getFlash($submitText);

		// have a success flash, so we are done
		if ($flash)
			{
			return $container;
			}

		$form = new \PHPFUI\Form($this->page);

		$message = new \PHPFUI\Input\TextArea('message', 'Message');
		$message->setToolTip('So what is on your mind?');
		$message->addAttribute('placeholder', 'So what is on your mind?');
		$message->setAttribute('maxlength', (string)1600);
		$message->setRequired();
		$form->add($message);
		$form->add($this->getGeoFields($sender, new \PHPFUI\Submit($submitText)));
		$container->add($form);

		return $container;
		}

	private function getGeoFields(\App\Record\Member $sender, \PHPFUI\Button $button) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (2 == $sender->geoLocate)
			{
			$container->add($button);

			return $container;
			}

		$geoLocatation = new \App\Model\GeoLocation();
		$latitude = new \PHPFUI\Input\Hidden('latitude');
		$container->add($latitude);
		$longitude = new \PHPFUI\Input\Hidden('longitude');
		$container->add($longitude);

		$geoLocatation->setLatLong($latitude, $longitude);

		$callout = new \PHPFUI\Callout('alert');
		$callout->add('Please Turn on Location Services');
		$callout->addClass('hide');
		$container->add($geoLocatation->setMessageElement($callout));

		$geoLocation = new \PHPFUI\Input\CheckBoxBoolean('geoLocate', 'Include Geo Location', (bool)$sender->geoLocate);
		$geoLocation->setToolTip('Include a Google Maps link to your location in the message.');
		$container->add($geoLocatation->setOptIn($geoLocation));

		$container->add($geoLocatation->setAcceptButton($button));

		$this->page->addJavaScript($geoLocatation->getJavaScript());

		return $container;
		}
	}
