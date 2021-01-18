<?php

namespace TAO\Session;

class Manager extends \Illuminate\Session\SessionManager
{
	protected function buildSession($handler)
	{
		if (config('session.encrypt')) {
			return $this->buildEncryptedSession($handler);
		} else {
			return new Store(config('session.cookie'), $handler);
		}
	}
}