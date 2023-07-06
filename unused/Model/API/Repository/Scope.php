<?php

namespace App\Model\API\Repository;

class Scope implements \League\OAuth2\Server\Repositories\ScopeRepositoryInterface
  {
  /**
   * {@inheritDoc}
   */
  public function finalizeScopes(array $scopes, $grantType, \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity, $userIdentifier = null) : array
	  {
		return [];
		}

  /**
   * {@inheritDoc}
   */
  public function getScopeEntityByIdentifier($scopeIdentifier) : ?\League\OAuth2\Server\Entities\ScopeEntityInterface
		{
		return null;
		}
	}
