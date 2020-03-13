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
		if (starts_with($path, 'data:')) {
			$image = \Image::make(base64_decode(preg_replace('{^.+;base64,}', '', $path)));
		} else {
			if (!$this->exists($path)) {
				$image = \Image::make('tao/images/fields/image/noimage/100x100.png');
			} else {
				$image = \Image::make(\Storage::get($path));
			}
		}
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
		if (empty($path) || !$mods) {
			if (null === ($url = parent::url())) {
				$url = '/tao/images/fields/image/noimage/100x100.png';
			}
			return $url;
		}
		$modified = app('tao.images')->modify($path, $mods);
		if (starts_with($modified, 'data:')) {
			return $modified;
		}
		return \Storage::url($modified);
	}

	public function publicUrl($mods = false)
	{
		$mods = $this->param('mods', false);
		return $this->url($mods);
	}
	
	public function renderForAdminList()
	{
		return $this->renderForAdmin('list');
	}
	
	public function renderForAdminView()
	{
		return $this->renderForAdmin('view');
	}
	
	public function renderForAdmin($action)
	{
		if ($action == 'view') {
			$pcall = ['render_in_admin_view', 'render_in_admin_list', 'render_in_admin'];
			$pmods = ['in_admin_view_mods', 'in_admin_list_mods', 'in_admin_mods'];
		} else {
			$pcall = ['render_in_admin_list', 'render_in_admin_view', 'render_in_admin'];
			$pmods = ['in_admin_list_mods', 'in_admin_view_mods', 'in_admin_mods'];
		}
		try {
			$render = $this->callableParam($pcall, null, [$this], $this->item);
			if (is_null($render)) {
				$mods = $this->param($pmods, 'fit100x100');
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
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
	}

	public function imageSize()
	{
		$path = $this->value();
		if (empty($path)) {
			return null;
		}
		if (!$this->isBase64() && !\Storage::exists($path)) {
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
		if (!$this->isBase64() && !\Storage::exists($path)) {
			return '';
		}
		return app('tao.images')->show($path, $mods, $tpl);
	}
}
