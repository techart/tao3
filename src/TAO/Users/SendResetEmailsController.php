<?php

namespace TAO\Users;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Database\Schema\Blueprint;

class SendResetEmailsController extends \TAO\Controller
{
	use SendsPasswordResetEmails {
		sendResetLinkEmail as parentSendResetLinkEmail;
	}

	public function sendResetLinkEmail()
	{
		$this->createResetTable();
		return $this->parentSendResetLinkEmail(request());
	}

	protected function createResetTable()
	{
		if (\Schema::hasTable('password_resets')) {
			return;
		}
		\Schema::create('password_resets', function (Blueprint $table) {
			$table->string('email')->index();
			$table->string('token')->index();
			$table->timestamp('created_at');
		});
	}
}
