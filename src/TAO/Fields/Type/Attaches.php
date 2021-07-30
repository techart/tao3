<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Callback;
use TAO\Fields\Field;
use TAO\Fields\FileField;
use TAO\Fields\Type\Attaches\Entry;
use Illuminate\Http\File;

class Attaches extends StringField implements \IteratorAggregate
{
	use FileField;

	/**
	 * @param Blueprint $table
	 * @param bool $column
	 * @return \Illuminate\Support\Fluent
	 */
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column ? $column : $this->name;
		return $table->text($column);
	}

	/**
	 * @return string
	 */
	protected function defaultFileNameTemplate()
	{
		return '{translit}.{ext}';
	}

	/**
	 * @return array
	 */
	public function defaultInfo()
	{
		$defs = [];
		foreach ($this->infoFields() as $name => $data) {
			$defs[$name] = $data['default'];
		}
		return $defs;
	}

	protected function createEntry($data)
	{
		return new Entry($data);
	}

	/**
	 * @param bool $raw
	 * @return array
	 */
	public function value($raw = false)
	{
		$defs = $this->defaultInfo();
		$value = parent::value();
		if (starts_with($value, '{')) {
			$value = (array)json_decode($value);
		} else {
			$value = (array)unserialize($value);
		}
		$out = [];
		if (is_array($value)) {
			foreach ($value as $key => $data) {
				$data = (array)$data;
				$path = $data['path'] ?? false;
				if (\Storage::exists($path)) {
					$data['key'] = $key;
					$data['new'] = false;
					$data['url'] = $this->fileUrl($data['path'], $key);
					if (!isset($data['info'])) {
						$data['info'] = $defs;
					}
					if ($raw) {
						$out[$key] = $data;
					} else {
						$out[$key] = $this->createEntry($data);
					}
				}
			}
		}
		return $out;
	}
	
	public function fileUrl($path, $key = '')
	{
		if ($this->param('private', false)) {
			return $this->apiUrl('download', [
				'key' => $key,
			]);
		}
		return \Storage::url($path);
	}
	
	public function apiActionDownload()
	{
		$key = app()->request()->get('key');
		$datatype = dt(app()->request()->get('datatype'));
		$field = app()->request()->get('field');
		$item = $datatype->find(app()->request()->get('id'));
		if ($item->accessView()) {
			$files = $item->field($field)->value();
			if ($file = $files[$key] ?? false) {
				$path = $file['path'];
				$filename = preg_replace('{^.+/}', '', $path);
				$mime = \Storage::mimeType($path);
				return \Storage::download($path, 200, [
					'Content-Type' => $mime,
					'Content-Disposition' => 'inline; filename="'.$filename.'"',
				]);
			}
		}
		return \TAO::pageNotFound();
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->value());
	}

	/**
	 * @return string
	 */
	public function renderFilelistJSON()
	{
		return json_encode((object)$this->value(true));
	}

	/**
	 * @return null
	 */
	public function isSortable()
	{
		return $this->param('sortable', false);
	}

	/**
	 * @return null
	 */
	public function infoFieldsSrc()
	{
		return $this->param('info', []);
	}

	/**
	 * @return array
	 */
	public function infoFields()
	{
		$out = [];
		foreach ($this->infoFieldsSrc() as $name => $data) {
			if (is_array($data)) {
				$out[$name] = $data;
			} else {
				$label = $data;
				$type = 'string';
				if ($m = \TAO::regexp('{^(.+)\s+(.+)$}', $name)) {
					$type = strtolower(trim($m[1]));
					$name = trim($m[2]);
				}
				$out[$name] = [
					'type' => $type,
					'name' => $name,
					'label' => $label,
				];
			}
			$type = isset($out[$name]['type']) ? $out[$name]['type'] : 'string';
			$default = isset($out[$name]['default']) ? $out[$name]['default'] : null;
			if (is_null($default)) {
				if ($type == 'date') {
					$default = date('d.m.Y');
				} elseif ($type == 'checkbox') {
					$default = 0;
				} else {
					$default = '';
				}
			}
			$out[$name]['type'] = $type;
			$out[$name]['default'] = $default;
		}
		return $out;
	}

	/**
	 * @return bool
	 */
	public function withInfo()
	{
		$fields = $this->infoFields();
		return !empty($fields);
	}

	/**
	 * @param $name
	 * @return string
	 */
	public function infoFieldId($name)
	{
		return "ifield_{$this->name}_{$name}";
	}

	/**
	 * @return string
	 */
	public function templateJS()
	{
		return 'js';
	}

	/**
	 * @return string
	 */
	public function templateEditInfoJS()
	{
		return 'js-info';
	}

	/**
	 * @return string
	 */
	public function templateEntryJS()
	{
		return 'js-entry';
	}

	/**
	 * @return string
	 */
	public function templateFilelistJS()
	{
		return 'js-filelist';
	}

	/**
	 * @return string
	 */
	public function filelistClass()
	{
		return 'tao-fields-attaches-filelist';
	}

	/**
	 * @return array
	 */
	public function extraCSS()
	{
		return [];
	}

	/**
	 * @return array
	 */
	public function extraJS()
	{
		return [];
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
		$out = [];
		$files = (array)json_decode($request[$this->name]);
		$dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
		$dir = "{$dir}/{$this->name}";
		$exists = [];
		foreach ($files as $key => $data) {
			$data = (array)$data;
			if (isset($data['name']) && isset($data['path'])) {
				$name = $data['name'];
				$path = $data['path'];
				$new = isset($data['new']) ? $data['new'] : false;

				if ($new) {
					$newPath = "{$dir}/{$name}";
					if (\Storage::exists($newPath)) {
						\Storage::delete($newPath);
					}
					\Storage::copy($path, $newPath);
					\Storage::delete($path);
					$data['path'] = $newPath;
				}

				unset($data['url']);
				unset($data['new']);
				unset($data['error']);
				unset($data['key']);

				$this->checkWidthAndHeight($data);

				$exists[$name] = $name;

				$out[$key] = $data;
			}
		}

		foreach (\Storage::files($dir) as $file) {
			$filename = basename($file);
			if (!isset($exists[$filename])) {
				\Storage::delete($file);
			}
		}

		$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => serialize($out)]);
		$this->item[$this->name] = serialize($out);
	}

	/**
	 * Добавление файла в список (для вызова из скрипта)
	 * Если передан параметр $body, то тело файла будет взято из него, а из $path - только имя для сохранения
	 *
	 * @param $path
	 * @param array $info
	 * @param $body
	 */
	public function add($path, $info = [], $body = false)
	{
		$ext = 'bin';
		$fileName = strtolower(preg_replace('{^.*/}', '', $path));
		if ($m = \TAO::regexp('{\.([a-z0-9]+)$}', $fileName)) {
			$ext = $m[1];
		}
		$files = unserialize($this->item[$this->name]);
		$files = is_array($files) ? $files : [];
		foreach (array_keys($files) as $key) {
			$name = $files[$key]['name'];
			if ($name == $fileName) {
				unset($files[$key]);
			}
		}
		$key = 'f' . md5($path);
		$dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
		$dir = "{$dir}/{$this->name}";
		$dest = "{$dir}/{$fileName}";

		if (\Storage::exists($dest)) {
			\Storage::delete($dest);
		}

		if ($body) {
			\Storage::put("{$dir}/{$fileName}", $body);
		} elseif (\TAO::regexp('{^https?://}', $path)) {
			$dest = app('tao.http')->saveFile($path, $dir);
		} elseif (is_file($path)) {
			\Storage::putFileAs($dir, new File($path), $fileName);
		} else {
			\Storage::copy($path, "{$dir}/{$fileName}");
		}

		$data = array(
			'path' => $dest,
			'name' => $fileName,
			'info' => \TAO::merge($this->defaultInfo(), $info),
		);

		$files[$key] = $data;
		$this->item[$this->name] = serialize($files);
		$this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => serialize($files)]);
	}


	/**
	 * @return array|bool|mixed
	 */
	public function apiActionUpload()
	{
		$tid = app()->request()->get('upload_id');
		$this->tempId = $tid;
		$dir = $this->tempDir($tid);
		if (!\Storage::exists($dir)) {
			\Storage::makeDirectory($dir);
		}
		$files = app()->request()->file('uploadfile');
		if (!is_array($files)) {
			$files = array($files);
		}

		$returnInfo = array();
		foreach ($files as $file) {
			$returnInfo['files'][] = $this->uploadFile($file, $tid, $dir);
		}

		return $returnInfo;
	}

	/**
	 * @param $file
	 * @param $tid
	 * @param $dir
	 * @return array|bool
	 */
	protected function uploadFile($file, $tid, $dir)
	{
		$size = $file->getSize();
		$human_size = $this->generateHumanSize($size);

		$info = array(
			'upload_id' => $tid,
			'name' => $file->getClientOriginalName(),
			'ext' => $file->getClientOriginalExtension(),
			'mime' => $file->getClientMimeType(),
			'size' => $size,
			'human_size' => $human_size,
			'new' => true,
			'preview' => '',
		);
		$check = $this->checkUploadedFile($file, $info);
		if (is_string($check)) {
			return $check;
		}
		if (is_array($check)) {
			$info = $check;
		}
		$name = (string)$this->destinationFileName($info);
		$dir = rtrim($dir, '/');
		$path = "{$dir}/{$name}";
		$file->storeAs($dir, $name);

		$key = 'f' . md5($path);

		return [
			'path' => $path,
			'name' => basename($path),
			'url' => false,
			'new' => true,
			'key' => $key,
			'info' => $this->defaultInfo(),
		];
	}

	/**
	 * @return mixed|null
	 */
	public function renderableEntries()
	{
		return $this->callParam('renderable_entries', function () {
			return $this->value();
		});
	}

	public function countRenderableEntries()
	{
		return count($this->renderableEntries());
	}

	/**
	 * @return mixed|null
	 */
	public function renderable()
	{
		return $this->callParam('renderable', function () {
			$entries = $this->renderableEntries();
			return count($entries) > 0;
		});
	}

	/**
	 * @return null
	 */
	public function containerClass()
	{
		return $this->param('container_class', "b-{$this->type}");
	}

	/**
	 * @return null
	 */
	public function entryClass()
	{
		return $this->param('entry_class', $this->containerClass() . '__entry');
	}

	/**
	 * @return string
	 */
	public function entryTemplate()
	{
		return 'fields ~ attaches.entry';
	}

	/**
	 * @return array
	 */
	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['entry_template'] = $this->entryTemplate();
		$context['entry_class'] = $this->entryClass();
		$context['container_class'] = $this->containerClass();
		return $context;
	}
	
	public function jsonValue()
	{
		$out = [];
		foreach($this->value() as $key => $entry) {
			$out[$key] = [
				'url' => (request()->isSecure()? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $entry->url(),
				'name' => $entry->name(),
				'ext' => $entry->ext(),
				'info' => $entry->info(),
			];
		}
		return $out;
	}

	public function dataExportValue()
	{
		$out = '';
		foreach ($this->value() as $data) {
			$name = $data['name'] ?? false;
			$path = $data['path'] ?? false;
			$info = $data['info'] ?? [];
			if ($name && $path && \Storage::exists($path)) {
				$out .= "\n*name={$name}";
				$out .= "\n*info=" . base64_encode(serialize($info));
				$out .= "\n*file";
				$out .= "\n" . trim(chunk_split(base64_encode(\Storage::get($path))));
				$out .= "\n*endfile";
			}
		}
		return $out;
	}

	public function dataImport($src)
	{
		foreach (explode('*endfile', $src) as $part) {
			if ($path = trim($part)) {
				$name = false;
				$info = [];
				$fileSrc = '';
				foreach (explode("\n", $part) as $line) {
					if ($line = trim($line)) {
						if ($m = \TAO::regexp('{^\*(name|info)=(.+)$}', $line)) {
							$key = $m[1];
							$value = trim($m[2]);
							if ($key == 'name') {
								$name = $value;
							} elseif ($key == 'info') {
								$info = unserialize(base64_decode($value));
							}
						} elseif (!starts_with($line, '*')) {
							$fileSrc .= $line;
						}
					}
				}

				if ($name && $fileSrc) {
					$this->add($name, $info, base64_decode($fileSrc));
				}
			}
		}
	}
	
	public function defaultTemplate()
	{
		return 'fields ~ attaches.template';
	}
}
