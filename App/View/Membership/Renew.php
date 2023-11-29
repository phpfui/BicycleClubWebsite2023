<?php

namespace App\View\Membership;

class Renew
	{
	private float $additionalMemberDues = 0;

	private readonly \App\Model\MembershipDues $duesModel;

	private readonly \App\Model\Member $memberModel;

	/**
	 * @var \PHPFUI\ORM\RecordCursor<\App\Record\Member>
	 */
	private readonly \PHPFUI\ORM\RecordCursor $members;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Membership $membership, private readonly \App\View\Member $memberView)
		{
		$this->duesModel = new \App\Model\MembershipDues();
		$this->memberModel = new \App\Model\Member();
		$this->settingTable = new \App\Table\Setting();
		$this->members = \App\Table\Member::membersInMembership($membership->membershipId);
		$this->additionalMemberDues = \array_sum($this->duesModel->AdditionalMemberDues);

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

		$paidMembers = $this->duesModel->PaidMembers;

		if ($years > 1)
			{
			$output->add("<br>You elected to renew for {$years} years");
			}
		$output->add("<br>{$paidMembers} Membership is $" . $this->duesModel->getMembershipPriceByYear($years) . ' every 12 months.');

		if ($additionalMembers && $this->additionalMemberDues)
			{
			$output->add('<br>Additional members are $' . $this->duesModel->getAdditionalMemberPriceByYear($years) . ' per year.');
			$output->add("<br>You have {$additionalMembers} additional member");

			if ($additionalMembers > 1)
				{
				$output->add('s');
				}
			}

		if ('Family' == $paidMembers && $additionalMembers > 0)
			{
			$additionalMembers = \count($this->members);

			if ($additionalMembers > 2)
				{
				$additionalMembers -= 2;
				}
			else
				{
				$additionalMembers = 0;
				}
			}
		elseif ('Unlimited' == $paidMembers)
			{
			$additionalMembers = 0;
			}

		if ($years)
			{
			$unpaidBalance = $this->duesModel->getTotalMembershipPrice(\count($this->members), $years);
			}
		else
			{
			$unpaidBalance = $additionalMembers * $this->duesModel->getAdditionalMemberPriceByYear(1);
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

		$form = new \PHPFUI\Form($this->page);

		if (\PHPFUI\Session::checkCSRF())
			{
			$years = (int)($_POST['years'] ?? 0);
			$maxMembers = (int)($_POST['maxMembers'] ?? 1);
			$price = $this->duesModel->getTotalMembershipPrice($maxMembers, $years);
			$price += (float)($_POST['donation'] ?? 0);
			$this->page->setResponse(\number_format($price, 2));

			return $container;
			}

		$paidMembers = $this->duesModel->PaidMembers;
		$renewalType = $this->duesModel->MembershipType;

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

		$maxMembersOnMembership = (int)$this->duesModel->MaxMembersOnMembership;

		if (! $maxMembersOnMembership || $maxMembersOnMembership > \count($this->members))
			{
			// show add member button
			$container->add($this->memberView->getAddMemberModalButton($this->membership));
			}

		$dollar = '$';

		$membershipRates = new \PHPFUI\FieldSet('Membership Rates');

		$table = new \PHPFUI\Table();
		$dues = $this->duesModel->AnnualDues;
		$additionalDues = $this->duesModel->AdditionalMemberDues;

		$headers = [];
		$numberYears = \count($dues);

		if ($numberYears > 1)
			{
			$headers['Renewal Term (Years)'] = 'Renewal Term (Years)';
			}

		switch ($paidMembers)
			{
			case 'Unlimited':
				$headers['Cost Per Year'] = 'Cost Per Year';

				break;

			case 'Paid':
				$headers['Cost Per Year'] = 'Cost Per Year';

				if (\array_sum($additionalDues))
					{
					$headers['Additional Member Dues'] = 'Additional Member Dues';
					}

				break;

			case 'Family':
				$headers['Annual Family (2 members) Dues'] = 'Annual Family (2 members) Dues';

				if (\array_sum($additionalDues))
					{
					$headers['Additional Member Dues'] = 'Additional Member Dues';
					}

				break;
			}
		$table->setHeaders($headers);

		foreach ($dues as $year => $amount)
			{
			$row = [];

			if ($numberYears > 1)
				{
				$years = $year + 1;

				if ($years >= $numberYears && $numberYears < (int)$this->duesModel->MaxRenewalYears)
					{
					$years = "{$years}+";
					}
				$row['Renewal Term (Years)'] = $years;
				}

			$row['Cost Per Year'] = $row['Annual Family (2 members) Dues'] = '$' . \number_format((float)$amount, 2);
			$row['Additional Member Dues'] = '$' . \number_format((float)($additionalDues[$year] ?? 0.0), 2);
			$table->addRow($row);
			}
		$membershipRates->add($table);
		$container->add($membershipRates);

		$totalDue = new \PHPFUI\HTML5Element('div');
		$totalDueId = $totalDue->getId();
		$totalDisplay = new \PHPFUI\MultiColumn('<b>Total Due</b>', $totalDue);

		if ('Manual' == $renewalType || 'Both' == $renewalType)
			{
			$form->setAttribute('action', '/Membership/renewCheckout');
			$multiColumn = new \PHPFUI\MultiColumn();
			$yearlyRenewal = new \PHPFUI\FieldSet('Yearly Renewal');
			$yearsField = new \PHPFUI\Input\Select('years', 'Number of years to renew');
			$yearsField->addOption('No Years, Donation only', '0');

			for ($i = 1; $i <= (int)$this->duesModel->MaxRenewalYears; ++$i)
				{
				$yearsField->addOption("{$i} Year" . (($i > 1) ? 's' : ''), (string)$i, 1 == $i);
				}
			$yearsField->addAttribute('onchange', 'updatePrice();');
			$multiColumn->add($yearsField);

			if ($this->additionalMemberDues && $maxMembersOnMembership > 1)
				{
				$maxMembersField = new \PHPFUI\Input\Select('maxMembers', 'Number of members on your membership');

				if (! $maxMembersOnMembership) // @phpstan-ignore-line
					{
					$maxMembersOnMembership = 10;
					}

				for ($i = $numberMembers; $i <= $maxMembersOnMembership; ++$i)
					{
					$maxMembersField->addOption("{$i} Member" . (($i > 1) ? 's' : ''), (string)$i, $i == $numberMembers);
					}
				$maxMembersField->addAttribute('onchange', 'updatePrice()');
				$multiColumn->add($maxMembersField);
				}
			else
				{
				$maxMembersField = new \PHPFUI\Input\Hidden('maxMembers', '1');
				$yearlyRenewal->add($maxMembersField);
				}
			$yearlyRenewal->add($multiColumn);
			$form->add($yearlyRenewal);

			$donationSet = new \PHPFUI\FieldSet('Optional Additional Donation');
			$multiColumn = new \PHPFUI\MultiColumn();
			$multiColumn->add($this->settingTable->value('donationText'));
			$donation = new \PHPFUI\Input\Text('donation', 'Donation Amount', '0');
			$donationId = $donation->getId();
			$donation->setToolTip('Your donation will be added to your membership dues.');
			$donation->addAttribute('onchange', 'updatePrice()');
			$multiColumn->add($donation);
			$donationSet->add($multiColumn);
			$donationSet->add(new \PHPFUI\Input\Text('itemDetail', 'Donation notes or dedications', ''));
			$form->add($donationSet);

			$yearId = $yearsField->getId();
			$maxMembersId = $maxMembersField->getId();
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
				$maxMembersField = new \PHPFUI\Input\Select('MaxMembersSubscription', 'Number of members on your membership');

				for ($i = $numberMembers; $i <= $maxMembersOnMembership; ++$i)
					{
					$maxMembersField->addOption("{$i} Member" . (($i > 1) ? 's' : ''), (string)$i, $i == $numberMembers);
					}
				$maxMembersField->addAttribute('onchange', 'updatePrice()');
				$maxMembersId = $maxMembersField->getId();
				}
			else
				{
				$maxMembersField = new \PHPFUI\Input\Hidden('maxMembers', (string)$maxMembersOnMembership);
				}
			$container->add($maxMembersField);
			$container->add($totalDisplay);
			$container->add('<br>');
			$container->add(new \PHPFUI\Button('Subscribe', '/Membership/subscription'));
			$container->add($fieldSet);
			}
		$dollar = '$';
		$formId = $form->getId();

		$js = <<<JAVASCRIPT
function updatePrice(){var form={$dollar}('#{$formId}');
$.ajax({type:'POST',dataType:'html',data:form.serialize(),
success:function(response){var data;try{data=JSON.parse(response);}catch(e){alert('Error: '+response);}
{$dollar}('#{$totalDueId}').html('<b>$'+data.response+'</b>')}});};updatePrice();
JAVASCRIPT;
		$this->page->addJavaScript($js);

		return $container;
		}
	}
