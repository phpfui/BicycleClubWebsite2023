<?php

namespace App\View;

class Subscription
	{
	protected string $clubName;

	/** @var array<string,mixed> */
	protected array $member = [];

	protected float $subscriptionDues;

	protected string $type = 'Subscription';

	private readonly \App\Model\PayPal $paypalModel;

	public function __construct(private readonly \App\View\Page $page, int $memberId)
		{
		$memberTable = new \App\Table\Member();
		$this->member = $memberTable->getMembership($memberId);
		$this->paypalModel = new \App\Model\PayPal($this->type);
		$settingTable = new \App\Table\Setting();
		$duesModel = new \App\Model\MembershipDues();
		$this->subscriptionDues = (float)$duesModel->SubscriptionDues;
		$this->clubName = $settingTable->value('clubName');
		$members = \App\Table\Member::membersInMembership((int)$this->member['membershipId']);
		$this->subscriptionDues += (\count($members) - 1) * (float)$duesModel->AdditionalMemberDues[0];
		}

	public function populateForm(\PHPFUI\Form $form) : void
		{
		$form->setAttribute('action', $this->paypalModel->getUrl() . '/cgi-bin/webscr');
		$form->add(new \PHPFUI\Input\Hidden('cmd', '_xclick-subscriptions'));
		$form->add(new \PHPFUI\Input\Hidden('redirect_cmd', '_xclick'));
		$form->add(new \PHPFUI\Input\Hidden('item_name', $this->clubName . ' Membership ' . $this->type));
		$form->add(new \PHPFUI\Input\Hidden('item_number', "Membership-{$this->member['memberId']}"));
		$form->add(new \PHPFUI\Input\Hidden('amount', (string)$this->subscriptionDues));
		$form->add(new \PHPFUI\Input\Hidden('a3', (string)$this->subscriptionDues));
		$form->add(new \PHPFUI\Input\Hidden('p3', (string)1));
		$form->add(new \PHPFUI\Input\Hidden('t3', 'Y'));
		$form->add(new \PHPFUI\Input\Hidden('src', (string)1));
		$form->add(new \PHPFUI\Input\Hidden('srt', (string)30));
		$form->add(new \PHPFUI\Input\Hidden('quantity', (string)1));
		$form->add(new \PHPFUI\Input\Hidden('first_name', $this->member['firstName']));
		$form->add(new \PHPFUI\Input\Hidden('last_name', $this->member['lastName']));
		$form->add(new \PHPFUI\Input\Hidden('address1', $this->member['address']));
		$form->add(new \PHPFUI\Input\Hidden('address2', ''));
		$form->add(new \PHPFUI\Input\Hidden('city', $this->member['town']));
		$form->add(new \PHPFUI\Input\Hidden('state', $this->member['state']));
		$form->add(new \PHPFUI\Input\Hidden('zip', $this->member['zip']));
		$phone = $this->member['phone'] ?? '';
		$form->add(new \PHPFUI\Input\Hidden('night_phone_a', $phone));
		$cell = $this->member['cellPhone'] ?? '';
		$form->add(new \PHPFUI\Input\Hidden('night_phone_b', $cell));
		$form->add(new \PHPFUI\Input\Hidden('night_phone_c', ''));
		$form->add(new \PHPFUI\Input\Hidden('no_note', (string)1));
		$form->add(new \PHPFUI\Input\Hidden('no_shipping', (string)1));
		$form->add(new \PHPFUI\Input\Hidden('rm', (string)2));
		$server = 'https://' . $_SERVER['SERVER_NAME'];
		$form->add(new \PHPFUI\Input\Hidden('notify_url', $server . '/PayPal/notify/' . $this->paypalModel->getType()));
		$form->add(new \PHPFUI\Input\Hidden('cpp_header_image', $server . $this->paypalModel->getLogo()));
		$form->add(new \PHPFUI\Input\Hidden('return', $server . '/PayPal/completed/' . $this->paypalModel->getType()));
		$form->add(new \PHPFUI\Input\Hidden('cancel_return', $server . '/PayPal/cancelled/' . $this->paypalModel->getType()));
		$form->add(new \PHPFUI\Submit('Subscribe With PayPal'));
		}

	public function subscribe() : \PHPFUI\FieldSet
		{
		if ($this->member['renews'])
			{
			$fieldSet = new \PHPFUI\FieldSet('You have a membership subscription');
			$format = 'l F j, Y';
			$fieldSet->add(new \App\UI\Display('Your membership expires on:', \App\Tools\Date::formatString($format, $this->member['expires'])));
			$fieldSet->add(new \App\UI\Display('Your subscription will renew on:', \App\Tools\Date::formatString($format, $this->member['renews'])));
			$expires = \App\Model\Member::addYears($this->member['expires'], 1);
			$expiresString = \App\Tools\Date::formatString($format, $expires);
			$fieldSet->add(new \App\UI\Display('And will extend it until:', $expiresString));
			$message = '<br>You can cancel it at any time by hitting the cancel subscription button below. Please note if you cancel your ' .
					'subscription, your membership will continue until you reach your expiration date of ' . $expiresString;
			$fieldSet->add($message);
			$message = '<br><br><a href="' . $this->paypalModel->getUrl() . '/cgi-bin/webscr?cmd=_subscr-find' . // &alias=' . $this->paypalModel->getAccountId() .
				'&switch_classic=true"><img src="https://www.paypalobjects.com/webstatic/en_US/i/btn/png/btn_unsubscribe_113x26.png"></a>';
			$fieldSet->add($message);
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Setup a Membership Subscription');
			$format = 'F j, Y';
			$message = 'You can set up a membership subscription. With a subscription, you are automatically renewed at $' . $this->subscriptionDues . ' until you cancel your subscription. ';
			$message .= 'You can cancel at any time. A subscription is good for a year, and will add a year to your membership when it renews.<br><br>';
			$message .= 'By subscribing today, you will add a year to your membership now, and every year at this time.<br><br>';
			$fieldSet->add($message);
			$form = new \PHPFUI\Form($this->page);
			$this->populateForm($form);
			$fieldSet->add($form);
			}

		return $fieldSet;
		}
	}
