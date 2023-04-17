<?php

namespace App\Model;

abstract class MemberPickerBase
	{
	protected bool $currentMember = true;

	protected array $member = [];

	protected \App\Table\Member $memberTable;

	public function __construct(protected string $name = '')
		{
		$this->memberTable = new \App\Table\Member();
		}

	public function findByName(array $names) : iterable
		{
		return $this->memberTable->findByName($names, $this->currentMember);
		}

	abstract public function getMember(string $title = '', bool $returnSomeone = true) : array;

	public function getName() : string
		{
		return $this->name;
		}

	abstract public function save(int $value) : void;

	public function setMember(array $member) : void
		{
		$this->member = $member;
		}
	}
