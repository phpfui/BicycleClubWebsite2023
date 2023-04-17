<?php

namespace Tests\Fixtures\Permission;

class AllowedFlags
{
	use \App\Model\Traits\AllowedFlags;

	public int $allowedSubPermissionFlags = 0;

	public int $revokedSubPermissionFlags = 0;
}
