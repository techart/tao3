<?php

namespace TAO\Session;

class Manager extends \Illuminate\Session\SessionManager
{
	protected function buildSession($handler)
	{
		if ($this->app['config']['session.encrypt']) {
			return $this->buildEncryptedSession($handler);
		} else {
			return new Store($this->app['config']['session.cookie'], $handler);
		}
	}
}