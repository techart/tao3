<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use TAO\Fields\Field;
use TAO\Fields;

/**
 * Class Upload
 * @package TAO\Fields\Type
 */
class Upload extends Field
{
	use Fields\FileField;

	protected $newFile = false;
	protected $defaultDisk;

	public function __construct()
	{
		$this->defaultDisk = \config('filesystems.default');
	}

	/**
	 * @param Blueprint $table
	 * @return \Illuminate\Support\Fluent
	 */
	public function createField(Blueprint $table)
	{
		if ($this->isBase64()) {
			if ($args = $this->typeParamsArgs()) {
				foreach ($args as $arg) {
					if ($arg == 'longtext') {
						return $table->longText($this->name);
					} elseif ($arg == 'mediumtext') {
						return $table->mediumText($this->name);
					}
				}
			}
			return $table->text($this->name);
		}
		return $table->string($this->name, 250)->default('');
	}

	/**
	 * @param $request
	 */
	public function setFromRequest($request)
	{
		if ($request[$this->name] == 'delete') {
			$this->delete();
			$this->item[$this->name] = '';
		}
	}

	public function isBase64()
	{
		return $this->param('base64', false);
	}

	/**
	 *
	 */
	public function delete()
	{
		$file = trim($this->value());
		if (!empty($file) && \Storage::disk($this->getDisk())->exists($file)) {
			\Storage::disk($this->getDisk())->delete($file);
		}
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
		$tid = $request[$this->name];
		$path = $this->tempDir($tid);
		if (\Storage::disk($this->getDisk())->exists("{$path}/file.tmp") && \Storage::disk($this->getDisk())->exists("{$path}/info.json")) {
			$info = json_decode(\Storage::disk($this->getDisk())->get("{$path}/info.json"));
			$this->delete();
			$filePath = "{$path}/file.tmp";

			$dest = $this->setFile($filePath, $info);

			\Storage::disk($this->getDisk())->delete("{$path}/info.json");
			\Storage::disk($this->getDisk())->delete($filePath);

			$method = "setUploadFieldAfterSave";
			$rc = null;
			if (method_exists($this->item, $method)) {
				$rc = $this->item->$method($this, $dest);
			}
			if (!is_null($rc)) {
				$this->oldValue = $rc;
			} else {
				$this->oldValue = $this->item[$this->name];
				$this->item[$this->name] = $dest;
				$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
			}
		}
	}

	public function setFile($path, $info)
	{
		$dest = $this->destinationPath($info);
		if (\Storage::disk($this->getDisk())->exists($dest)) {
			\Storage::disk($this->getDisk())->delete($dest);
		}
		if ($this->isBase64()) {
			$content = is_file($path)? file_get_contents($path) : \Storage::get($path);
			$dest = "data:{$info->mime};base64,".base64_encode($content);
		} else {
			if (is_file($path)) {
				list($dir, $file) = $this->destinationDirAndName($info);
				\Storage::disk($this->getDisk())->putFileAs($dir, new File($path), $file);
			} else {
				\Storage::disk($this->getDisk())->copy($path, $dest);
			}
		}
		return $dest;
	}

	public function setDefault($value)
	{
		return parent::set($value);
	}

	public function set($path)
	{
		$this->newFile = $path;
	}

	public function processAfterSet()
	{
		if ($this->newFile) {
			$temp = false;
			if (\TAO::regexp('{^https?://}', $this->newFile)) {
				$dir = 'temp/'.uniqid();
				\Storage::disk($this->getDisk())->makeDirectory($dir);
				$temp = app('tao.http')->saveFile($this->newFile, $dir);
				$this->newFile = $temp;
			}
			$name = $this->newFile;
			$ext = '';
			if ($m = \TAO::regexp('{/([^/]+)$}', $name)) {
				$name = $m[1];
			}
			if ($m = \TAO::regexp('{\.([^.]+)$}', $name)) {
				$ext = $m[1];
			}
			$info = new \StdClass;
			$info->name = $name;
			$info->ext = $ext;
			$dest = $this->setFile($this->newFile, $info);
			$this->newFile = false;

			if ($temp) {
				\Storage::disk($this->getDisk())->delete($temp);
				\Storage::disk($this->getDisk())->deleteDirectory($dir);
			}

			return $dest;
		}
	}

	public function afterItemSave()
	{
		parent::afterItemSave();
		if ($dest = $this->processAfterSet()) {
			$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
			$this->item[$this->name] = $dest;
		}
	}

	public function apiActionDownload()
	{
		$datatype = dt(app()->request()->get('datatype'));
		$field = app()->request()->get('field');
		$item = $datatype->find(app()->request()->get('id'));
		if ($item->accessView()) {
			$file = $item->field($field)->value();
			$filename = preg_replace('{^.+/}', '', $file);
			$mime = \Storage::disk($this->getDisk())->mimeType($file);
			return \Storage::disk($this->getDisk())->download($file, 200, [
				'Content-Type' => $mime,
				'Content-Disposition' => 'inline; filename="'.$filename.'"',
			]);
		}
		return \TAO::pageNotFound();
	}

