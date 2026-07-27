<?php

namespace App\View\Membership;

class Combine
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function combine() : string | \PHPFUI\Container | \PHPFUI\Form
		{
		if (isset($_POST['submit']) && $_POST['submit'] == 'Combine Memberships')
			{
			$memberModel = new \App\Model\Member();
			$membershipId = $memberModel->combineMembership($_POST);
			if ($membershipId)
				{
				$this->page->redirect('', 'combined=' . $membershipId);
				}
			else
				{
				$this->page->redirect();
				}
			}
		elseif (isset($_GET['combined']))
			{
			return $this->getCombinedPage((int)$_GET['combined']);
			}
		else
			{
			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);

			$fieldSet = new \PHPFUI\FieldSet('Instructions');
			$fieldSet->add('Search for duplicate memberships by address and / or name. You can filter and sort by different columns to help identify duplicates.<br><br>');
			$fieldSet->add('Then select the membership under the <b>Master</b> column you want to combine members into. Then check each membership you want to move into that membership by checking
									 the <b>Combine</b> column.');
			$form->add($fieldSet);
			$combine = new \PHPFUI\Submit('Combine Memberships');
			$form->add($combine);
			$form->add($this->show());

			return $form;
			}

		return '';
		}

	private function getCombinedPage(int $membershipId) : \PHPFUI\Container
		{
		$form = new \PHPFUI\Container();
		$form->add(new \PHPFUI\SubHeader('Combined Members'));
		$members = new \App\Table\Member()->membersInMembership($membershipId);

		if (\count($members))
			{
			$view = new \App\View\Member($this->page);
			$form->add($view->show($members));
			}

		return $form;
		}

	public function show() : \App\UI\ContinuousScrollTable
		{
		$memberTable = new \App\Table\Member();
		$memberTable->setJoin('membership');
		$memberTable->setGroupBy('membershipId');

		$view = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
		$sortableHeaders = ['address' => 'Address', 'firstName' => 'First Name', 'lastName' => 'LastName', 'joined' => 'Member Since', 'expires' => 'Expires'];

		$otherHeaders = ['master' => 'Master', 'combine' => 'Combine', ];

		$view->addCustomColumn('master', static fn (array $member) : \PHPFUI\Input\Radio => new \PHPFUI\Input\Radio('master', '', (string)$member['membershipId'])->setId('radio' . $member['membershipId']));

		$view->addCustomColumn('combine', static fn (array $member) : \PHPFUI\Input\CheckBox => new \PHPFUI\Input\CheckBox('combine-' . $member['memberId'], ''));

		$view->setHeaders(\array_merge($otherHeaders, $sortableHeaders))->setSortableColumns(\array_keys($sortableHeaders));
		$view->setSearchColumns(\array_keys($sortableHeaders));

		return $view;
		}

	}
