<?php

namespace App\UI;

class PermissionGroupPicker
	{
	private readonly \App\Table\PermissionGroup $permissionGroupTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\Permission $initial = new \App\Record\Permission())
		{
		$this->permissionGroupTable = new \App\Table\PermissionGroup();
		$this->permissionGroupTable->addJoin('permission', new \PHPFUI\ORM\Condition('groupId', new \PHPFUI\ORM\Field('permission.permissionId')));
		$this->permissionGroupTable->addSelect('groupId');
		$this->permissionGroupTable->addSelect('name');
		$this->permissionGroupTable->setDistinct();
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return (mixed|string)[][][]
	 *
	 * @psalm-return array{suggestions: list<array{value: string, data: mixed}>}
	 */
	public function callback(array $parameters) : array
		{
		$returnValue = [];

		if (empty($parameters['save']))
			{
			$names = \explode(' ', (string)$parameters['AutoComplete']);
			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('name', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->permissionGroupTable->setWhere($condition);

			foreach ($this->permissionGroupTable->getDataObjectCursor() as $permission)
				{
				$returnValue[] = ['value' => $permission->name, 'data' => $permission->groupId];
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->initial->name ?? '';
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->permissionId ?? 0));
		$control->setNoFreeForm();

		return $control;
		}
	}
