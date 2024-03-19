<?php

namespace App\WWW\Admin;

class Permission extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
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

	public function groupsWithPermission(\App\Record\Permission $permission = new \App\Record\Permission()) : void
		{
		if ($permission->empty())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Permission not found'));
			}
		elseif ($this->page->addHeader('Show Groups With Permission'))
			{
			$this->page->addSubHeader($permission->name);
			$view = new \App\View\Permissions($this->page);
			$this->page->addPageContent($view->groupsWithPermission($permission));
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

	public function roles() : void
		{
		if ($this->page->addHeader('Role Assignments'))
			{
			$assignmentView = new \App\View\Member\Assign($this->page);
			$this->page->addPageContent($assignmentView->getForm());
			}
		}
}
