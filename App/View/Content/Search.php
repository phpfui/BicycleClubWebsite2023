<?php

namespace App\View\Content;

class Search implements \Stringable
	{
	private array $searchFields = [
		'headline' => 'Story Headline',
		'subhead' => 'Story Sub Heading',
		'body' => 'Story Body',
		'author' => 'Author',
	];

	private readonly \App\Table\Story $storyTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->storyTable = new \App\Table\Story();
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Content');
		$modal = $this->getSearchModal($button);
		$output = '';

		$condition = new \PHPFUI\ORM\Condition();

		foreach ($this->searchFields as $field => $name)
			{
			if (! empty($_GET[$field]))
				{
				$condition->and($field, '%' . $_GET[$field] . '%', new \PHPFUI\ORM\Operator\Like());
				}
			}

		if (\count($condition))
			{
			$this->storyTable->setWhere($condition);

			$view = new \App\View\Content($this->page);
			$output = $view->showContinuousScrollTable($this->storyTable);

			if ($this->storyTable->count())
				{
				$output .= $button;
				}
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Search Content');

		foreach ($this->searchFields as $field => $name)
			{
			$fieldSet->add(new \PHPFUI\Input\Text($field, $name, $_GET[$field] ?? ''));
			}
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}
	}
