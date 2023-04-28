<?php

namespace App\View\Membership;

class Renew
	{
	private float $additionalMemberDues = 0;

	private int $allowedFamilyMembers = 0;

	private readonly \App\Model\Member $memberModel;

	private readonly iterable $members;

	private float $membershipPrice = 0;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Membership $membership, private readonly \App\View\Member $memberView)
		{
		$this->memberModel = new \App\Model\Member();
		$this->settingTable = new \App\Table\Setting();
		$this->members = \App\Table\Member::membersInMembership($membership->membershipId);
		$this->additionalMemberDues = (float)$this->settingTable->value('additionalMemberDues');
		$this->membershipPrice = (float)$this->settingTable->value('annualDues');

		if (isset($_GET['delete']))
			{
			$delete = (int)($_GET['delete']);

			if ($delete != \App\Model\Session::signedInMemberId())
				{
				foreach ($this->members as $member)
					{
					if ($member['memberId'] == $delete)
						{
						$this->memberModel->delete($delete);

						break;
						}
					}
				}
			$this->page->redirect();
			}
		}

	public function checkout(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$view = new \App\View\PayPal($this->page, new \App\Model\PayPal('Membership'));
		$output = new \PHPFUI\HTML5Element('div');
		$errors = $member->membership->validate();

		if (\App\Model\Session::checkCSRF() && ($_POST['submit'] ?? '') == 'Save')
			{
			$fields = \array_intersect_key($_POST, ['firstName' => 1, 'lastName' => 1, 'address' => 1, 'town' => 1, 'state' => 1, 'zip' => 1]);
			$member->setFrom($fields)->update();
			$member->membership->setFrom($fields)->update();

			$this->page->redirect();
			}

		if ($errors)
			{
			$customerModel = new \App\Model\Customer();
			$customerView = new \App\View\Customer($this->page, $customerModel);
			$form = $customerView->edit($member->memberId, false);

			$output->add(new \PHPFUI\Header('Please correct the following errors', 4));
			$output->add(new \App\UI\ErrorCallout($errors));
			$output->add($form);

			return $output;
			}

		$output->add($view->getPayPalLogo());
		$years = \max((int)($_POST['years'] ?? 1), 0);
		$additionalMembers = \count($this->members) - 1;
		$maxMembers = \max((int)($_POST['maxMembers'] ?? 1), 1);
		$additionalMembers = \max($additionalMembers, $maxMembers - 1);

		$paidMembers = $this->settingTable->value('PaidMembers');

		if ($years > 1)
			{
			$output->add("<br>You elected to renew for {$years} years");
			}
		$output->add("<br>{$paidMembers} Membership is $" . $this->membershipPrice . ' every 12 months.');

		if ($additionalMembers && $this->additionalMemberDues)
			{
			$output->add('<br>Additional members are $' . $this->additionalMemberDues . ' per year.');
			$output->add("<br>You have {$additionalMembers} additional member");

			if ($additionalMembers > 1)
				{
				$output->add('s');
				}
			}

		if ('Family' == $paidMembers && $additionalMembers > 0)
			{
			$additionalMembers = 1;
			}
		elseif ('Unlimited' == $paidMembers)
			{
			$additionalMembers = 0;
			}

		if ($years)
			{
			$unpaidBalance = $years * ($this->membershipPrice + $additionalMembers * $this->additionalMemberDues);
			}
		else
			{
			$unpaidBalance = $additionalMembers * $this->additionalMemberDues;
			}

		$donation = (float)($_POST['donation'] ?? 0.0);
		$unpaidBalance += $donation;

		if ($unpaidBalance <= 0.0)
			{
			\App\Model\Session::setFlash('alert', 'You have not specified a donation amount');

			$this->page->redirect('/Membership/renew');
			}
		$output->add('<br>Additional Donation $' . \number_format($donation, 2));
		$output->add('<p><b>Total Due : $' . \number_format($unpaidBalance, 2) . '</b>');
		$invoice = $this->memberModel->getRenewInvoice($member, $additionalMembers, $unpaidBalance, $years, $donation, $_POST['itemDetail'] ?? '');
		$output->add($view->getCheckoutForm($invoice, $output->getId(), 'Membership Renewal'));

		return $output;
		}

	public function renew(bool $confirmAndPayButton = true) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $this->memberModel->hasValidAddress($this->membership))
			{
			$content = new \App\View\Content($this->page);
			$container->add($content->getDisplayCategoryHTML('Address Required'));
			$form = new \PHPFUI\Form($this->page);

			if (\App\Model\Session::checkCSRF())
				{
				$this->membership->setFrom(\array_intersect_key($_POST, \array_flip(['address', 'town', 'state', 'zip'])));
				$this->membership->update();
				$this->page->redirect();
				}
			$form->add($this->memberView->getAddress($this->membership));
			$form->add(new \PHPFUI\Submit('Save and Continue'));
			$form->add(new \PHPFUI\Input\Hidden('membershipId', (string)$this->membership->membershipId));
			$container->add($form);

			return $container;
			}

		$numberMembers = \count($this->members);
		$functions = [];
		$paidMembers = $this->settingTable->value('PaidMembers');
		$renewalType = $this->settingTable->value('MembershipType');

		if ('Both' == $renewalType)
			{
			$container->add(new \PHPFUI\SubHeader('Renewal Options'));
			}

		if ($numberMembers > 1)
			{
			$container->add(new \PHPFUI\Header('You have the following members on your membership', 5));
			$table = new \PHPFUI\Table();
			$headers = ['name' => 'Member Name'];

			if ($this->page->isAuthorized('Edit Member'))
				{
				$headers['edit'] = 'Edit';
				}
			$headers['del'] = 'Delete';
			$table->setHeaders($headers);

			foreach ($this->members as $member)
				{
				$edit = new \PHPFUI\FAIcon('far', 'edit', "/Membership/edit/{$member['memberId']}");

				if ($member['memberId'] != \App\Model\Session::signedInMemberId())
					{
					$url = $this->page->getBaseURL();
					$delete = new \PHPFUI\FAIcon('far', 'trash-alt', $url . '?delete=' . $member['memberId']);
					$delete->setConfirm('Are you sure you want to remove this member from your membership?');
					}
				else
					{
					$delete = '&nbsp;';
					}
				$table->addRow(['name' => "{$member['firstName']} {$member['lastName']}",
					'edit' => $edit,
					'del' => $delete, ]);
				}
			$container->add($table);
			}

		$maxMembersOnMembership = (int)$this->settingTable->value('maxMembersOnMembership');

		if (! $maxMembersOnMembership || $maxMembersOnMembership > \count($this->members))
			{
			$container->add($this->memberView->getAddMemberModalButton($this->membership));
			}

		$dollar = '$';

		$membershipRates = new \PHPFUI\FieldSet('Membership Rates');

		switch ($paidMembers)
			{
			case 'Unlimited':
				$membershipRates->add(new \App\UI\Display('Annual Dues', '$' . $this->membershipPrice));

				break;

			case 'Paid':
				$membershipRates->add(new \App\UI\Display('Annual Dues', '$' . $this->membershipPrice));

				if ($this->additionalMemberDues)
					{
					$membershipRates->add(new \App\UI\Display('Additional Member Dues', '$' . $this->additionalMemberDues));
					}

				break;

			case 'Family':
				$membershipRates->add(new \App\UI\Display('Annual Dues', '$' . $this->membershipPrice));
				$membershipRates->add(new \App\UI\Display('Additional Member Dues', '$' . $this->additionalMemberDues));
				$this->allowedFamilyMembers = 1;

				break;
			}
		$container->add($membershipRates);

		$totalDue = new \PHPFUI\HTML5Element('div');
		$totalDisplay = new \PHPFUI\MultiColumn('<b>Total Due</b>', $totalDue);

		if ('Manual' == $renewalType || 'Both' == $renewalType)
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAttribute('action', '/Membership/renewCheckout');
			$multiColumn = new \PHPFUI\MultiColumn();
			$yearlyRenewal = new \PHPFUI\FieldSet('Yearly Renewal');
			$years = new \PHPFUI\Input\Select('years', 'Number of years to renew');
			$years->addOption('No Years, Donation only', '0');

			if ('Paid' == $paidMembers || ('Family' == $paidMembers && 1 == $this->membership->allowedMembers && \count($this->members) > 1))
				{
				$years->addOption('Add Members (no renewal)', '0');
				}

			for ($i = 1; $i < 10; ++$i)
				{
				$years->addOption("{$i} Year" . (($i > 1) ? 's' : ''), (string)$i, 1 == $i);
				}
			$years->addAttribute('onchange', 'computePrice();');
			$multiColumn->add($years);

			if ($this->additionalMemberDues)
				{
				$maxMembers = new \PHPFUI\Input\Select('maxMembers', 'Number of members on your membership');

				if (! $maxMembersOnMembership)
					{
					$maxMembersOnMembership = 10;
					}

				for ($i = $numberMembers; $i <= $maxMembersOnMembership; ++$i)
					{
					$maxMembers->addOption("{$i} Member" . (($i > 1) ? 's' : ''), (string)$i, $i == $numberMembers);
					}
				$maxMembers->addAttribute('onchange', 'computePrice()');
				$multiColumn->add($maxMembers);
				}
			else
				{
				$maxMembers = new \PHPFUI\Input\Hidden('maxMembers', (string)$maxMembersOnMembership);
				$yearlyRenewal->add($maxMembers);
				}
			$yearlyRenewal->add($multiColumn);
			$form->add($yearlyRenewal);

			$donationSet = new \PHPFUI\FieldSet('Optional Additional Donation');
			$multiColumn = new \PHPFUI\MultiColumn();
			$multiColumn->add($this->settingTable->value('donationText'));
			$donation = new \PHPFUI\Input\Text('donation', 'Donation Amount', '0');
			$donationId = $donation->getId();
			$donation->setToolTip('Your donation will be added to your membership dues.');
			$donation->addAttribute('onchange', 'updateDonation()');
			$multiColumn->add($donation);
			$donationSet->add($multiColumn);
			$donationSet->add(new \PHPFUI\Input\Text('itemDetail', 'Donation notes or dedications', ''));
			$form->add($donationSet);

			$yearId = $years->getId();
			$maxMembersId = $maxMembers->getId();
			$totalDueId = $totalDue->getId();
			$this->additionalMemberDues = (float)$this->additionalMemberDues;
			$js = <<<JAVASCRIPT
