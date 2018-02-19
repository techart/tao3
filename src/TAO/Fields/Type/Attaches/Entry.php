<?php

namespace TAO\Fields\Type\Attaches;

class Entry implements \ArrayAccess
{
    protected $data = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function path($mods = false)
    {
        if ($mods) {
            return \Images::modify($this->data['path'], $mods);
        }
        return $this->data['path'];
    }

    public function url($mods = false)
    {
        if ($mods) {
            return \Storage::url($this->path($mods));
        }
        return $this->data['url'];
    }

    public function name()
    {
        return $this->data['name'];
    }

    public function ext()
    {
        $name = $this->data['name'];
        if ($m = \TAO::regexp('{\.([a-z0-9]+)$}i', $name)) {
            return strtolower($m[1]);
        }
    }

    public function isImage()
    {
        return in_array($this->ext(), ['jpg', 'jpeg', 'gif', 'png', 'svg']);
    }

    public function info($name = false)
    {
        $info = $this->data['info'];
        if (is_string($name)) {
            return $info->$name;
        }
        return $info;
    }

    public function previewPath($mods)
    {
        if (!$this->isImage()) {
            return;
        }
        return \Images::modify($this->path, $mods);
    }

    public function previewUrl($mods)
    {
        return \Storage::url($this->previewPath($mods));
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }
}