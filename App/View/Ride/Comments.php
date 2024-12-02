<?php

namespace App\View\Ride;

class Comments
	{
	private readonly bool $deleteComments;

	private readonly \App\Record\RideSignup $rideSignup;

	/**
	 * @var array<string,int>
	 */
	private array $rideSignupKey = [];

	private readonly \App\Table\RideSignup $rideSignupTable;

	private readonly \App\Record\Member $sender;

	private readonly \App\Model\SMS $smsModel;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Ride $ride)
		{
		$this->sender = \App\Model\Session::signedInMemberRecord();
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->rideSignupKey = ['rideId' => $this->ride->rideId, 'memberId' => $this->sender->memberId];
		$this->rideSignup = new \App\Record\RideSignup($this->rideSignupKey);
		$this->smsModel = new \App\Model\SMS();

		$this->deleteComments = $this->page->isAuthorized('Delete Ride Comments') ||
				$this->ride->memberId == \App\Model\Session::signedInMemberId();
		}

	public function getRideComments() : string | \PHPFUI\Container
		{
		if (\App\Enum\Ride\Comments::DISABLED_AND_HIDDEN == $this->ride->commentsDisabled)
			{
			return '';
			}

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['submit']) && 'Add' == $_POST['submit'] && $this->ride->rideId)
				{
				$data = $_POST;
				$data['rideId'] = $this->ride->rideId;
				$data['memberId'] = $this->sender->memberId;
				$rideComment = new \App\Record\RideComment();
				$rideComment->setFrom($data);
				$rideComment->insert();

				if ($this->rideSignup->loaded() && $this->rideSignup->rideComments != (int)$_POST['rideComments'])
					{
					$this->rideSignup->rideComments = (int)$_POST['rideComments'];
					$this->rideSignup->update();
					}

				if (! empty($_POST['comment']))
					{
					$email = new \App\Tools\EMail();

					if ($this->smsModel->enabled() && $this->ride->rideDate == \App\Tools\Date::todayString() && \App\Enum\RideComment\Delivery::EMAIL != \App\Enum\RideComment\Delivery::from((int)$_POST['delivery']))
						{
						$this->smsModel->setBody($_POST['comment']);
						$this->smsModel->setGeoLocation($_POST);
						$this->smsModel->setFromMember($this->sender);
						$this->smsModel->textRide($this->ride);
						}

					$settings = new \App\Table\Setting();
					$site = $settings->value('homePage');
					$rideLink = "<a href='{$site}/Rides/signedUp/{$this->ride->rideId}'>{$this->ride->title}</a>";
					$rideDate = $this->ride->rideDate;
					$body = 'On <i>' . \date('D M j g:i a') . "</i> <b>{$this->sender->fullName()}</b> in relation to {$rideLink} on {$rideDate}, said:<p><hr>";
					$body .= '<pre style="font-size:14px;">' . $data['comment'] . '</pre>';

					$geoLocation = \App\Model\RideWithGPS::getMapPinLink($_POST);

					if ($geoLocation)
						{
						$body .= '<p>Sent from: ' . new \PHPFUI\Link($geoLocation);
						}

					$body .= "<hr><p><a href='{$site}/Rides/rideComments/{$this->ride->rideId}'>Post a new comment</a><p>";
					$body .= "<p><a href='{$site}/Rides/rideComments/{$this->ride->rideId}?unsubscribe'>Unsubscribe from future comments for this ride</a><p>";
					$body .= "<p><a href='{$site}/Rides/rideComments/{$this->ride->rideId}?unsubscribe&unsubscribeAll'>Unsubscribe from all future ride comments</a><p>";
					$email->setBody($body);
					$email->setSubject($this->sender->fullName() . ' commented on ' . $this->ride->title);
					$email->setFromMember($this->sender->toArray());

					if ($this->rideSignup->loaded() && ! $_POST['rideComments'])
						{
						$email->setToMember($this->sender->toArray());
						}
					$members = $this->rideSignupTable->getAllSignedUpRiders($this->ride, false);

					foreach ($members as $member)
						{
						if ($member->rideComments)
							{
							$email->addBCCMember($member->toArray());
							}
						}
					$email->setHtml();
					$email->bulkSend();
					}
				$this->page->redirect();

				return '';
				}
			elseif ($this->deleteComments && 'deleteComment' == ($_POST['action'] ?? ''))
				{
				$rideComment = new \App\Record\RideComment((int)$_POST['rideCommentId']);
				$rideComment->delete();
				$this->page->setResponse($_POST['rideCommentId']);

				return '';
				}
			}
		$container = new \PHPFUI\Container();

		if (isset($_GET['unsubscribe']))
			{
			$this->rideSignup->rideComments = 0;
			$this->rideSignup->update();
			$container->add(new \App\UI\Alert('You have been unsubscribed from comments for this ride'));
			}

		if (isset($_GET['unsubscribeAll']))
			{
			$this->rideSignup->rideComments = 0;
			$this->rideSignup->update();
			$this->sender->rideComments = 0;
			$this->sender->update();
			$container->add(new \App\UI\Alert('You have been unsubscribed from ride comments on future rides.'));
			}
		$index = 'rideCommentId';
		$delete = new \PHPFUI\AJAX('deleteComment', 'Are you sure you want to delete this comment?');
		$delete->addFunction('success', "$('#{$index}-'+data.response).css('background-color','red').hide('fast').remove()");
		$this->page->addJavaScript($delete->getPageJS());
		$rideCommentTable = new \App\Table\RideComment();
		$rideCommentTable->setWhere(new \PHPFUI\ORM\Condition('rideId', $this->ride->rideId));
		$rideCommentTable->addOrderBy('time', 'desc');

		if (! $this->page->isAuthorized('Ride Comments'))
			{
			return $container;
			}

		$fieldSet = new \PHPFUI\FieldSet('Ride Comments');

		if (\App\Enum\Ride\Comments::ENABLED == $this->ride->commentsDisabled)
			{
			$add = new \PHPFUI\Button('Add Comment');
			$fieldSet->add($add);
			$this->getModal($add);

			foreach ($rideCommentTable->getRecordCursor() as $rideComment)
				{
				$rideCommentId = $rideComment->rideCommentId;
				$row = new \PHPFUI\GridX();
				$nameColumn = new \PHPFUI\Cell($this->deleteComments ? 11 : 12);
				$time = \App\Tools\TimeHelper::relativeFormat($rideComment->time);
				$rider = $rideComment->member;

				$location = \App\Model\RideWithGPS::getMapPinLink($rideComment->toArray());

				if ($location)
					{
					$location = new \PHPFUI\Link($location, 'from');
					$location->addAttribute('target', '_blank');
					}

				$rideComment->comment = \App\Tools\TextHelper::addLinks($rideComment->comment);
				$nameColumn->add("<b>{$rider->fullName()}</b> - <i>{$time}</i> said {$location}:<br>" . $rideComment->comment);
				$row->add($nameColumn);

				if ($this->deleteComments)
					{
					$deleteColumn = new \PHPFUI\Cell(1);
					$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
					$trash->addAttribute('onclick', $delete->execute([$index => $rideCommentId]));
					$deleteColumn->add($trash);
					$row->add($deleteColumn);
					}
				$row->setId("{$index}-{$rideCommentId}");
				$fieldSet->add($row);
				$fieldSet->add('<hr>');
				}
			}
		else
			{
			$fieldSet->add(new \PHPFUI\Header('Comments have been disabled for this ride', 4));
			}

		$container->add($fieldSet);

		return $container;
		}

	private function getModal(\PHPFUI\HTML5Element $link) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $link);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\SubHeader('Comment on ' . $this->ride->title));
		$optIn = new \PHPFUI\Input\CheckBoxBoolean('rideComments', 'Receive ride comment emails', (bool)$this->sender->rideComments);
		$optIn->setToolTip('Check this box if you want to get future ride comments emailed to you.');

		$multiColumn = new \PHPFUI\MultiColumn($optIn);

		if (2 !== $this->sender->geoLocate)
			{
			$geoLocation = new \PHPFUI\Input\CheckBoxBoolean('geoLocate', 'Include GPS Location', (bool)$this->sender->geoLocate);
			$geoLocation->setToolTip('Include a Google Maps link to your location in the message.');

			$geoLocate = new \App\Model\GeoLocation();
			$multiColumn->add($geoLocate->setOptIn($geoLocation));

			$latitude = new \PHPFUI\Input\Hidden('latitude');
			$modalForm->add($latitude);
			$longitude = new \PHPFUI\Input\Hidden('longitude');
			$modalForm->add($longitude);
			$geoLocate->setLatLong($latitude, $longitude);

			$callout = new \PHPFUI\Callout('alert');
			$callout->add('Please Turn on Location Services');
			$callout->addClass('hide');
			$modalForm->add($geoLocate->setMessageElement($callout));
			}
		$modalForm->add($multiColumn);

		$textArea = new \PHPFUI\Input\TextArea('comment', 'Your Comments (255 characters max)');
		$textArea->addAttribute('maxlength', (string)255);
		$textArea->setRequired();
		$modalForm->add($textArea);

		if ($this->smsModel->enabled() && $this->ride->rideDate == \App\Tools\Date::todayString())
			{
			$radioGroup = new \PHPFUI\Input\RadioGroupEnum('delivery', 'Delivery Method', \App\Enum\RideComment\Delivery::BOTH);
			}
		else
			{
			$radioGroup = new \PHPFUI\Input\Hidden('delivery', (string)\App\Enum\RideComment\Delivery::EMAIL->value);
			}
		$modalForm->add($radioGroup);

		$submit = new \PHPFUI\Submit('Add');
		$buttonGroup = new \PHPFUI\ButtonGroup();

		if (2 !== $this->sender->geoLocate)
			{
			$buttonGroup->addButton($geoLocate->setAcceptButton($submit));
			$this->page->addJavaScript($geoLocate->getJavaScript());
			}
		else
			{
			$buttonGroup->addButton($submit);
			}
		$buttonGroup->addButton(new \App\UI\Cancel());
		$modalForm->add($buttonGroup);

		$modal->add($modalForm);
		}
	}
