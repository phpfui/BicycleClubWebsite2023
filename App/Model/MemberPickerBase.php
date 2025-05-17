<?php

namespace App\Model;

abstract class MemberPickerBase
	{
	protected bool $currentMember = true;

	/** @var array<string,mixed> */
	protected array $member = [];

	protected \App\Table\Member $memberTable;

	public function __construct(protected string $name = '')
		{
		$this->memberTable = new \App\Table\Member();
		$this->memberTable->addOrderBy('firstName')->addOrderBy('lastName');
		}

	/**
	 * @param array<string> $names
	 */
	public function findByName(array $names) : \PHPFUI\ORM\ArrayCursor
		{
		return $this->memberTable->findByName($names, $this->currentMember);
		}

	public function getCurrentMember() : bool
		{
		return $this->currentMember;
		}

	/**
	 * @return array<string,mixed> member
	 */
	abstract public function getMember(string $title = '', bool $returnSomeone = true) : array;

	public function getName() : string
		{
		return $this->name;
		}

	abstract public function save(int $value) : void;

	/**
	 * @param array<string,mixed> $member
	 */
	public function setMember(array $member) : void
		{
		$this->member = $member;
		}
	}
