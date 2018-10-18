<?php

namespace TAO\Components\Unisender;

class Controller
{
	/**
	 *
	 */
	public function subscribe()
	{
		$data = unserialize(\Crypt::decrypt(request('encrypted_data')));
		if (($time = $data['time'] ?? false) && $time < time() - 5) {
			$params = $data;
			unset($params['time']);
			unset($params['template']);
			unset($params['submit_url']);
			unset($params['return_url']);
			$email = request('email');
			$result = app('unisender')->addEmail($email, $params);
			if (request()->isJson()) {
				return response(json_encode($result), 200, ['Content-Type' => 'application/json']);
			} else {
				$result->email = $email;
				$url = $data['return_url'] ?? '/unisender/subscribe/ok/';
				$url .= '?data=' . (\Crypt::encrypt($result));
				return redirect($url);
			}
		}
		return 'Error';
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function ok()
	{
		$data = \Crypt::decrypt(request('data'));
		return view('unisender.ok', array(
			'email' => $data->email ?? '???@???',
			'letter_sent' => $data->result->invitation_letter_sent ?? 0,
			'person_id' => $data->result->person_id ?? 0,
		));
	}
}