<?php

namespace TAO\App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		$events = config('events', []);

		foreach ($events as $event => $listeners) {
			if (!is_array($listeners)) {
				$listeners = [$listeners];
			}

			foreach ($listeners as $listener) {
				\Event::listen($event, $listener);
			}
		}

		parent::boot();

		//
	}
}
