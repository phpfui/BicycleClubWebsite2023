<?php

namespace App\WWW\Content;

class SlideShow extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\SlideShow $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteCategory':
						if ($this->page->controller->getPermissions()->isAuthorized('Delete Content Category', 'Content'))
							{
							$blog = new \App\Record\Blog((int)$_POST['blogId']);
							$blog->delete();
							$this->page->setResponse($_POST['blogId']);
							}

						break;
					}
				}
			}
		$this->view = new \App\View\SlideShow($this->page);
		}

	public function edit(\App\Record\SlideShow $slideShow = new \App\Record\SlideShow()) : void
		{
		$type = $slideShow->loaded() ? 'Edit' : 'Add';

		if ($this->page->addHeader($type . ' Slide Show'))
			{
			$this->page->addPageContent($this->view->editShow($slideShow));
			}
		}

	public function slide(\App\Record\SlideShow $slideShow = new \App\Record\SlideShow(), \App\Record\Slide $slide = new \App\Record\Slide()) : void
		{
		if (! $slideShow->loaded())
			{
			$this->page->redirect('/Content/SlideShow/edit/0');

			return;
			}
		$type = $slide->loaded() ? 'Edit' : 'Add';

		if ($this->page->addHeader($type . ' Slide'))
			{
			$this->page->addPageContent($this->view->editSlide($slideShow, $slide));
			}
		}

	public function list() : void
		{
		if ($this->page->addHeader('Slide Shows'))
			{
			$slideShowTable = new \App\Table\SlideShow();
			$this->page->addPageContent(new \PHPFUI\Button('Add Slide Show', '/Content/SlideShow/edit/0'));
			$this->page->addPageContent($this->view->list($slideShowTable));
			}
		}

	public function photo(\App\Record\Slide $slide = new \App\Record\Slide()) : never
		{
		$thumbModel = new \App\Model\SlideImage($slide);

		echo $thumbModel->getImg();

		exit;
		}

	public function show(\App\Record\SlideShow $slideShow = new \App\Record\SlideShow(), string $debug = '') : void
		{
		if ($this->page->addHeader('Show Slide Show'))
			{
			$this->page->addPageContent($this->view->show($slideShow, '' == $debug, 'debug' == $debug));
			}
		}
	}
