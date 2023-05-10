<?php

namespace App\WWW;

class Admin extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function addGroup() : void
		{
		if ($this->page->addHeader('Add Permission Group'))
			{
			$permission = $this->page->getPermissions()->addGroup();
			$this->page->redirect('/Admin/groupEdit/' . $permission->permissionId);
			}
		}

	public function bikeShopAreas() : void
		{
		if ($this->page->addHeader('Bike Shop Areas'))
			{
			$this->page->addPageContent(new \App\View\Admin\BikeShopAreas($this->page));
			}
		}

	public function bikeShopEdit(\App\Record\BikeShop $bikeShop = new \App\Record\BikeShop()) : void
		{
		if ($this->page->addHeader('Bike Shop Edit'))
			{
			$view = new \App\View\Admin\BikeShop($this->page);
			$this->page->addPageContent($view->edit($bikeShop));
			}
		}

	public function bikeShopList() : void
		{
		if ($this->page->addHeader('Bike Shop Maintenance'))
			{
			$view = new \App\View\Admin\BikeShop($this->page);
			$this->page->addPageContent($view->list());
			}
		}

	public function blackList() : void
		{
		if ($this->page->addHeader('Email Blacklist'))
			{
			$view = new \App\View\Admin\BlackList($this->page);
			$this->page->addPageContent($view->emails());
			}
		}

	public function board() : void
		{
		if ($this->page->addHeader('Edit Board Members'))
			{
			$view = new \App\View\Admin\Board($this->page);
			$this->page->addPageContent($view->editView());
			}
		}

	public function boardMember(\App\Record\BoardMember $member = new \App\Record\BoardMember()) : void
		{
		if ($this->page->addHeader('Edit Board Member'))
			{
			if ($member->loaded())
				{
				$view = new \App\View\Admin\Board($this->page);
				$this->page->addPageContent($view->editMember($member));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member Not Found'));
				}
			}
		}

	public function clubEmails() : void
		{
		if ($this->page->addHeader('Club Email Addresses'))
			{
			$this->page->addPageContent(new \App\View\Admin\SystemEmail($this->page));
			}
		}

	public function config() : void
		{
		if ($this->page->addHeader('Site Configuration'))
			{
			$view = new \App\View\Admin\Configuration($this->page);
			$this->page->addPageContent($view->site());
			}
		}

	public function downloadWaivers() : void
		{
		if ($this->page->isAuthorized('Waiver Editor'))
			{
			$waiver = new \App\Report\MemberWaiver();
			$memberTable = new \App\Table\Member();
			$currentMembers = $memberTable->getAllMembers(\App\Tools\Date::todayString());

			foreach ($currentMembers as $member)
				{
				$waiver->generate($member);
				}
			$waiver->output('AllMemberWaivers' . \App\Tools\Date::todayString() . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
			}
		}

	public function editWaiver() : void
		{
		if ($this->page->addHeader('Waiver Editor'))
			{
			$view = new \App\View\Admin\Waiver($this->page);
			$this->page->addPageContent($view->edit());
			}
		}

	public function emailQueue() : void
		{
		if ($this->page->addHeader('Email Queue'))
			{
			$view = new \App\View\Admin\EmailQueue($this->page);
			$this->page->addPageContent($view->getQueue());
			}
		}

	public function files() : void
		{
		if ($this->page->addHeader('Manage Files'))
			{
			$fileView = new \App\View\Admin\Files($this->page, new \App\Model\PDFFile());
			$this->page->addPageContent($fileView->list());
			}
		}

	public function groupEdit(\App\Record\Permission $permission = new \App\Record\Permission()) : void
		{
		if ($permission->loaded() && $this->page->addHeader('Edit Permission Group'))
			{
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->editPermissionGroup($permission));
			}
		}

	public function groupMembers(\App\Record\Permission $permission = new \App\Record\Permission()) : void
		{
		if ($permission->loaded() && $this->page->addHeader('Members In Group'))
			{
			$this->page->addSubHeader($permission->name);
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->membersWithPermission($permission));
			}
		}

	public function images() : void
		{
		if ($this->page->addHeader('System Images'))
			{
			$view = new \App\View\Admin\Images($this->page);
			$this->page->addPageContent($view->getSettings());
			}
		}

	public function journalQueue() : void
		{
		if ($this->page->addHeader('Journal Queue'))
			{
			$view = new \App\View\Admin\JournalQueue($this->page);
			$this->page->addPageContent($view->getQueue());
			}
		}

	public function myPermissions() : void
		{
		if ($this->page->addHeader('My Permissions'))
			{
			$permissions = \App\Table\UserPermission::forMember(\App\Model\Session::signedInMemberId());
			$misc = [];
			$accordion = new \App\UI\Accordion();

			foreach ($permissions as $permission)
				{
				$text = empty($permission['menu']) ? $permission['name'] : $permission['menu'] . ' - ' . $permission['name'];

				if ($permission['permissionGroup'] < 100000)
					{
					$groupPermissions = \App\Table\PermissionGroup::getGroupPermissions($permission['permissionGroup']);
					$list = '';

					foreach ($groupPermissions as $name)
						{
						$name = empty($name['menu']) ? $name['name'] : $name['menu'] . ' - ' . $name['name'];
						$list .= $name . '<br>';
						}
					$accordion->addTab($text, $list);
					}
				else
					{
					$misc[] = $text;
					}
				}

			if (\count($misc))
				{
				$text = '';

				foreach ($misc as $single)
					{
					$text .= $single . '<br>';
					}
				$accordion->addTab('Miscellaneous', $text);
				}
			$this->page->addPageContent($accordion);
			}
		}

	public function passwordPolicy() : void
		{
		if ($this->page->addHeader('Password Policy'))
			{
			$view = new \App\View\Admin\PasswordPolicy($this->page);
			$this->page->addPageContent($view->edit());
			}
		}

	public function permissionGroupAssignment() : void
		{
		if ($this->page->addHeader('Permission Group Assignments'))
			{
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->groupAssignments());
			}
		}

	public function permissionGroups() : void
		{
		if ($this->page->addHeader('Permission Groups'))
			{
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->getAllGroups());
			}
		}

	public function permissionMembers(\App\Record\Permission $permission = new \App\Record\Permission()) : void
		{
		if ($permission->empty())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Permission not found'));
			}
		elseif ($this->page->addHeader('Show Members With Permission'))
			{
			$this->page->addSubHeader($permission->name);
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->membersWithPermission($permission));
			}
		}

	public function permissions() : void
		{
		if ($this->page->addHeader('Permissions'))
			{
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->getAllPermissions());
			}
		}

	public function publicEdit(\App\Record\PublicPage $publicPage = new \App\Record\PublicPage()) : void
		{
		if ($this->page->addHeader('Edit Public Pages'))
			{
			if (! $publicPage->empty())
				{
				$view = new \App\View\Admin\PublicPageEditor($this->page);
				$this->page->addPageContent($view->edit($publicPage));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Public page not found'));
				}
			}
		}

	public function publicPage() : void
		{
		if ($this->page->addHeader('Edit Public Pages'))
			{
			$publicPageTable = new \App\Table\PublicPage();
			$publicPageTable->addOrderBy('sequence');
			$publicPage = new \App\View\Admin\PublicPageEditor($this->page);
			$this->page->addPageContent($publicPage->list($publicPageTable));
			}
		}

	public function resetWaivers() : void
		{
		if ($this->page->isAuthorized('Waiver Editor'))
			{
			$memberTable = new \App\Table\Member();
			$memberTable->update(['acceptedWaiver' => null]);
			\App\Model\Session::setFlash('success', 'All member waivers reset.');
			$this->page->redirect('/Admin/editWaiver');
			}
		}
}
