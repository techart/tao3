<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class UsersCreate
 * @package TAO\Console\Commands
 */
class UsersCreate extends Command
{
	protected $signature = 'users:create';

	protected $description = 'Create user';

	/**
	 *
	 */
	public function handle()
	{
		$name = false;
		$email = $this->ask("Email");

		if ($m = \TAO::regexp('{^([^@]+)@}', $email)) {
			$name = trim($m[1]);
		} else {
			$this->error('Invalid E-Mail');
			return;
		}

		$p1 = $this->secret("Password");
		$p2 = $this->secret("Confirm password");
		if ($p1 != $p2) {
			$this->error('Password mismatch');
			return;
		}
		$p = bcrypt($p1);
		$isSuperAdmin = $this->confirm("User is admin?", true);
		$user = \TAO::datatype('users')->newInstance();
		$user->name = $name;
		$user->email = $email;
		$user->password = $p;
		$user->is_admin = $isSuperAdmin;
		$user->save();
	}
}