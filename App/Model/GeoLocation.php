<?php

namespace App\Model;

class GeoLocation
	{
	private ?\PHPFUI\HTML5Element $callout = null;

	private ?\PHPFUI\Input $latitude = null;

	private ?\PHPFUI\Input $longitude = null;

	private ?\PHPFUI\Input\CheckBox $optIn = null;

	private ?\PHPFUI\Button $submit = null;

	/**
	 * @param array<string,bool|int> $options
	 */
	public function __construct(private readonly array $options = ['enableHighAccuracy' => true, 'timeout' => 5000, 'maximumAge' => 0])
		{
		}

	public function getJavaScript() : string
		{
		if (! $this->latitude || ! $this->longitude)
			{
			throw new \Exception('latitude / longitude not set in ' . self::class);
			}

		// get a unique id for function name
		$id = $this->latitude->getId();
		$d = '$';
		$js = "function geolocate{$id}(){";

		if ($this->optIn)
			{
			$js .= "if({$d}('#{$this->optIn->getId()}').is(':checked')){";
			}
		$js .= 'if(navigator.geolocation){navigator.geolocation.getCurrentPosition(function(pos){';
		$js .= "{$d}('#{$id}').val(pos.coords.latitude);";
		$js .= "{$d}('#{$this->longitude->getId()}').val(pos.coords.longitude);";

		if ($this->submit)
			{
			$js .= "{$d}('#{$this->submit->getId()}').prop('disabled', false);";
			}

		if ($this->callout)
			{
			$js .= "{$d}('#{$this->callout->getId()}').addClass('hide');";
			}
		$js .= '},function(err){';

		if ($this->submit)
			{
			$js .= "{$d}('#{$this->submit->getId()}').prop('disabled', true);";
			}

		if ($this->callout)
			{
			$js .= "{$d}('#{$this->callout->getId()}').html('ERROR('+err.code+'):'+err.message+'. Uncheck Include GPS location to send.').removeClass('hide')";
			}
		$js .= '},' . \PHPFUI\TextHelper::arrayToJS($this->options) . ')}else{';

		if ($this->submit)
			{
			$js .= "{$d}('#{$this->submit->getId()}').prop('disabled', true);";
			}

		if ($this->callout)
			{
			$js .= "{$d}('#{$this->callout->getId()}').removeClass('hide')";
			}
		$js .= '}';

		if ($this->optIn)
			{
			$js .= '}else{';

			if ($this->submit)
				{
				$js .= "{$d}('#{$this->submit->getId()}').prop('disabled', false);";
				}

			if ($this->callout)
				{
				$js .= "{$d}('#{$this->callout->getId()}').addClass('hide');";
				}

			$js .= "{$d}('#{$id}').val('');";
			$js .= "{$d}('#{$this->longitude->getId()}').val('')}";
			}
		$function = "geolocate{$id}()";
		$js .= "};{$function}";
		$this->optIn->setAttribute('onclick', $function);

		return $js;
		}

	public function setAcceptButton(\PHPFUI\Button $button) : \PHPFUI\Button
		{
		$button->addAttribute('disabled');

		return $this->submit = $button;
		}

	public function setLatLong(\PHPFUI\Input $latitude, \PHPFUI\Input $longitude) : static
		{
		$this->latitude = $latitude;
		$this->longitude = $longitude;

		return $this;
		}

	public function setMessageElement(\PHPFUI\HTML5Element $element) : \PHPFUI\HTML5Element
		{
		return $this->callout = $element;
		}

	public function setOptIn(\PHPFUI\Input\CheckBox $optIn) : \PHPFUI\Input\CheckBox
		{
		return $this->optIn = $optIn;
		}
	}
