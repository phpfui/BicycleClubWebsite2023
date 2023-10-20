<?php

namespace App\View\Member;

class Combine
	{
	private readonly \App\Model\Member $memberModel;

	private readonly \App\Table\Member $memberTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberTable = new \App\Table\Member();
		$this->memberModel = new \App\Model\Member();
		}

	public function combine() : string | \PHPFUI\Form | \PHPFUI\Container
		{
		if (isset($_POST['memberId']))
			{
			$memberId = $this->memberModel->combineMembers($_POST);
			$this->page->redirect('', 'combined=' . $memberId);
			}
		elseif (isset($_GET['submit']))
			{
			return $this->getSelectPage($_GET);
			}
		elseif (isset($_GET['combined']))
			{
			return $this->getCombinedPage(new \App\Record\Member($_GET['combined']));
			}
		else
			{
			return $this->getSearchPage();
			}

		return '';
		}

	private function getCombinedPage(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add(new \PHPFUI\SubHeader('Combined Member'));

		if ($member->loaded())
			{
			$view = new \App\View\Member($this->page);
			$container->add($view->show($member->membership->MemberChildren));
			}

		return $container;
		}

	private function getSearchPage() : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'GET');
		$fieldSet = new \PHPFUI\FieldSet('Enter Member Name To Find');
		$fieldSet->add(new \PHPFUI\Input\Text('name', 'Can be first, last or partial name'));
		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Search'));

		return $form;
		}

	private function getSelectPage(array $get) : \PHPFUI\Form
		{
		$members = $this->memberTable->findByName(\explode(' ', (string)$get['name']), false);

		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Instructions');
		$fieldSet->add('Select the member under the <b>Master</b> column you want to preserve. Then check each member you want to combine into the master member by checking
									 the <b>Combine</b> column. Empty fields in the master member will be filled by the combined member.  Master member fields will not be altered.
									 Combined members will be deleted.');
		$form->add($fieldSet);
		$table = new \PHPFUI\Table();
		$table->setHeaders(['Master', 'Combine', 'Name', 'address' => 'Address', 'Member Since', 'Expires', 'email']);

		foreach ($members as $member)
			{
			$member['Master'] = new \PHPFUI\Input\Radio('memberId', '', $member['memberId']);
			$member['Combine'] = new \PHPFUI\Input\CheckBoxBoolean('combined-' . $member['memberId']);
			$member['Name'] = $member['firstName'] . ' ' . $member['lastName'];
			$member['Member Since'] = $member['joined'];
			$member['Expires'] = $member['expires'];
			$table->addRow($member);
			}
		$form->add($table);
		$form->add(new \PHPFUI\Submit('Merge'));

		return $form;
		}
	}
