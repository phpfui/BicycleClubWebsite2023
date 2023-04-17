<?php

namespace App\Model\API\Entity;

class RefreshToken implements \League\OAuth2\Server\Entities\RefreshTokenEntityInterface
	{
	use \League\OAuth2\Server\Entities\Traits\EntityTrait;
	use \League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
	}
