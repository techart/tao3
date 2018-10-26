<?php

namespace TAO\Vars;

use Illuminate\Support\Facades\Storage as AppStorage;
use TAO\Callback;
use TAO\Type;

class Storage
{
	public function get($name)
	{
		$name = trim($name);
		$scope = $this->getScopeFor($name);
		if (is_array($scope) && isset($scope['vars'])) {
			$scopeName = false;
			if ($m = \TAO::regexp('{^(.+)::(.+)$}', $name)) {
				$scopeName = trim($m[1]);
				$name = trim($m[2]);
			}
			if (isset($scope['vars'][$name])) {
				$vparams = $this->canonizeVarParams($scope['vars'][$name]);
				return $this->makeVarInstanse($scopeName, $name, $vparams);
			}
		}
	}

	public function find($name)
	{
		return $this->get($name);
	}

	public function getScopeFor($name)
	{
		if ($m = \TAO::regexp('{^(.+)::(.+)$}', $name)) {
			$scopeName = trim($m[1]);
			return config("vars.{$scopeName}", false);
		}
		return config("vars");
	}

	public function canonizeVarParams($data)
	{
		$fields = [];
		if (isset($data['fields'])) {
			$fields = $data['fields'];
		} else {
			$fdata = [];
			$fdata['label'] = isset($data['label']) ? $data['label'] : 'Значение';
			$fdata['type'] = isset($data['type']) ? $data['type'] : 'string';
			foreach (['style', 'validate'] as $param) {
				if (isset($data[$param])) {
					$fdata[$param] = $data[$param];
				}
			}
			$fields['value'] = $fdata;
		}
		$data['fields'] = $fields;
		return $data;
	}

	public function makeVarInstanse($scope, $name, $data)
	{
		return new \TAO\Vars\Variable($scope, $name, $data);
	}

	public function save($scope, $name, $values)
	{
		$path = $this->getStoragePath($scope, $name);
		AppStorage::put($path, serialize($values));
	}

	public function load($scope, $name)
	{
		$path = $this->getStoragePath($scope, $name);
		if (AppStorage::exists($path)) {
			return unserialize(AppStorage::get($path));
		}
		return [];
	}

	public function getStoragePath($scope, $name)
	{
		if (!$scope) {
			return "vars/{$name}";
		}
		return "vars/{$scope}/{$name}";
	}

	public function accessToScope($scope = false)
	{
		$data = $scope? config("vars.{$scope}", []) : config('vars');
		if ($access = $data['access'] ?? false) {
			if (Type::isCallable($access)) {
				return Callback::instance($access)->call();
			}
			return auth()->user()->checkAccess($access);
		}
		return true;
	}
}