function computePrice() {
var years = {$dollar}('#{$yearId}').val();
var members = {$dollar}('#{$maxMembersId}').val() - 1 - {$this->allowedFamilyMembers};
if (members < 0) members = 0;
var price = parseInt($('#{$donationId}').val()) + years * ({$this->membershipPrice} + members * {$this->additionalMemberDues});
{$dollar}('#{$totalDueId}').html('<b>$'+price+'</b>');}computePrice()
JAVASCRIPT;
			$functions[] = 'computePrice()';
			$this->page->addJavaScript($js);
			$form->add($totalDisplay);

			if ($confirmAndPayButton)
				{
				$form->add('<br>');
				$form->add(new \PHPFUI\Submit('Confirm and Pay'));
				}
			$container->add($form);
			}

		if ('Subscription' == $renewalType || 'Both' == $renewalType)
			{
			$fieldSet = new \PHPFUI\FieldSet('Annual Subscription');
			$fieldSet->add("You are automatically renewed each year at this time at today's membership rate.<br><br>");
			$dollar = '$';
			$additional = 0;

			if ($numberMembers > 1 && $this->additionalMemberDues)
				{
				$additional = ($numberMembers - 1) * $this->additionalMemberDues;
				}

			if ($this->additionalMemberDues)
				{
				if (! $maxMembersOnMembership)
					{
					$maxMembersOnMembership = 10;
					}
				$maxMembers = new \PHPFUI\Input\Select('maxMembersSubscription', 'Number of members on your membership');

				for ($i = $numberMembers; $i <= $maxMembersOnMembership; ++$i)
					{
					$maxMembers->addOption("{$i} Member" . (($i > 1) ? 's' : ''), (string)$i, $i == $numberMembers);
					}
				$maxMembers->addAttribute('onchange', 'computePriceSubscription()');
				$maxMembersId = $maxMembers->getId();
				$totalDueId = $totalDue->getId();
				$js = <<<JAVASCRIPT
function computePriceSubscription() {
var members = {$dollar}('#{$maxMembersId}').val() - 1;
var price = {$this->membershipPrice} + members * {$this->additionalMemberDues};
{$dollar}('#{$totalDueId}').html('$'+price);}computePriceSubscription()
JAVASCRIPT;
				$functions[] = 'computePriceSubscription()';
				$this->page->addJavaScript($js);
				}
			else
				{
				$maxMembers = new \PHPFUI\Input\Hidden('maxMembers', (string)$maxMembersOnMembership);
				}
			$container->add($maxMembers);
			$container->add($totalDisplay);
			$container->add('<br>');
			$container->add(new \PHPFUI\Button('Subscribe', '/Membership/subscription'));
			$container->add($fieldSet);
			}
		$this->page->addJavaScript('function updateDonation() {' . \implode(';', $functions) . '}');

		return $container;
		}
	}
