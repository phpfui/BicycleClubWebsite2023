<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class PermissionGroup extends \App\Record\Definition\PermissionGroup
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'name' => [\App\DB\GroupName::class],
	];
	}
