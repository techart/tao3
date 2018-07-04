<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

// TODO: geocoding
class Coordinates extends Field
{
	protected $uniqId;

	public function checkSchema(Blueprint $table)
	{
		if (!$this->item->hasColumn($this->latColumnName())) {
			$table->decimal($this->latColumnName(), 8, 6)->nullable();
		}
		if (!$this->item->hasColumn($this->lngColumnName())) {
			$table->decimal($this->lngColumnName(), 8, 6)->nullable();
		}
	}

	public function createField(Blueprint $table)
	{
	}

	protected function baseColumnName()
	{
		return $this->name;
	}

	public function latColumnName()
	{
		return $this->param('latitude_column', $this->baseColumnName() . '_lat');
	}

	public function lngColumnName()
	{
		return $this->param('longitude_column', $this->baseColumnName() . '_lng');
	}

	public function latInputName()
	{
		return $this->latColumnName();
	}

	public function lngInputName()
	{
		return $this->lngColumnName();
	}

	public function getInputId()
	{
		return $this->uniqId();
	}

	public function getMapId()
	{
		return $this->uniqId();
	}

	public function uniqId()
	{
		if (!$this->uniqId) {
			$this->uniqId = $this->name . "-" . uniqid();
		}
		return $this->uniqId;
	}

	public function lat()
	{
		return $this->item ? $this->item->getAttribute($this->latColumnName()) : $this->nullValue();
	}

	public function lng()
	{
		return $this->item ? $this->item->getAttribute($this->lngColumnName()) : $this->nullValue();
	}

	public function value()
	{
		return $this->isNotEmpty() ? [
			'lat' => $this->lat(),
			'lng' => $this->lng()
		] : $this->nullValue();
	}

	public function checkEmpty()
	{
		return is_null($this->lat()) || is_null($this->lng());
	}

	public function setLat($lat)
	{
		if ($this->item) {
			$this->item->setAttribute($this->latColumnName(), $this->prepareCoordinate($lat));
		}
	}

	public function setLng($lng)
	{
		if ($this->item) {
			$this->item->setAttribute($this->lngColumnName(), $this->prepareCoordinate($lng));
		}
	}

	public function set($value)
	{
		$value = $this->prepareValue($value);

		if (!$this->checkValue($value)) {
			return false;
		}

		$this->setLat($value['lat'] ?? $value[0]);
		$this->setLng($value['lng'] ?? $value[1]);
	}

	protected function hasRequestValue($request)
	{
		return $request->has($this->latColumnName()) && $request->has($this->lngColumnName());
	}

	protected function getValueFromRequest($request)
	{
		return [
			'lat' => request($this->latColumnName()),
			'lng' => request($this->lngColumnName())
		];
	}

	public function nullValue()
	{
		return null;
	}

	public function setupDefault()
	{
		return [$this->nullValue(), $this->nullValue()];
	}

	protected function prepareValue($value)
	{
		if (!is_array($value)) {
			$value = explode(',', $value);
		}
		return $value;
	}

	protected function checkValue($value)
	{
		return is_array($value) && count($value) == 2;
	}

	protected function prepareCoordinate($coordinate)
	{
		return (float)str_replace([' ', ','], ['', '.'], $coordinate);
	}

	public function defaultInputContext()
	{
		$context = parent::defaultInputContext();
		$context['show_map'] = $this->param('show_map', false);
		return $context;
	}

	public function jsFieldParams()
	{
		return json_encode([
			'center' => [54.505, 37.09],
			'zoom' => 6,
			'value' => $this->value()
		]);
	}

	public function mapStyle()
	{
		return $this->param('map_style', false);
	}

	public function defaultContext()
	{
		$context = parent::defaultContext();
		$context['zoom'] = $this->param('zoom', 10);
		return $context;
	}

	protected function defaultTemplate()
	{
		return 'fields ~ coordinates.output';
	}


}
