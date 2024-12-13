<?php

namespace App\UI;

class CoordinatePicker extends \PHPFUI\Reveal
	{
	private \PHPFUI\Form $form;

	private \App\Model\GeoLocation $geoLocate;

	private \PHPFUI\Input\Number $latitude;

	private \PHPFUI\Input\Number $longitude;

	public function __construct(\PHPFUI\Page $page, \PHPFUI\HTML5Element $openingElement)
		{
		parent::__construct($page, $openingElement);
		$this->geoLocate = new \App\Model\GeoLocation();

		$callout = new \PHPFUI\Callout('alert');
		$callout->add('Please Turn on Location Services');
		$callout->addClass('hide');

		$this->latitude = new \PHPFUI\Input\Number('latitude', 'Latitude');
		$this->latitude->setRequired();
		$this->longitude = new \PHPFUI\Input\Number('longitude', 'Longitude');
		$this->longitude->setRequired();
		$this->latitude->setValue('23.333');//$_GET['latitude']);

		$noPopUp = 0;

		if (\is_numeric($_GET['latitude'] ?? ''))
			{
			$this->latitude->setValue($_GET['latitude']);
			++$noPopUp;
			}

		if (\is_numeric($_GET['longitude'] ?? ''))
			{
			$this->longitude->setValue($_GET['longitude']);
			++$noPopUp;
			}
		$this->geoLocate->setLatLong($this->latitude, $this->longitude);

		if ($noPopUp < 2)
			{
			$this->showOnPageLoad();
			}
		$this->geoLocate->setErrorMessage('Check that your browse has location services enabled');
		$this->form = new \PHPFUI\Form($page);
		$this->form->addAttribute('method', 'GET');
		$callout = new \PHPFUI\Callout('alert');
		$callout->add('Please Turn on Location Services');
		$callout->addClass('hide');
		$this->form->add(new \PHPFUI\Header('Set Location for Search', 4));
		$this->form->add($this->geoLocate->setMessageElement($callout));
		$fieldSet = new \PHPFUI\FieldSet('Coordinates');
		$fieldSet->add($this->latitude)->add($this->longitude);
		$this->form->add($fieldSet);
		$this->add($this->form);
//		$this->form->add(new \PHPFUI\Debug($this->geoLocate));

		if (\App\Model\Session::checkCSRF())
			{
			if (($_POST['action'] ?? '') == 'changeRWGPS')
				{
				$rwgps = new \App\Record\RWGPS($_POST['RWGPSId'] ?? '');
				$data = $rwgps->loaded() ? $rwgps->toArray() : [];
				$this->page->setRawResponse(\json_encode(['response' => $data], JSON_THROW_ON_ERROR));
				}
			elseif (($_POST['action'] ?? '') == 'changeStartLocation')
				{
				$startLocation = new \App\Record\StartLocation($_POST['startLocationId'] ?? '');
				$data = $startLocation->loaded() ? $startLocation->toArray() : [];
				$this->page->setRawResponse(\json_encode(['response' => $data], JSON_THROW_ON_ERROR));
				}
			}
		}

	public function addDateRange() : static
		{
		$fieldSet = new \PHPFUI\FieldSet('Date Range');
		$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date'));
		$fieldSet->add(new \PHPFUI\Input\Date($this->page, 'endDate', 'End Date'));
		$this->form->add($fieldSet);

		return $this;
		}

	public function addMyLocation() : static
		{
		$geoLocation = new \PHPFUI\Input\CheckBoxBoolean('geoLocate', 'Use My Location');
		$this->form->add($this->geoLocate->setOptIn($geoLocation));
		$this->page->addJavaScript($this->geoLocate->getJavaScript());

		return $this;
		}

	public function addRWGPS() : static
		{
		$rwgpsPicker = new \App\UI\RWGPSPicker($this->page, 'RWGPSId', 'RWGPS Route');
		$RWGPSId = $rwgpsPicker->getEditControl();
		$hidden = $RWGPSId->getHiddenField();
		$ajax = new \PHPFUI\AJAX('changeRWGPS');
		$js = 'if(data.response.latitude)$("#' . $this->latitude->getId() . '").val(data.response.latitude);';
		$js .= 'if(data.response.longitude)$("#' . $this->longitude->getId() . '").val(data.response.longitude);';
		$ajax->addFunction('success', $js);
		$hidden->addAttribute('onchange', $change = 'var value=$("#' . $hidden->getId() . '").val();if(value.length){' . $ajax->execute(['RWGPSId' => '$("#' . $hidden->getId() . '").val()']) . '}');
		$this->form->add($RWGPSId);
		$this->page->addJavaScript($ajax->getPageJS());

		return $this;
		}

	public function addStartLocation() : static
		{
		$startLocation = new \App\View\StartLocation($this->page);
		$control = $startLocation->getEditControl(0);
		$hidden = $control->getHiddenField();
		$ajax = new \PHPFUI\AJAX('changeStartLocation');
		$js = 'if(data.response.latitude)$("#' . $this->latitude->getId() . '").val(data.response.latitude);';
		$js .= 'if(data.response.longitude)$("#' . $this->longitude->getId() . '").val(data.response.longitude);';
		$ajax->addFunction('success', $js);
		$hidden->addAttribute('onchange', $change = 'var value=$("#' . $hidden->getId() . '").val();if(value.length){' . $ajax->execute(['startLocationId' => '$("#' . $hidden->getId() . '").val()']) . '}');
		$this->form->add($control);
		$this->page->addJavaScript($ajax->getPageJS());

		return $this;
		}

	public function getForm() : \PHPFUI\Form
		{
		return $this->form;
		}
	}
