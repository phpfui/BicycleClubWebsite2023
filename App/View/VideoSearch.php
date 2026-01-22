<?php

namespace App\View;

class VideoSearch
	{
	public function __construct(protected \App\View\Page $page)
		{
		}

	public function __toString() : string
		{
		$button = new \PHPFUI\Button('Search Videos');
		$modal = $this->getSearchModal($button);
		$output = '';

		if (! empty($_GET['title']) || ! empty($_GET['description']))
			{
			$view = new \App\View\Video($this->page);
			$videoTable = new \App\Table\Video();
			$output = $view->list($videoTable->search($_GET));

			if (\count($videoTable))
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
		$fieldSet = new \PHPFUI\FieldSet('Search Videos');
		$fieldSet->add(new \PHPFUI\Input\Text('title', 'Video Title', $_GET['title'] ?? ''));
		$fieldSet->add(new \PHPFUI\Input\Text('description', 'Video Description', $_GET['description'] ?? ''));
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}
	}
