<?php

namespace TAO\Session;

class Store extends \Illuminate\Session\Store
{
    public function migrate($destroy = false)
    {
        if ($destroy) {
            $sid = $this->getId();
            $path = "session-files/{$sid}";;
            if (\Storage::exists($path)) {
                \Storage::deleteDirectory($path);
            }
        }
        return parent::migrate($destroy);
    }
}