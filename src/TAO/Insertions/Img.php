<?php

namespace TAO\Insertions;

class Img
{
	public function run($args, $options, $unnamed)
	{
		foreach($unnamed as $value) {
			if (\TAO::regexp('{\.(jpe?g|png|gif)$}i', $value)) {
				if (!isset($options['preview'])) {
					$options['preview'] = $value;
				} elseif (!isset($options['full'])) {
					$options['full'] = $value;
				}
			} elseif (starts_with($value, 'http://') || starts_with($value, 'https://') || starts_with($value, '//')) {
				$options['external_link'] = $value;
			} elseif (starts_with($value, '/')) {
				$options['link'] = $value;
			} elseif (!empty(app('tao.images')->parseMods($value))) {
				if (!isset($options['preview_mods'])) {
					$options['preview_mods'] = $value;
				} elseif (!isset($options['full_mods'])) {
					$options['full_mods'] = $value;
				}
			}
		}
		if (!isset($options['preview'])) {
			return $options['insertion_source'];
		}
		if (!isset($options['full'])) {
			$options['full'] = $options['preview'];
		}
		if (!$this->isNo($options['preview_mods'])) {
			$options['preview'] = app('tao.images')->modify($options['preview'], $options['preview_mods']);
		}
		$options['preview_url'] = app('tao.images')->url($options['preview']);
		$options['preview_tag'] = app('tao.images')->show($options['preview']);
		if (isset($options['external_link'])) {
			return view('insertions.img.external-link', $options);
		}
		if (isset($options['link'])) {
			return view('insertions.img.link', $options);
		}
		if (!$this->isNo($options['full_mods']) && !$this->isNo($options['full'])) {
			$options['full'] = app('tao.images')->modify($options['full'], $options['full_mods']);
		}
		if ($this->isNo($options['full'])) {
			return view('insertions.img.simple', $options);
		}
		$options['full_url'] = app('tao.images')->url($options['full']);
		return view('insertions.img.thumb', $options);
	}

	protected function isNo($v)
	{
		$v = trim($v);
		return empty($v) || $v === 'false' || $v === '0' || $v === 'no';
	}
}
