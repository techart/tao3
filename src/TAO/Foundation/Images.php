<?php

namespace TAO\Foundation;

use TAO\Callback;

class Images
{
	public function init()
	{
	}

	public function modify($path, $mods)
	{
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
		return $path;
	}

	public function modifyImage($path, $dest, $mods)
	{
		$mods = $this->parseMods($mods);
		$image = \Image::make(\Storage::get($path));
		if ($image) {
			$ext = $this->ext($path);
			if (Callback::isValidCallback($mods['mods'])) {
				$image = Callback::instance($mods['mods'])->call($image);
			} else {
				foreach ($mods['mods'] as $mod) {
					$image = call_user_func_array([$image, $mod['directive']], $mod['params']);
					if (!$image) {
						return $path;
					}
				}
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

	public function parseMods($srcMods)
	{
		if (!is_string($srcMods)) {
			return $srcMods;
		}
		$mods = [];
		$src = false;
		foreach (explode(',', $srcMods) as $item) {
			$item = strtolower(trim($item));
			if (!empty($item)) {
				$item = $item == 'grayscale'? 'greyscale' : $item;
				$directive = false;
				$params = [];
				if ($m = \TAO::regexp('{^(width|height|blur|brightness|contrast|opacity|pixelate|sharpen)(\d+)$}', $item)) {
					$directive = $m[1];
					$params = [(int)$m[2]];
				} elseif ($m = \TAO::regexp('{^(crop|fit|size)(\d+)x(\d+)$}', $item)) {
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
		if (empty($mods)) {
			return [];
		}
		return array(
			'mods' => $mods,
			'filename' => $src,
		);
	}
}