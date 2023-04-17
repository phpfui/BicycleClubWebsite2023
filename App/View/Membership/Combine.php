<?php

namespace App\View\Membership;

class Combine
	{
	private readonly \App\Model\Member $memberModel;

	private readonly \App\Table\Member $memberTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberTable = new \App\Table\Member();
		$this->memberModel = new \App\Model\Member();
		}

	public function combine() : string | \PHPFUI\Container | \PHPFUI\Form
		{
		if (isset($_POST['membershipId']))
			{
			$membershipId = $this->memberModel->combineMembership($_POST);
			$this->page->redirect('', 'combined=' . $membershipId);
			}
		elseif (isset($_GET['submit']))
			{
			return $this->getSelectPage($_GET);
			}
		elseif (isset($_GET['combined']))
			{
			return $this->getCombinedPage((int)$_GET['combined']);
			}
		else
			{
			return $this->getSearchPage();
			}

		return '';
		}

	private function getCombinedPage(int $membershipId)
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader('Combined Members'));
		$members = $this->memberTable->membersInMembership($membershipId);

		if (\count($members))
			{
			$view = new \App\View\Member($this->page);
			$container->add($view->show($members));
			}

		return $container;
		}

	private function getSearchPage(string $search = '') : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAttribute('method', 'GET');
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Enter Address To Find');
		$fieldSet->add(new \PHPFUI\Input\Text('address', 'Can be partial address', $search));
		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Search'));

		return $form;
		}

	private function getSelectPage(array $get)
		{
		$get['all'] = true;
		$members = $this->memberTable->find($get);

		if (! \count($members))
			{
			$container = new \PHPFUI\Container();
			$alert = new \App\UI\Alert('No match, please try again');
			$alert->addClass('warning');
			$container->add($alert);
			$container->add($this->getSearchPage($get['address']));

			return $container;
			}
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Instructions');
		$fieldSet->add('Select the membership under the <b>Master</b> column you want to combine members into. Then check each member you want to move into that membership by checking
									 the <b>Combine</b> column.');
		$form->add($fieldSet);
		$table = new \PHPFUI\Table();
		$table->setHeaders(['Master', 'Combine', 'Name', 'address' => 'Address', 'Member Since', 'Expires', 'email']);

		foreach ($members as $member)
			{
			$row = $member->toArray();
			$row['Master'] = new \PHPFUI\Input\Radio('membershipId', '', $member->membershipId);
			$row['Combine'] = new \PHPFUI\Input\CheckBox('memberId-' . $member->memberId, '', 1);
			$row['Name'] = $member->firstName . ' ' . $member->lastName;
			$row['Member Since'] = $member->joined;
			$row['Expires'] = $member->expires;
			$table->addRow($row);
			}
		$form->add($table);
		$form->add(new \PHPFUI\Submit('Merge'));

		return $form;
		}
	}
