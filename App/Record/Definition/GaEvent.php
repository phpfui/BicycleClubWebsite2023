<?php

namespace App\Record\Definition;

/**
 * Autogenerated. Do not modify. Modify SQL table, then generate with \PHPFUI\ORM\Tool\Generate\CRUD class.
 *
 * @property ?int $allowShopping MySQL type int
 * @property ?int $dayOfRegistration MySQL type int
 * @property int $deleteIncomplete MySQL type int
 * @property ?string $description MySQL type text
 * @property string $eventDate MySQL type date
 * @property int $gaEventId MySQL type int
 * @property \App\Record\GaEvent $gaEvent related record
 * @property int $includeMembership MySQL type int
 * @property string $incompleteDaysAfter MySQL type varchar(255)
 * @property ?string $incompleteMessage MySQL type mediumtext
 * @property string $lastRegistrationDate MySQL type date
 * @property ?string $location MySQL type char(100)
 * @property ?int $maxRegistrants MySQL type int
 * @property ?string $membershipExpires MySQL type date
 * @property ?int $otherEvent MySQL type int
 * @property ?string $registrar MySQL type char(50)
 * @property ?string $registrarEmail MySQL type char(50)
 * @property ?int $showPreregistration MySQL type int
 * @property ?string $signupMessage MySQL type mediumtext
 * @property ?string $title MySQL type char(100)
 * @property ?int $volunteerDiscount MySQL type int
 * @property ?int $volunteerEvent MySQL type int
 * @property ?string $waiver MySQL type mediumtext
 */
abstract class GaEvent extends \PHPFUI\ORM\Record
	{
	protected static bool $autoIncrement = true;

	/** @var array<string, \PHPFUI\ORM\FieldDefinition> */
	protected static array $fields = [];

	/** @var array<string> */
	protected static array $primaryKeys = ['gaEventId', ];

	protected static string $table = 'gaEvent';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'allowShopping' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'dayOfRegistration' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'deleteIncomplete' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'description' => new \PHPFUI\ORM\FieldDefinition('text', 'string', 65535, true, ),
				'eventDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'gaEventId' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, ),
				'includeMembership' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, false, 0, ),
				'incompleteDaysAfter' => new \PHPFUI\ORM\FieldDefinition('varchar(255)', 'string', 255, false, '', ),
				'incompleteMessage' => new \PHPFUI\ORM\FieldDefinition('mediumtext', 'string', 16777215, true, ),
				'lastRegistrationDate' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, false, ),
				'location' => new \PHPFUI\ORM\FieldDefinition('char(100)', 'string', 100, true, ),
				'maxRegistrants' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'membershipExpires' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 10, true, ),
				'otherEvent' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'registrar' => new \PHPFUI\ORM\FieldDefinition('char(50)', 'string', 50, true, ),
				'registrarEmail' => new \PHPFUI\ORM\FieldDefinition('char(50)', 'string', 50, true, ),
				'showPreregistration' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'signupMessage' => new \PHPFUI\ORM\FieldDefinition('mediumtext', 'string', 16777215, true, ),
				'title' => new \PHPFUI\ORM\FieldDefinition('char(100)', 'string', 100, true, ),
				'volunteerDiscount' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'volunteerEvent' => new \PHPFUI\ORM\FieldDefinition('int', 'int', 0, true, ),
				'waiver' => new \PHPFUI\ORM\FieldDefinition('mediumtext', 'string', 16777215, true, ),
			];
			}

		return $this;
		}
	}
