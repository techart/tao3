<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;
use TAO\Fields;

/**
 * Class Upload
 * @package TAO\Fields\Type
 */
class Upload extends Field
{
    use Fields\FileField;

    /**
     * @param Blueprint $table
     * @return \Illuminate\Support\Fluent
     */
    public function createField(Blueprint $table)
    {
        return $table->string($this->name, 250);
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

    public function delete()
    {
        $file = trim($this->value());
        if (!empty($file) && \Storage::exists($file)) {
            \Storage::delete($file);
        }
    }

    /**
     * @param $request
     */
    public function setFromRequestAfterSave($request)
    {
        $tid = $request[$this->name];
        $path = $this->tempDir($tid);
        if (\Storage::exists("{$path}/file") && \Storage::exists("{$path}/info.json")) {
            $info = json_decode(\Storage::get("{$path}/info.json"));
            $this->delete();
            $dest = $this->destinationPath($info);
            if (\Storage::exists($dest)) {
                \Storage::delete($dest);
            }
            \Storage::copy("{$path}/file", $dest);
            \Storage::delete("{$path}/info.json");
            \Storage::delete("{$path}/file");
            $this->item[$this->name] = $dest;
            $this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
        }
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
        \Storage::put("{$dir}/info.json", json_encode($info));
        $file->storeAs($dir, 'file');
        return $info;
    }

    /**
     * @return int
     */
    public function size()
    {
        $file = $this->value();
        if (empty($file)) {
            return 0;
        }
        return \Storage::size($file);
    }

    /**
     * @return string
     */
    public function humanSize()
    {
        return $this->generateHumanSize($this->size());
    }

    protected function defaultFileNameTemplate()
    {
        return '{datatype}-{field}-{id}.{ext}';
    }


    /**
     * @return bool
     */
    public function url()
    {
        $file = $this->value();
        if (empty($file)) {
            return false;
        }
        return \Storage::url($file);
    }

    public function renderForAdminList()
    {
        $url = $this->url();
        $value = $this->value();
        return "<a href='{$url}'>{$value}</a>";
    }
}
