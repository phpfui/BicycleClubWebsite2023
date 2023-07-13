<?php

namespace App\View\Membership;

class Dues
	{
	private readonly \App\Model\MembershipDues $duesModel;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->duesModel = new \App\Model\MembershipDues();
		}

	public function getForm() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		$fieldSet = new \PHPFUI\FieldSet('Membership Settings');
		$multiColumn = new \PHPFUI\MultiColumn();
		$memberTerm = new \PHPFUI\Input\RadioGroup('MembershipTerm', 'Membership Term', $this->duesModel->MembershipTerm);
		$memberTerm->addButton('Annual');
		$memberTerm->addButton('12 Months');
		$memberTerm->setRequired()->setToolTip('Annual membership terms all renew on the same month. 12 month memberships are good for 12 months from date of joining.');
		$startMonth = new \App\UI\Month('MembershipStartMonth', 'Membership Start Month', $this->duesModel->MembershipStartMonth);
		$startMonth->setToolTip('For annual memberships, The month when all the memberships renew');
		$graceMonth = new \App\UI\Month('MembershipGraceMonth', 'Membership Grace Month', $this->duesModel->MembershipGraceMonth);
		$graceMonth->setToolTip('For annual memberships, if joining after this month, membership is good through the end of the next renwal period');
		$fieldSet->add(new \PHPFUI\MultiColumn($memberTerm, $startMonth, $graceMonth));

//		$renewalType = new \PHPFUI\Input\RadioGroup('MembershipType', 'Manual', $this->duesModel->MembershipType);
//		$renewalType->addButton('Manual Renewal', 'Manual');
//		$renewalType->addButton('Subscription', 'Subscription');
//		$renewalType->addButton('Both', 'Both');
//		$fieldSet->add($renewalType);
//		$form->add($fieldSet);

//		$subscriptionDues = new \PHPFUI\Input\Number('subscriptionDues', 'Subscription Dues', $this->duesModel->SubscriptionDues);

		$memberType = new \PHPFUI\Input\RadioGroup('PaidMembers', 'Membership Type', $this->duesModel->PaidMembers);
		$memberType->addButton('Unlimited', 'Unlimited');
		$memberType->addButton('Paid Only', 'Paid');
		$memberType->addButton('Family (two paid)', 'Family');
		$memberType->setRequired();

		$maxMembersOnMembership = new \PHPFUI\Input\Number('MaxMembersOnMembership', 'Max Members On Membership', $this->duesModel->MaxMembersOnMembership);
		$maxMembersOnMembership->setRequired(false)->setToolTip('You can limit total members on a membership, for family membership, all members above 2 are free');

		$fieldSet->add(new \PHPFUI\MultiColumn($memberType, $maxMembersOnMembership));

		$form->add($fieldSet);

		$this->page->addCSS('table {counter-reset: row-num -1;}table tr {counter-increment: row-num;}table tr td:first-child::before {content: counter(row-num);}');

		$fieldSet = new \PHPFUI\FieldSet('Membership Pricing');

		$table = new \PHPFUI\Table();
		$table->setHeaders(['Years', 'Annual Dues', 'Additional Member', 'Del']);
		$trash = new \PHPFUI\FAIcon('far', 'trash-alt');
		$trash->addAttribute('onclick', '$(this).parent().parent().css("background-color","red").hide("fast").remove()');
		$trashColumn = '';

		$dues = $this->duesModel->AnnualDues;
		$annualDues = new \PHPFUI\Input\Number('AnnualDues[]');
		$annualDues->addAttribute('pattern', 'number');

		$additionalDues = $this->duesModel->AdditionalMemberDues;
		$additionalMemberDues = new \PHPFUI\Input\Number('AdditionalMemberDues[]');
		$additionalMemberDues->addAttribute('pattern', 'number');

		foreach ($dues as $key => $value)
			{
			if (! \is_string($value) || 0 == \strlen($value))
				{
				break;
				}
			$annualDues->setValue($value);
			$additionalMemberDues->setValue($additionalDues[$key] ?? '0');
			$table->addRow(['Annual Dues' => clone $annualDues, 'Additional Member' => clone $additionalMemberDues, 'Del' => $trashColumn, ]);
			$trashColumn = $trash;
			}

		for ($i = 0; $i < 10; ++$i)
			{
			$table->addNextRowAttribute('hidden', '');
			$annualDues->setValue('');
			$additionalMemberDues->setValue('');
			$table->addRow(['Annual Dues' => $annualDues, 'Additional Member' => $additionalMemberDues, 'Del' => $trash, ]);
			}

		$fieldSet->add($table);
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$this->duesModel->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \PHPFUI\FormError());
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$insertRow = new \PHPFUI\Button('Add Pricing Row');
			$insertRow->addClass('warning');
			$js = '$("#' . $table->getId() . '").find("tr:hidden:first").removeAttr("hidden")';
			$insertRow->addAttribute('onclick', $js);
			$buttonGroup->addButton($insertRow);
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
