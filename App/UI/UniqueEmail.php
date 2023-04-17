<?php

namespace App\UI;

/**
 * Simple wrapper for Email input fields
 */
class UniqueEmail extends \PHPFUI\Input\Email
	{
	/**
	 * Validate that an email does not already exist in the system.
	 *
	 * @param \App\Record\Member $member currently being edited
	 * @param string $name of the field
	 * @param string $label defaults to empty
	 * @param ?string $value defaults to empty
	 */
	public function __construct(\PHPFUI\Interfaces\Page $page, \App\Record\Member $member, string $name, string $label = '', ?string $value = '')
		{
		parent::__construct($name, $label, $value);
		$this->addAttribute('onchange', 'UniqueEmail(' . $member->memberId . ',this,"' . $this->getId() . '")');
		$js = "function UniqueEmail(memberId,email,id){var data={memberId:memberId,email:email.value,id:id};
$.ajax({dataType:'json',type:'POST',traditional:true,data:data,
success:function(response){if (response.length){" .
'$field = $(email);
$field.addClass("is-invalid-input");
$field.parent().addClass("is-invalid-label");
$field.next().addClass("is-visible").html(response);
}}})}';
		$page->addJavaScript($js);

		if (\count($_POST) && ($_POST['memberId'] ?? 0) == $member->memberId && ($_POST['id'] ?? '') == $this->getId())
			{
			$memberTable = new \App\Table\Member();
			$memberTable->setWhere(new \PHPFUI\ORM\Condition('email', $_POST['email']));
			$error = '';

			foreach ($memberTable->getRecordCursor() as $found)
				{
				if ($found->memberId != $member->memberId)
					{
					$error = 'Email address ' . $_POST['email'] . ' is already in use';

					break;
					}
				}
			$page->setRawResponse(\json_encode($error, JSON_THROW_ON_ERROR));
			}
		}
	}
