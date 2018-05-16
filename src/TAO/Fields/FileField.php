<?php

namespace TAO\Fields;

use TAO\Text\StringTemplate;

trait FileField
{
	/**
	 * @var bool
	 */
	protected $tempId = false;

	/**
	 * @param $info
	 * @return string
	 */
	public function destinationFileName($info)
	{
		if (is_array($info)) {
			$info = (object)$info;
		}

		$fileName = $this->callableParam('generate_file_name', null, [$info]);
		if (!is_null($fileName)) {
			return $fileName;
		}

		$fileNameTemplate = $this->param('file_name_template', $this->defaultFileNameTemplate());
		$binds = $this->fileNameBinds($info);
		return StringTemplate::process($fileNameTemplate, $binds);
	}

	/**
	 * @param $info
	 * @return string
	 */
	public function destinationPath($info)
	{
		list($dir, $file) = $this->destinationDirAndName($info);
		return "{$dir}/{$file}";
	}

	/**
	 * @param $info
	 * @return string
	 */
	public function destinationDirAndName($info)
	{
		$dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
		$file = $this->destinationFilename($info);
		return [$dir, $file];
	}

	/**
	 * @return string
	 */
	public function tempId()
	{
		if (!$this->tempId) {
			$this->tempId = $this->generateTempId();
		}
		return $this->tempId;
	}

	/**
	 * @return string
	 */
	protected function generateTempId()
	{
		return uniqid($this->item->getDatatype() . '_' . $this->name);
	}

	/**
	 * @param $temp_id
	 * @return string
	 */
	public function tempDir($temp_id)
	{
		$session_id = \Session::getId();
		return "session-files/{$session_id}/{$temp_id}";
	}

	/**
	 * @param $file
	 * @param $info
	 * @return bool|mixed
	 */
	public function checkUploadedFile($file, &$info)
	{
		return $this->callableParam('check_uploaded_file', true, [$file]);
	}

	/**
	 * @param $size
	 * @return string
	 */
	public function generateHumanSize($size)
	{
		if ($size >= 10485760) {
			return ((int)round($size / 1048576)) . 'M';
		}
		if ($size >= 1048576) {
			return number_format($size / 1048576, 1) . 'M';
		}
		if ($size >= 10240) {
			return ((int)round($size / 1024)) . 'K';
		}
		if ($size >= 1024) {
			return number_format($size / 1024, 1) . 'K';
		}

		return $size . 'B';
	}

	/**
	 * @return string
	 */
	public function uploadUrl()
	{
		return $this->apiUrl('upload', ['_token' => csrf_token(), 'upload_id' => $this->tempId()]);
	}

	/**
	 * @param object $info
	 * @return array
	 */
	protected function fileNameBinds($info)
	{
		$nameWithoutExt = str_replace(".{$info->ext}", '', $info->name);
		return [
			'ext' => !empty($info->ext) ? strtolower($info->ext) : '',
			'Ext' => !empty($info->ext) ? $info->ext : '',
			'datatype' => $this->item->getDatatype(),
			'field' => $this->name,
			'id' => $this->item->getKey(),
			'filename' => $info->name,
			'translit' => \TAO\Text::process($nameWithoutExt, 'translit_for_url'),
		];
	}
	
	protected function isImage($path)
	{
		return \TAO::regexp('{\.(jpe?g|gif|png)$}i', $path);
	}
	
	protected function getImageSize($path)
	{
		$image = \Image::make(\Storage::get($path));
		if ($image) {
			$size = $image->getSize();
			return [
				'width' => $size->width,
				'height' => $size->height,
			];
		}
	}
	
	protected function checkWidthAndHeight(&$data)
	{
		if (isset($data['path']) && $this->isImage($data['path'])) {
			$size = $this->getImageSize($data['path']);
			if (is_array($size)) {
				$data = \TAO::merge($data, $size);
			}
		}
	}

	/**
	 * @return mixed
	 */
	abstract protected function defaultFileNameTemplate();
}