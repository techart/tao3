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
		$cb = $this->param(['render_in_admin_list', 'render_in_list'], false);
		if (is_string($cb)) {
			$cb = [$this->item, $cb];
		}
		if (is_callable($cb)) {
			return call_user_func($cb, $this);
		}
		$mods = $this->param(['in_admin_list_mods', 'in_list_mods'], 'fit100x100');
		$url = $this->url();
		$murl = $this->url($mods);
		$value = "<img src='{$murl}'>";
		return "<a href='{$url}'>{$value}</a>";
	}
}