	/**
	 * @return array|bool|mixed
	 */
	public function apiActionUpload()
	{
		$tid = app()->request()->get('upload_id');
		$this->tempId = $tid;
		$dir = $this->tempDir($tid);
		if (!\Storage::disk($this->getDisk())->exists($dir)) {
			\Storage::disk($this->getDisk())->makeDirectory($dir);
		}
		$file = app()->request()->file('uploadfile');
		$size = $file->getSize();
		$human_size = $this->generateHumanSize($size);

		$info = array(
			'upload_id' => $tid,
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
		\Storage::disk($this->getDisk())->put("{$dir}/info.json", json_encode($info));
		$file->storeAs($dir, 'file.tmp', [
			'disk' => $this->getDisk(),
		]);
		return $info;
	}

	/**
	 * @return int
	 */
	public function size()
	{
		$file = $this->value();
		if (!$this->exists($file)) {
			return 0;
		}
		if (starts_with($file, 'data:')) {
			$file = preg_replace('{^.+;base64,}', '', $file);
			return strlen(base64_decode($file));
		}
		return \Storage::disk($this->getDisk())->size($file);
	}

	/**
	 * @return string
	 */
	public function humanSize()
	{
		return $this->generateHumanSize($this->size());
	}

	/**
	 * @return string
	 */
	protected function defaultFileNameTemplate()
	{
		return '{datatype}-{field}-{id}.{ext}';
	}

	/**
	 * @return bool
	 */

	public function exists($file = null)
	{
		if (null === $file) {
			$file = $this->value();
		}
		if (empty($file)) {
			return false;
		}
		if ((!starts_with($file, 'data:')) &&
			(!\Storage::disk($this->getDisk())->exists($file))) {
			return false;
		}
		return true;
	}

	/**
	 * @return bool|string
	 */
	public function url()
	{
		$file = $this->value();
		if (empty($file)) {
			return false;
		}
		if (starts_with($file, 'data:')) {
			return $file;
		}
		if (!\Storage::disk($this->getDisk())->exists($file)) {
			return null;
		}
		if ($this->param('private', false)) {
			return $this->apiUrl('download');
		}
		return \Storage::disk($this->getDisk())->url($file);
	}

	public function jsonValue()
	{
		$url = $this->url();
		if (starts_with($url, '/')) {
			$url = (request()->isSecure()? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $url;
		}
		return $url;
	}

	/**
	 * @return string
	 */
	public function renderForAdminList()
	{
		$render = $this->callableParam(['render_in_admin_list', 'render_in_list'], null, [$this], $this->item);
		if (is_null($render)) {
			$url = $this->url();
			$value = $this->value();
			$render = "<a href='{$url}'>{$value}</a>";
		}
		return $render;
	}

	public function renderWithoutTemplate()
	{
		$url = $this->url();
		$value = $this->value();
		$render = "<a href='{$url}'>{$value}</a>";
		return $render;
	}

	public function dataExportValue()
	{
		$value = $this->value();
		if (starts_with($value, 'data:')) {
			return chunk_split($value);
		}
		$path = $value;
		if (\Storage::disk($this->getDisk())->exists($path)) {
			$name = preg_replace('{^.*/}', '', $value);
			return ":{$name}\n".chunk_split(base64_encode(\Storage::disk($this->getDisk())->get($path)));
		}
	}

	public function dataImport($src)
	{
		$name = false;
		$src = trim($src);
		$p = strpos($src, "\n");
		if ($p>0) {
			$line = trim(substr($src, 0,  $p));
			if ($m = \TAO::regexp('{^:(.+)$}', $line)) {
				$name = trim($m[1]);
				$src = trim(substr($src, $p));
			}
		}
		$src = preg_replace('{\s+}sm', '', $src);
		if (starts_with($src, 'data:')) {

		} else {
			if ($name) {
				$ext = 'bin';
				$content = base64_decode($src);
				if ($m = \TAO::regexp('{\.([^.]+)$}', $name)) {
					$ext = $m[1];
				}
				$info = new \StdClass;
				$info->name = $name;
				$info->ext = $ext;
				$dest = $this->destinationPath($info);
				list($dir, $file) = $this->destinationDirAndName($info);
				\Storage::disk($this->getDisk())->put($dest, $content);
				$this->item[$this->name] = $dest;
			}
		}
	}

	/**
	 * Метод возвращает абсолютный путь до файла на сервере
	 * Использовать на свой страх и риск, корректно работает только с локальным хранилищем
	 * @return bool|string
	 */
	public function getAbsolutePath ()
	{
		if (!$this->value()) {
			return false;
		}

		return \Storage::disk($this->getDisk())->path($this->value());
	}

	/** @see https://laravel.com/api/5.6/Illuminate/Http/File.html
	 * @return Illuminate\Http\File
	 */
	public function getFileObject ()
	{
		$absolutePath = $this->getAbsolutePath();
		if( $absolutePath ){
			return new File( $absolutePath );
		}
		return false;
	}

	/**
	 * Возвращает текущий диск для хранения файлов
	 *
	 * @return string
	 */
	protected function getDisk()
	{
		return $this->param('disk') ?? $this->defaultDisk;
	}
}
