<?php

namespace TAO\Fields\Type;

use Intervention\Image\Exception\NotReadableException;

class Gallery extends Attaches
{
	public function inputTemplateFrom()
	{
		return 'attaches';
	}

	public function templateEntryJS()
	{
		return 'js-entry-gallery';
	}

	public function filelistClass()
	{
		return 'tao-fields-attaches-filelist tao-fields-gallery-filelist';
	}

	public function extraCSS()
	{
		return ['/tao/styles/admin-gallery.css'];
	}

	public function infoFieldsSrc()
	{
		return \TAO::merge([
			'text description' => 'Подпись',
		], $this->param('info', []));
	}

	public function isSortable()
	{
		return $this->param('sortable', true);
	}

	public function adminPreviewSize()
	{
		return $this->param('admin_preview_size', 177);
	}

	public function adminPreviewUrl()
	{
		return $this->apiUrl('preview', ['upload_id' => $this->tempId()]);
	}

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

	public function defaultTemplate()
	{
		return 'fields ~ gallery.template';
	}

	public function entryTemplate()
	{
		return 'fields ~ gallery.entry';
	}

	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['preview_mods'] = $this->param('preview_mods', 'height100');
		$context['full_mods'] = $this->param('full_mods', false);
		return $context;
	}
}
