<?php

namespace App\View;

class Coordinators
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function getEmail(string $type) : string
		{
		$chair = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker($type));

		return $chair->getEditControl();
		}

	public function getRideCoordinators(?\PHPFUI\Button $backButton = null) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$categoryTable = new \App\Table\Category();
		$categories = $categoryTable->getAllCategories();

		if ($form->isMyCallback())
			{
			\PHPFUI\ORM::beginTransaction();
			$userPermissionTable = new \App\Table\UserPermission();
			// nuke all coordinator permissions
			$settingTable = new \App\Table\Setting();
			$coordinatorPermission = $settingTable->getStandardPermissionGroup('Ride Coordinator');
			$userPermissionTable->setWhere(new \PHPFUI\ORM\Condition('permissionGroup', $coordinatorPermission->permissionId));
			$userPermissionTable->delete();
			// add back in
			$permission = ['permissionGroup' => $coordinatorPermission->permissionId];

			$userPermission = new \App\Record\UserPermission();

			foreach ($categories as $categoryRecord)
				{
				$categoryRecord->coordinator = (int)$_POST['coordinator' . $categoryRecord->categoryId];
				$categoryRecord->update();

				if (! $categoryRecord->coordinator)
					{
					continue;
					}
				$userPermission->memberId = $categoryRecord->coordinator;
				$userPermission->revoked = 0;
				$userPermission->permissionGroup = $coordinatorPermission->permissionId;
				$userPermission->insertOrUpdate();
				}
			\PHPFUI\ORM::commit();
			$this->page->setResponse('Saved');
			}
		else
			{
			$memberTable = new \App\Table\Member();
			$leaders = $memberTable->getLeaders();
			$leaderView = new \App\View\Leader($this->page);
			$table = new \PHPFUI\Table();
			$table->setRecordId($pk = $categoryTable->getPrimaryKeys()[0]);
			$table->setHeaders(['category' => 'Category', 'coordinator' => 'Coordinator', ]);
			$leaderId = '';

			foreach ($categories as $category)
				{
				$id = $category->categoryId;
				$editControl = $leaderView->getEditControl("coordinator{$id}", '', $leaders, $category->coordinator);

				if ($leaderId)
					{
					$editControl->setArray($leaderId);
					}
				else
					{
					$leaderId = $editControl->getName();
					}
				$row = $category->toArray();
				$row['coordinator'] = $editControl;
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$buttonGroup->addButton($backButton);
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
