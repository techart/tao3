<?php

namespace TAO\App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use TAO\Callback;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		//
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		if (Callback::isValidCallback(config('app.schedules'))) {
			Callback::instance(config('app.schedules'))->call($schedule);
			return;
		}

		if (\TAO::isIterable(config('app.schedules'))) {
			foreach (config('app.schedules') as $scheduleConfig) {
				if (Callback::isValidCallback($scheduleConfig)) {
					Callback::instance($scheduleConfig)->call($schedule);
				}
			}
		}
	}

	/**
	 * Register the Closure based commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		require base_path('routes/console.php');
	}
}
