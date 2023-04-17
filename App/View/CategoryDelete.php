<?php

namespace App\View;

class CategoryDelete implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Category $category)
		{
		if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
			{
			if ('Delete' == $_POST['submit'])
				{
				$categoryTable = new \App\Table\Category();
				$categoryTable->changeCategory($_POST['deleteCategory'], $_POST['mergeCategory']);
				$this->page->redirect('/Leaders/categories');
				}
			}
		}

	public function __toString() : string
		{
		$form = new \PHPFUI\Form($this->page);

		if ($this->category->loaded())
			{
			$form->add("<h3>Are you sure you want to delete category {$this->category->category}?</h3>");
			$alert = new \PHPFUI\Callout('info');
			$alert->add('In order to delete a category, you need to merge it into another category to preserve any rides associated with it.
				Any ride with the old category will now have the new category you specify. Make sure the paces from the old category line up by order with the new category, as the old rides with be given the new paces in the new category by pace order.');
			$form->add($alert);
			$form->add(new \PHPFUI\Input\Hidden('deleteCategory', (string)$this->category->categoryId));
			$cancel = new \PHPFUI\Button('Cancel', '/Leaders/categories');
			$cancel->addClass('hollow')->addClass('alert');
			$categoryView = new \App\View\Categories($this->page, $cancel);
			$catPicker = $categoryView->getCategoryPicker('mergeCategory', "Category to merge {$this->category->category} into");
			$catPicker->removeOption((string)0);
			$catPicker->removeOption((string)$this->category->categoryId);
			$form->add($catPicker);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton(new \PHPFUI\Submit('Delete'));
			$buttonGroup->addButton($cancel);
			$form->add($buttonGroup);
			}
		else
			{
			$form->add(new \PHPFUI\SubHeader('Invalid Category'));
			}

		return "{$form}";
		}
	}
