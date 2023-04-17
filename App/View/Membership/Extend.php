<?php

namespace App\View\Membership;

class Extend implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);
		$extendButton = new \PHPFUI\Submit('Extend Memberships');
		$extendButton->setConfirm('Are you sure you want to extend memberships? It might not be easy to undo.');
		$testButton = new \PHPFUI\Submit('Test Extension');
		$testButton->addClass('info');

		if (\App\Model\Session::checkCSRF())
			{
			$extend = $extendButton->submitted($_POST);
			$message = new \PHPFUI\Container();

			if ($extend)
				{
				$status = 'success';
				$message->add(new \PHPFUI\Header('Memberships Extended', 5));
				$message->add('The following memberships HAVE BEEN extended:');
				}
			else
				{
				$status = 'alert';
				$message->add(new \PHPFUI\Header('Test Results', 5));
				$message->add('The following memberships would be extended:');
				}

			$membershipTable = new \App\Table\Membership();
			$firstOfMonth = \App\Tools\Date::firstOfMonth(\App\Tools\Date::today());
			$start = \App\Tools\Date::addMonths($firstOfMonth, (int)$_POST['lapsed']);
			$end = \App\Tools\Date::toString(\App\Tools\Date::addMonths($start, 1) - 1);
			$start = \App\Tools\Date::toString($start);
			$memberships = $membershipTable->getExpiringMemberships($start, $end);

			$table = new \PHPFUI\Table();
			$headers = ['Name', 'Old Expiration', 'New Expiration'];
			$table->setHeaders($headers);

			foreach ($memberships as $membership)
				{
				$row = ['Name' => $membership->firstName . ' ' . $membership->lastName];
				$row['Old Expiration'] = $membership->expires;

				$firstOfMonth = \App\Tools\Date::firstOfMonth(\App\Tools\Date::fromString($membership->expires));
				$start = \App\Tools\Date::addMonths($firstOfMonth, (int)$_POST['extend']);
				$membership->expires = \App\Tools\Date::toString(\App\Tools\Date::addMonths($start, 1) - 1);
				$row['New Expiration'] = $membership->expires;

				if ($extend)
					{
					$membershipRecord = new \App\Record\Membership($membership->toArray());
					$membershipRecord->update();
					}
				$table->addRow($row);
				}
			$message->add($table);

			\App\Model\Session::setFlash($status, "{$message}");
			\App\Model\Session::setFlash('post', $_POST);
			$this->page->redirect();

			return $form;
			}
		$post = \App\Model\Session::getFlash('post');

		$fieldSet = new \PHPFUI\FieldSet('Extend Memberships');
		$fieldSet->add('You can extend memberships based on the membership expiration date.');
		$ul = new \PHPFUI\UnorderedList();
		$ul->addItem(new \PHPFUI\ListItem('0 extends membership expiring this month.'));
		$ul->addItem(new \PHPFUI\ListItem('-1 extends memberships that expired last month'));
		$ul->addItem(new \PHPFUI\ListItem('1 extends memberships that will lapse next month.'));
		$fieldSet->add($ul);
		$monthsLapsed = new \PHPFUI\Input\Number('lapsed', 'Months Lapsed', $post['lapsed'] ?? 0);
		$monthsLapsed->setRequired()->setToolTip('0 extends membership expiring this month. -1 extends memberships that expired last month, 1 e');

		$monthsExtend = new \PHPFUI\Input\Number('extend', 'Months To Extend', $post['extend'] ?? 0);
		$monthsExtend->setRequired()->setToolTip('Enter the number of months to extend each membership.');

		$fieldSet->add(new \PHPFUI\MultiColumn($monthsLapsed, $monthsExtend));
		$form->add($fieldSet);

		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->add($extendButton);
		$buttonGroup->add($testButton);
		$form->add($buttonGroup);

		return "{$form}";
		}
	}
