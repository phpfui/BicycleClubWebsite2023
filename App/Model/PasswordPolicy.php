<?php

namespace App\Model;

class PasswordPolicy
	{
	/** @var array<string, array<null|string>> */
	protected static array $fields = [
		'Length' => [null, 'Password must be at least :value characters long'],
		'Upper' => ['/[A-Z]/', 'Password must contain UPPER case characters'],
		'Lower' => ['/[a-z]/', 'Password must contain lower case characters'],
		'Numbers' => ['/[0-9]/', 'Password must contain numbers (0-9)'],
		'Punctuation' => ['/[^A-Za-z0-9]/', 'Password must contain punctuation characters'],
	];

	/** @var array<string,int> */
	protected static array $passwordOptions = ['cost' => 11];

	protected static string $prefix = 'PasswordPolicy';

	/**
	 * @var array<string,string>
	 */
	protected static array $values = [];

	public function __construct()
		{
		$settingsSaver = new \App\Model\SettingsSaver(self::$prefix);
		static::$values = $settingsSaver->getValues();
		}

	public static function getVerifyCode(?string $password) : int
		{
		$verifyCode = \preg_replace('/[^0-9]*/', '', $password ?? '');
		$len = \strlen($verifyCode);
		$minLen = 5;

		if ($len > $minLen)
			{
			$verifyCode = \substr($verifyCode, $len - $minLen, $minLen);
			}

		return (int)$verifyCode;
		}

	public static function hashPassword(string $password) : ?string
		{
		return \password_hash(\trim($password), PASSWORD_DEFAULT, self::$passwordOptions);
		}

	public static function needsRehash(string $hash) : bool
		{
		return \password_needs_rehash($hash, PASSWORD_DEFAULT, self::$passwordOptions);
		}

	public static function resetPassword(string $email, bool $text = false) : void
		{
		$member = new \App\Record\Member(['email' => \App\Model\Member::cleanEmail($email)]);

		if ($member->loaded())
			{
			$member->passwordReset = \bin2hex(\random_bytes(10));
			$member->passwordResetExpires = \date('Y-m-d H:i:s', \time() + 3600 * 24);
			$member->loginAttempts = (string)\json_encode([]);
			$member->update();
			$settingTable = new \App\Table\Setting();
			$resetLink = $settingTable->value('homePage') . '/Membership/passwordNew/' . $member->memberId . '/' . $member->passwordReset;

			if ($text && $member->cellPhone)
				{
				$sms = new \App\Model\SMS("Here is your requested password reset link:\n\n{$resetLink}");
				$sms->textMember($member);
				}
			else
				{
				$email = new \App\Tools\EMail();
				$email->setSubject('Reset your ' . $settingTable->value('clubName') . ' password');
				$body = $settingTable->value('NewPasswordEmail') ?: '<a href="~password~">Click here to enter a new password</a>';
				$member->password = $resetLink;
				$email->setBody(\App\Tools\TextHelper::processText($body, $member->toArray()));
				$email->setHtml();
				$email->addToMember($member);
				$email->setFrom($settingTable->value('Web_MasterEmail'), $settingTable->value('Web_MasterName'));
				$email->send();
				}
			}
		}

	/**
	 * @return array<string> errors
	 */
	public static function validate(string $password) : array
		{
		$errors = [];

		if (! static::$values)
			{
			return $errors;
			}

		foreach (static::$fields as $key => $parameters)
			{
			$value = static::$values[self::$prefix . $key];

			if (! empty($value))
				{
				if ($parameters[0])
					{
					$matches = [];
					\preg_match($parameters[0], $password, $matches);

					if (! $matches)
						{
						$errors[] = \trans($parameters[1], ['value' => $value]);
						}
					}
				elseif (\strlen($password) < $value)
					{
					$errors[] = \trans($parameters[1], ['value' => $value]);
					}
				}
			}

		return $errors;
		}

	public static function verifyPassword(string $passwordToVerify, \App\Record\Member $member) : bool
		{
		return \password_verify($passwordToVerify, $member->password ?? \uniqid());
		}
	}
