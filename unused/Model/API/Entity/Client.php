<?php

namespace App\Model\API\Entity;

class Client implements \League\OAuth2\Server\Entities\ClientEntityInterface
  {
  use \League\OAuth2\Server\Entities\Traits\ClientTrait;
  use \League\OAuth2\Server\Entities\Traits\EntityTrait;

  public function setConfidential() : void
	{
	$this->isConfidential = false;
	}

  public function setName($name) : void
	{
	$this->name = $name;
	}

  public function setRedirectUri($uri) : void
	{
	$this->redirectUri = $uri;
	}
  }
