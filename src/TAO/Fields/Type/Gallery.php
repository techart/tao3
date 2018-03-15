<?php

namespace TAO\Fields\Type;

use Intervention\Image\Exception\NotReadableException;

class Gallery extends Attaches
{
	/**
	 * @return string
	 */
	public function inputTemplateFrom()
	{
		return 'attaches';
	}

	/**
	 * @return string
	 */
	public function templateEntryJS()
	{
		return 'js-entry-gallery';
	}

	/**
	 * @return string
	 */
	public function filelistClass()
	{
		return 'tao-fields-attaches-filelist tao-fields-gallery-filelist';
	}

	/**
	 * @return array
	 */
	public function extraCSS()
	{
		return ['/tao/styles/admin-gallery.css'];
	}

	/**
	 * @return mixed
	 */
	public function infoFieldsSrc()
	{
		return \TAO::merge([
			'text description' => 'Подпись',
		], $this->param('info', []));
	}

	/**
	 * @return null
	 */
	public function isSortable()
	{
		return $this->param('sortable', true);
	}

	/**
	 * @return null
	 */
	public function adminPreviewSize()
	{
		return $this->param('admin_preview_size', 177);
	}

	/**
	 * @return string
	 */
	public function adminPreviewUrl()
	{
		return $this->apiUrl('preview', ['upload_id' => $this->tempId()]);
	}

	/**
	 * @return mixed
	 */
	public function apiActionPreview()
	{
		if (app()->request()->has('path')) {
			$path = app()->request()->get('path');
			if (\Storage::exists($path)) {
				$image = false;
				try {
					$image = \Image::make(\Storage::get($path));
				} catch (NotReadableException $e) {
				}
				if ($image) {
					$size = $this->adminPreviewSize();
					return $image->resize($size, $size, function ($c) {
						$c->aspectRatio();
					})->response('jpg');
				}
			}
		}

		return \Image::make(base_path('www/tao/images/exclamation-octagon-frame.png'))->response('png');
	}

	/**
	 * @return string
	 */
	public function defaultTemplate()
	{
		return 'fields ~ gallery.template';
	}

	/**
	 * @return string
	 */
	public function entryTemplate()
	{
		return 'fields ~ gallery.entry';
	}

	/**
	 * @return array
	 */
	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['preview_mods'] = $this->param('preview_mods', 'height100');
		$context['full_mods'] = $this->param('full_mods', false);
		return $context;
	}
}
