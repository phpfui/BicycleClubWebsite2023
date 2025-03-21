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

	private readonly string $query;

	/**
	 * @var array<string,int|float|string>
	 */
	private array $requiredParameters = ['years' => 0, 'maxMembers' => 0, 'donation' => 0.0, 'itemDetail' => '', ];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Membership $membership, private readonly \App\View\Member $memberView)
		{
		$this->duesModel = new \App\Model\MembershipDues();
		$this->memberModel = new \App\Model\Member();
		$this->settingTable = new \App\Table\Setting();
		$this->members = \App\Table\Member::membersInMembership($membership->membershipId);
		$this->additionalMemberDues = \array_sum($this->duesModel->AdditionalMemberDues);
		$this->query = \http_build_query(\array_intersect_key($_POST, $this->requiredParameters));

		$this->processRequest($_GET);
		$this->processRequest($_POST);
		}

	public function checkout(\App\Record\Member $member, string $discountCodeEntered = '') : \PHPFUI\HTML5Element
		{
		$view = new \App\View\PayPal($this->page, new \App\Model\PayPal('Membership'));
		$output = new \PHPFUI\HTML5Element('div');
		$errors = $member->membership->validate();

		if (\App\Model\Session::checkCSRF() && ($_POST['submit'] ?? '') == 'Save')
			{
			$member->setFrom($_POST, ['firstName', 'lastName'])->update();
			$member->membership->setFrom($_POST, ['address', 'town', 'state', 'zip'])->update();

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
		$years = \max((int)($_GET['years'] ?? 1), 0);
		$additionalMembers = \count($this->members) - 1;
		$maxMembers = \max((int)($_GET['maxMembers'] ?? 1), 1);
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
			$text = "<br>You have {$additionalMembers} additional member";

			if ($additionalMembers > 1)
				{
				$text .= 's';
				}
			$output->add($text);
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

		$discountCode = new \App\Record\DiscountCode(['discountCode' => $discountCodeEntered]);
		$discountCodeModel = new \App\Model\DiscountCode($discountCode);
		$discountCodeTable = new \App\Table\DiscountCode();

		$validDiscountCode = new \App\Record\DiscountCode();
		$validDiscountCodes = $discountCodeTable->getActiveMembershipCodes();

		foreach($validDiscountCodes as $validCode)
			{
			if ($discountCodeEntered == $validCode->discountCode)
				{
				$validDiscountCode = $discountCode;
				}
			}

		if (! $validDiscountCode->empty())
			{
			$discount = $this->computeDiscount($validDiscountCode, $years, $additionalMembers);
			$unpaidBalance -= $discount;
			$output->add('<br>Discount Applied $' . \number_format($discount, 2));
			}

		$donation = (float)($_GET['donation'] ?? 0.0);
		$unpaidBalance += $donation;

		if ($unpaidBalance <= 0.0)
			{
			\App\Model\Session::setFlash('alert', 'You have not specified a donation amount');

			$this->page->redirect('/Membership/renew');
			}

		if (! $this->duesModel->disableDonations)
			{
			$output->add('<br>Additional Donation $' . \number_format($donation, 2));
			}
		$output->add('<p><b>Total Due : $' . \number_format($unpaidBalance, 2) . '</b>');
		$invoice = $this->memberModel->getRenewInvoice($member, $additionalMembers, $unpaidBalance, $years, $donation, $_GET['itemDetail'] ?? '', $discountCode);
		$output->add($view->getCheckoutForm($invoice, $output->getId(), 'Membership Renewal'));

		return $output;
		}

	public function renew(string $discountCodeEntered = '') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! $this->memberModel->hasValidAddress($this->membership))
			{
			$content = new \App\View\Content($this->page);
			$container->add($content->getDisplayCategoryHTML('Address Required'));
			$form = new \PHPFUI\Form($this->page);

			if (\App\Model\Session::checkCSRF())
				{
				$this->membership->setFrom($_POST, ['address', 'town', 'state', 'zip']);
				$this->membership->update();
				$this->page->redirect(parameters:$this->query);
				}
			$form->add($this->memberView->getAddress($this->membership));
			$form->add(new \PHPFUI\Submit('Save and Continue'));
			$form->add(new \PHPFUI\Input\Hidden('membershipId', (string)$this->membership->membershipId));
			$container->add($form);

			return $container;
			}

		$form = new \PHPFUI\Form($this->page);

		$discountCode = new \App\Record\DiscountCode(['discountCode' => $discountCodeEntered]);
		$discountCodeModel = new \App\Model\DiscountCode($discountCode);
		$discountCodeTable = new \App\Table\DiscountCode();

		$validDiscountCode = new \App\Record\DiscountCode();
		$validDiscountCodes = $discountCodeTable->getActiveMembershipCodes();

		foreach($validDiscountCodes as $validCode)
			{
			if ($discountCodeEntered == $validCode->discountCode)
				{
				$validDiscountCode = $discountCode;
				}
			}

		if (\PHPFUI\Session::checkCSRF())
			{
			$years = (int)($_POST['years'] ?? 0);
			$maxMembers = (int)($_POST['maxMembers'] ?? 1);
			$price = $this->duesModel->getTotalMembershipPrice($maxMembers, $years);
			$price -= $this->computeDiscount($validDiscountCode, $years, $maxMembers - 1);
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

		$numberMembers = \count($this->members);

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
					$delete = new \PHPFUI\FAIcon('far', 'trash-alt', $url . '?deleteMember=' . $member['memberId']);
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

		if (0 === $maxMembersOnMembership || $maxMembersOnMembership > \count($this->members))
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
			$multiColumn = new \PHPFUI\MultiColumn();
			$yearlyRenewal = new \PHPFUI\FieldSet('Yearly Renewal');
			$yearsField = new \PHPFUI\Input\Select('years', 'Number of years to renew');

			if (! $this->duesModel->disableDonations)
				{
				$yearsField->addOption('No Years, Donation only', '0');
				}

			$_GET['years'] = (int)($_GET['years'] ?? 1);

			for ($i = 1; $i <= (int)$this->duesModel->MaxRenewalYears; ++$i)
				{
				$yearsField->addOption("{$i} Year" . (($i > 1) ? 's' : ''), (string)$i, $_GET['years'] == $i);
				}
			$yearsField->addAttribute('onchange', 'updatePrice();');
			$multiColumn->add($yearsField);

			if ($this->additionalMemberDues && $maxMembersOnMembership > 1)
				{
				$maxMembersField = new \PHPFUI\Input\Select('maxMembers', 'Number of members on your membership');

				if (0 !== $maxMembersOnMembership) // @phpstan-ignore-line
					{
					$maxMembersOnMembership = 10;
					}

				$_GET['maxMembers'] = (int)($_GET['maxMembers'] ?? 1);

				for ($i = $numberMembers; $i <= $maxMembersOnMembership; ++$i)
					{
					$maxMembersField->addOption("{$i} Member" . (($i > 1) ? 's' : ''), (string)$i, $_GET['maxMembers'] == $i);
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

			if ($validDiscountCodes->count())
				{
				$form->add(new \App\UI\DiscountCode($validDiscountCode, $discountCodeEntered));
				}

			if (! $this->duesModel->disableDonations)
				{
				$donationSet = new \PHPFUI\FieldSet('Optional Additional Donation');
				$multiColumn = new \PHPFUI\MultiColumn();
				$multiColumn->add($this->settingTable->value('donationText'));
				$donation = new \PHPFUI\Input\Text('donation', 'Donation Amount', $_GET['donation'] ?? '0');
				$donationId = $donation->getId();
				$donation->setToolTip('Your donation will be added to your membership dues.');
				$donation->addAttribute('onchange', 'updatePrice()');
				$multiColumn->add($donation);
				$donationSet->add($multiColumn);
				$donationSet->add(new \PHPFUI\Input\Text('itemDetail', 'Donation notes or dedications', $_GET['itemDetail'] ?? ''));
				$form->add($donationSet);
				}

			$yearId = $yearsField->getId();
			$maxMembersId = $maxMembersField->getId();
			$form->add($totalDisplay);

			$form->add('<br>');
			$form->add(new \PHPFUI\Submit('Confirm and Pay'));
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
				if (0 === $maxMembersOnMembership)
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
var formData=form.serialize();
if (! formData.includes('years')) return;
$.ajax({type:'POST', dataType:'html', data:formData,
success:function(response){var data;try{data=JSON.parse(response);}catch(e){alert('Error: '+response);}
{$dollar}('#{$totalDueId}').html('<b>$'+data.response+'</b>')}});};updatePrice();
JAVASCRIPT;
		$this->page->addJavaScript($js);

		return $container;
		}

	private function computeDiscount(\App\Record\DiscountCode $discountCode, int $years, int $additionalMembers) : float
		{
		$discountCodeModel = new \App\Model\DiscountCode($discountCode);

		$items = [];
		$invoiceItem = new \App\Record\InvoiceItem();
		$invoiceItem->storeItemId = 1;
		$invoiceItem->storeItemDetailId = \App\Model\Member::FIRST_MEMBERSHIP;

		if ($years)
			{
			$invoiceItem->price = (float)\number_format($this->duesModel->getMembershipPriceByYear($years), 2);
			}
		$invoiceItem->quantity = $years;

		$items[] = $invoiceItem->toArray();

		$paidMembers = $this->duesModel->PaidMembers;

		if ('Unlimited' == $paidMembers)
			{
			$additionalMembers = 0;
			}
		elseif ('Family' == $paidMembers && $additionalMembers)
			{
			$additionalMembers = 1;
			}

		$additionalMemberDues = $this->duesModel->getAdditionalMembershipPrice($additionalMembers + 1, $years);

		if ($additionalMemberDues > 0.0 && $additionalMembers)
			{
			$invoiceItem->storeItemId = 1;
			$invoiceItem->storeItemDetailId = \App\Model\Member::ADDITIONAL_MEMBERSHIP;
			$invoiceItem->price = (float)\number_format($additionalMemberDues / $additionalMembers, 2);
			$invoiceItem->quantity = $additionalMembers;
			$items[] = $invoiceItem->toArray();
			}
		$total = 0;

		foreach ($items as $item)
			{
			$total += $item['price'] * $item['quantity'];
			}

		return $discountCodeModel->computeDiscount($items, $total);
		}

	/**
	 * @param array<string,array<string,string>|string> $parameters
	 */
	private function processRequest(array $parameters) : void
		{
		if (isset($parameters['deleteMember']))
			{
			$delete = (int)($parameters['deleteMember']);

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
			$this->page->redirect(parameters:$this->query);
			}
		elseif (isset($parameters['submit']) && \App\Model\Session::checkCSRF())
			{
			$redirect = '';

			switch ($parameters['submit'])
				{
				case 'Confirm and Pay':
					$redirect = '/Membership/renewCheckout/' . $parameters['discountCode'] ?? '';

					break;

				/** @noinspection PhpMissingBreakStatementInspection */
				case 'Remove':
					$parameters['discountCode'] = '';

					// Intentionally fall through
				case 'Apply':
					$redirect = '/Membership/renew/' . $parameters['discountCode'];

					break;
				}
			$this->page->redirect($redirect, $this->query);
			}
		}
	}
