<?php

namespace TAO\Fields\Type;

use TAO\Fields;

/**
 * Class PublicUpload
 * @package TAO\Fields\Type
 */
class PublicUpload extends Upload
{
	use Fields\FileField;

	/**
	 * @param $request
	 */
	public function setFromRequest($request)
	{
	}

	/**
	 * @param $request
	 * @return array|string
	 */
	public function setFromRequestAfterSave($request)
	{
		$file = $request->file($this->name);
		if ($file) {
			$size = $file->getSize();
			$human_size = $this->generateHumanSize($size);
			$info = array(
				'name' => $file->getClientOriginalName(),
				'ext' => $file->getClientOriginalExtension(),
				'mime' => $file->getClientMimeType(),
				'size' => $size,
				'human_size' => $human_size,
				'preview' => '',
			);
			$check = $this->checkUploadedFile($file, $info);
			if (is_string($check)) {
				return $check;
			}
			if (is_array($check)) {
				$info = $check;
			}
			$path = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
			$fileName = $this->destinationFilename($info);
			\Storage::deleteDirectory($path);
			$file->storeAs($path, $fileName);
			$fullPath = $path . '/' . $fileName;
			$this->item[$this->name] = $fullPath;
			$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $fullPath]);
			return $info;
		}
	}
}
