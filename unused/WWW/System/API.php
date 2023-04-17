<?php

namespace App\WWW\System;

class API extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function users() : void
		{
		if ($this->page->addHeader('API Users'))
			{
			$oauthUserTable = new \App\Table\OauthUser();
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$addButton = new \PHPFUI\Button('Add User', '/System/API/edit');
			$addButton->addClass('success');
			$buttonGroup->addButton($addButton);
			$settingsText = 'API Server Settings';

			if ($this->page->isAuthorized($settingsText))
				{
				$settingsButton = new \PHPFUI\Button($settingsText, '/System/API/server');
				$settingsButton->addClass('info');
				$buttonGroup->addButton($settingsButton);
				}
			$this->page->addPageContent($buttonGroup);
			$view = new \App\View\System\API($this->page);
			$this->page->addPageContent($view->list($oauthUserTable));
			}
		}

	public function edit(\App\Record\OauthUser $user = new \App\Record\OauthUser()) : void
		{
		if ($this->page->addHeader('Edit API User'))
			{
			if ($user->loaded() || ! $user->oauthUserId)
				{
				$view = new \App\View\System\API($this->page);
				$this->page->addPageContent($view->edit($user));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('User not found'));
				}
			}
		}

	public function permissions(\App\Record\OauthUser $user = new \App\Record\OauthUser()) : void
		{
		if ($this->page->addHeader('API User Permissions'))
			{
			if ($user->loaded())
				{
				$view = new \App\View\System\API($this->page);
				$this->page->addPageContent($view->editPermissions($user));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('User not found'));
				}
			}
		}

	public function server() : void
		{
		if ($this->page->addHeader('API Server Settings'))
			{
			$view = new \App\View\System\API($this->page);
			$this->page->addPageContent($view->serverSettings());
			}
		}
	}
