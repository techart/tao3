<?php

namespace TAO\Foundation;

use TAO\Callback;
use TAO\Text\StringTemplate;

class Images
{
	protected $userMods;
	protected $extraMods = [];
	protected $regexpMods = [];

	public function init()
	{
	}

	public function modifier($name, $callback)
	{
		$this->extraMods[$name] = $callback;
	}

	public function localizePath($path)
	{
		$path = str_replace(trim(config('app.url'),'/'), '', $path);
		$path = preg_replace('{^/storage/}', 'public/', $path);
		return $path;
	}

	protected function initMods()
	{
		if (is_null($this->userMods)) {
			foreach (config('tao.images.modifiers', []) as $name => $callback) {
				$this->modifier($name, $callback);
			}
			$this->userMods = $this->extraMods;
			foreach ($this->userMods as $mod => $directive) {
				if (starts_with($mod, '{')) {
					$this->regexpMods[$mod] = $directive;
				}
			}
		}
	}

	public function modify($path, $mods)
	{
		$originalPath = $path;
		$path = $this->localizePath($path);
		if (\Storage::exists($path)) {
			$mods = $this->parseMods($mods);
			if (empty($mods)) {
				return $path;
			}
			$lastModified = \Storage::lastModified($path);
			$dest = $this->destination($path, $mods);
			if (\Storage::exists($dest)) {
				if ($lastModified < \Storage::lastModified($dest)) {
					return $dest;
				}
			}
			return $this->modifyImage($path, $dest, $mods);
		}
		if (starts_with($path, 'data:')) {
			return $this->modifyImage($path, false, $mods);
		}
		return $originalPath;
	}

	public function make($path)
	{
		$path = $this->localizePath($path);
		if ($m = \TAO::regexp('{^data:.+;base64,(.+)$}', $path)) {
			$content = base64_decode($m[1]);
		} else {
			$content = \Storage::get($path);
		}
		return \Image::make($content);
	}

	public function size($path)
	{
		$image = $this->make($path);
		if ($image) {
			return $image->getSize();
		}
	}

	public function url($path)
	{
		$path = $this->localizePath($path);
		if (starts_with($path, 'data:')) {
			return $path;
		}
		return \Storage::url($path);
	}

	public function show($path, $mods = false, $tpl = '<img src="{url}" width="{width}" height="{height}">')
	{
		if ($mods) {
			$path = $this->modify($path, $mods);
		}
		$width = 0;
		$height = 0;
		$url = $this->url($path);
		if (strpos($tpl, '{width}') || strpos($tpl, '{height}')) {
			$size = $this->size($path);
			$width = $size->width;
			$height = $size->height;
		}
		return StringTemplate::process($tpl, [
			'url' => $url,
			'width' => $width,
			'height' => $height,
		]);
	}

	public function modifyImage($path, $dest, $mods)
	{
		$mods = $this->parseMods($mods);
		$image = $this->make($path);
		if ($image) {
			$ext = $this->ext($path);
			if (Callback::isValidCallback($mods['mods'])) {
				$image = Callback::instance($mods['mods'])->call($image);
			} else {
				foreach ($mods['mods'] as $mod) {
					if (Callback::isValidCallback($mod['directive'])) {
						$params = [$image, $mod['params']];
						$image = Callback::instance($mod['directive'])->args($params)->call();
					} else {
						$image = Callback::instance([$image, $mod['directive']])->args($mod['params'])->call();
					}
					if (!$image) {
						return $path;
					}
				}
			}
			if (!$dest) {
				return 'data:image/png;base64,' . base64_encode($image->encode('png', 90));
			}
			$encoded = $image->encode($ext, 90);
			\Storage::put($dest, $encoded);
			return $dest;
		}
		return $path;
	}

	public function ext($path)
	{
		$info = pathinfo($path);
		return isset($info['extension']) ? $info['extension'] : 'jpg';
	}

	public function destination($path, $mods)
	{
		$mods = $this->parseMods($mods);
		$ext = $this->ext($path);
		$path = "public/cache/images/{$path}";
		if (!\Storage::exists($path)) {
			\Storage::makeDirectory($path);
		}
		$name = $mods['filename'] . ".{$ext}";
		return "{$path}/{$name}";

	}

	public function userMod($mod)
	{
		$mod = trim($mod);
		if (isset($this->userMods[$mod])) {
			$directive = $this->userMods[$mod];
			return [
				'directive' => $directive,
				'params' => [],
			];
		} elseif (Callback::isValidCallback($mod)) {
			return [
				'directive' => $mod,
				'params' => [],
			];
		}
		foreach ($this->regexpMods as $re => $directive) {
			if ($m = \TAO::regexp($re, $mod)) {
				return [
					'directive' => $directive,
					'params' => $m,
				];
			}
		}
	}

	public function parseMods($srcMods)
	{
		$this->initMods();
		if (!is_string($srcMods)) {
			return $srcMods;
		}
		$mods = [];
		$src = false;
		foreach (explode(',', $srcMods) as $item) {
			if (!empty($item)) {
				$mod = $this->userMod($item);
				if ($mod) {
					$mods[] = $mod;
					$src .= empty($src) ? '' : ',';
					$src .= trim(preg_replace('{[^a-z0-9:_-]+}i', '.', $item), '.');
				} else {
					$item = strtolower(trim($item));
					$item = $item == 'grayscale' ? 'greyscale' : $item;
					$directive = false;
					$params = [];
					if ($m = \TAO::regexp('{^(width|height|blur|brightness|contrast|opacity|pixelate|sharpen)(-?\d+)$}', $item)) {
						$directive = $m[1];
						$params = [(int)$m[2]];
					} elseif ($m = \TAO::regexp('{^(crop|fit|resize)(\d+)x(\d+)$}', $item)) {
						$directive = $m[1];
						$params = [(int)$m[2], (int)$m[3]];
					} elseif (in_array($item, ['greyscale', 'invert'])) {
						$directive = $item;
					}
					if ($directive) {
						$src .= empty($src) ? '' : ',';
						$src .= $directive . implode('x', $params);

						$directive = str_replace('width', 'widen', $directive);
						$directive = str_replace('height', 'heighten', $directive);

						$mods[] = array(
							'directive' => $directive,
							'params' => $params,
						);
					}
				}
			}
		}
		if (empty($mods)) {
			return [];
		}
		return array(
			'mods' => $mods,
			'filename' => $src,
		);
	}
}