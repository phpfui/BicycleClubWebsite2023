<?php

namespace App\UI;

class ErrorForm extends \PHPFUI\Form
	{
	public function __construct(\PHPFUI\Interfaces\Page $page, ?\PHPFUI\Submit $submit = null)
		{
		$id = $this->getId();
		$functionName = 'post' . $id;
		parent::__construct($page, $submit, $functionName);
		$formError = new \PHPFUI\FormError('');
		$formErrorClass = 'FormError' . $id;
		$formError->addClass($formErrorClass);
		$this->add($formError);
		$page->addJavaScript('function ' . $functionName . '(post){if(post.errors){
var error="Please correct the following errors:<ul>";for(const [key,value] of Object.entries(post.errors))
{var $input=$("[name=\'"+key+"\']");$input.addClass("is-invalid-input")
for(const element of value){error+="<li><b>"+key+"</b>: <i>"+element+"</i></li>";}}
error+="</ul>";$(".' . $formErrorClass . '").html(error).attr("style","display:block");}}');
		}

	public function returnErrors(array $errors) : string
		{
		return \json_encode(['response' => 'Error!', 'color' => 'red', 'errors' => $errors, ]);
		}
	}