<?php

namespace TAO\Fields\Type;

class Image extends Upload
{
    public function adminPreviewUrl()
    {
        return $this->apiUrl('preview', ['_token' => csrf_token(), 'upload_id' => $this->tempId()]) . '&r=' . time() . rand(1111, 9999);
    }

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
}
