<?php

namespace TAO\Fields\Type;

class Image extends Upload
{
	/**
	 * @return string
	 */
	public function adminPreviewUrl()
	{
		return $this->apiUrl('preview', ['_token' => csrf_token(), 'upload_id' => $this->tempId()]) . '&r=' . time() . rand(1111, 9999);
	}

	/**
	 * @return string
	 */
	public function apiActionPreview()
	{
		$tid = app()->request()->get('upload_id');
		$path = $this->tempDir($tid) . '/file';
		if (!\Storage::exists($path)) {
			\Log::debug("$path !!!!!");
			$path = trim($this->value());
		}
		if (empty($path) || !\Storage::exists($path)) {
			return 'Image not found';
		}
		$image = \Image::make(\Storage::get($path));
		if ($m = \TAO::regexp('{^image/(gif|png|jpeg)$}', $image->mime)) {
			$ext = str_replace('jpeg', 'jpg', $m[1]);
			return $image->resize(100, 100, function ($c) {
				$c->aspectRatio();
			})->response($ext);
		} else {
			return 'Invalid mime type: ' . $image->mime;
		}

	}

	/**
	 * @param $file
	 * @param $info
	 * @return bool|mixed|string
	 */
	public function checkUploadedFile($file, &$info)
	{
		$check = parent::checkUploadedFile($file, $info);
		if (is_string($check)) {
			return $check;
		}
		$ext = str_replace('jpeg', 'jpg', strtolower($info['ext']));
		if ($ext != 'jpg' && $ext != 'png' && $ext != 'gif') {
			return 'Only jpg/png/gif!';
		}
		$info['ext'] = $ext;
		$info['preview'] = $this->adminPreviewUrl();
		return true;
	}

	public function url($mods = false)
	{
		$path = $this->value();
		if (!$mods && empty($path)) {
			return parent::url();
		}
		$modified = app('tao.images')->modify($path, $mods);
		return \Storage::url($modified);
	}

	public function publicUrl($mods = false)
	{
		$mods = $this->param('mods', false);
		return $this->url($mods);
	}
	
	public function renderForAdminList()
	{
		$render = $this->callableParam(['render_in_admin_list', 'render_in_list'], null, [$this], $this->item);
		if (is_null($render)) {
			$mods = $this->param(['in_admin_list_mods', 'in_list_mods'], 'fit100x100');
			$url = $this->url();
			if ($url) {
				$murl = $this->url($mods);
				$value = "<img src='{$murl}'>";
				$render = "<a href='{$url}'>{$value}</a>";
			}
		}
		if (is_null($render)) {
			$render = '';
		}
		return $render;
	}

	public function imageSize()
	{
		$path = $this->value();
		if (empty($path)) {
			return null;
		}
		return app('tao.images')->size($path);
	}

	public function show($mods = false, $tpl = '<img src="{url}" width="{width}" height="{height}">')
	{
		$path = $this->value();
		if (empty($path)) {
			return '';
		}
		return app('tao.images')->show($path, $mods, $tpl);
	}
}
