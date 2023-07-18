<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class OauthUser extends \App\Record\Definition\OauthUser
	{
	/** @var array<string, int> */
	private array $cost = ['cost' => 12];

	public function authenticateUser(string $userName, string $password) : bool
		{
		$this->read(['userName' => $userName]);

		if (! $this->loaded())
			{
			return false;
			}

		$hash = $this->password ?? '';
		$password = \trim($password);

		// Verify stored hash against plain-text password
		if (! \password_verify($password, $hash))
			{
			return false;
			}

		$this->lastLogin = \date('Y-m-d H:i:s');
		// Check if a newer hashing algorithm is available or the cost has changed
		if (\password_needs_rehash($hash, PASSWORD_DEFAULT, $this->cost))
			{
			// If so, create a new hash, and replace the old one
			$this->setPassword($password);
			$this->update();
			}

		return true;
		}

	/**
	 * @return array<string,array<string,int>>
	 */
	public function getPermissions() : array
		{
		return \json_decode($this->permissions ?: '[]', true);
		}

	public function setPassword(string $password) : static
		{
		$this->password = \password_hash($password, PASSWORD_DEFAULT, $this->cost);

		return $this;
		}

	/**
	 * @param array<string,array<string,int>> $permissions
	 */
	public function setPermissions(array $permissions) : static
		{
		\App\Tools\Logger::get()->debug($permissions);
		$this->permissions = \json_encode($permissions, JSON_THROW_ON_ERROR);

		return $this;
		}
	}
