<?php

namespace TAO\Components\Comments;

use Illuminate\Database\Eloquent\Builder;

class Model extends \TAO\ORM\Model
{
	protected $table = 'comments';
	protected $options;
	protected $authorObject;

	public function fields()
	{
		return array(
			'time_create' => array(
				'type' => 'date_integer',
				'default' => time(),
			),
			'time_update' => array(
				'type' => 'date_integer',
				'default' => 0,
			),
			'material' => array(
				'type' => 'string(100) index',
			),
			'parent_id' => array(
				'type' => 'integer index',
				'default' => 0,
			),
			'author' => array(
				'type' => 'integer index',
				'default' => \Auth::user() ? \Auth::user()->id : 0,
			),
			'updated_by' => array(
				'type' => 'integer index',
				'default' => 0,
			),
			'content' => array(
				'type' => 'text',
				'default' => '',
			),
		);
	}

	public function defaultOptions()
	{
		return array(
			'moderators' => 'moderators',
			'date_format' => 'd.m.Y',
			'template_list' => 'list',
			'template_add' => 'add',
			'template_branch' => 'branch',
			'template_branches' => 'branches',
		);
	}

	public function appOptions()
	{
		$cfg = config('comments', []);
		return \TAO::merge($this->defaultOptions(), $cfg);
	}

	public function options($options = [])
	{
		if (is_null($this->options)) {
			$this->options = \TAO::merge($this->appOptions(), $options);

		}
		return $this->options;
	}

	public function option($name, $default = null)
	{
		$options = $this->options();
		return isset($options[$name]) ? $options[$name] : $default;
	}

	/**
	 * @return Builder
	 */
	public function ordered()
	{
		return $this->orderBy('id');
	}

	public function renderList($context)
	{
		$template = $this->option('template_list');
		return view("comments ~ {$template}", $context);
	}

	public function getStartRows($material)
	{
		return $this->ordered()->where('material', $material)->where('parent_id', 0);
	}

	public function invoke($material, $options = [])
	{
		$context = $this->options($options);

		$query = $this->getStartRows($material);
		$count = $query->count();
		$rows = $query->get();
		$context['count'] = $count;
		$context['comments'] = $rows;
		$context['material'] = $material;
		$context['add_url'] = $this->addUrl($material);
		return $this->renderList($context);
	}

	public function getCount($material)
	{
		return $this->where('material', $material)->count();
	}

	public function renderError($message)
	{
		return view('comments ~ error', ['message' => $message]);
	}

	public function renderComment()
	{
		$context = $this->options([]);
		$context['comment'] = $this;
		return view('comments ~ comment', $context);
	}

	public function accessAdd($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		if (!$user) {
			return false;
		}
		return true;
	}

	public function accessEdit($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		if ($user) {
			if ($user->checkAccess($this->option('moderators'))) {
				return true;
			}
			if ($user->id == $this->author && $this->time_create + 1200 > time()) {
				return true;
			}
		}
		return false;
	}

	public function accessDelete($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		if ($user) {
			if ($user->checkAccess($this->option('moderators'))) {
				return true;
			}
		}
		return false;
	}

	public function addComment($material)
	{
		if (!$this->accessAdd()) {
			return $this->renderError('Access denied!');
		}

		$request = app()->request();
		$message = '';

		if ($request->has('message')) {
			$message = trim($request->get('message'));
		}

		if (empty($message)) {
			return $this->renderError('Message is empty!');
		}

		$comment = $this->newInstance();
		$comment->material = $material;
		$comment->content = $message;
		$comment->save();

		return $comment->renderComment();
	}

	public function editComment($id)
	{
		$item = $this->find($id);
		if (!$item) {
			return '-';
		}
		if (!$item->accessEdit()) {
			return '-';
		}

		$request = app()->request();
		$message = '';

		if ($request->has('message')) {
			$message = trim($request->get('message'));
		}

		if (empty($message)) {
			return '';
		}


		$item->content = $message;
		$item->time_update = time();
		$item->updated_by = \Auth::user()->id;
		$item->save();
		return $item->renderComment();
	}

	public function deleteComment($id)
	{
		$item = $this->find($id);
		if (!$item) {
			return 'not found';
		}
		if (!$item->accessDelete()) {
			return 'access denied';
		}
		$item->delete();
		return 'ok';
	}

	public function rawComment($id)
	{
		$item = $this->find($id);
		if (!$item) {
			return '';
		}
		if (!$item->accessEdit()) {
			return '';
		}
		return $item->content;
	}

	public function addUrl($material)
	{
		return "/comments/api/add/{$material}";
	}

	public function deleteUrl($id = false)
	{
		$id = $id ? $id : $this->id;
		return "/comments/api/delete/{$id}";
	}

	public function rawCommentUrl($id = false)
	{
		$id = $id ? $id : $this->id;
		return "/comments/api/raw/{$id}";
	}

	public function editUrl($id = false)
	{
		$id = $id ? $id : $this->id;
		return "/comments/api/delete/{$id}";
	}

	public function automaticRoutes()
	{
		\Route::post($this->addUrl('{material}'), function ($material) {
			return $this->addComment($material);
		});
		\Route::get($this->deleteUrl('{id}'), function ($id) {
			return $this->deleteComment($id);
		});
		\Route::get($this->rawCommentUrl('{id}'), function ($id) {
			return $this->rawComment($id);
		});
		\Route::post($this->editUrl('{id}'), function ($id) {
			return $this->editComment($id);
		});
	}

	public function author()
	{
		if (empty($this->authorObject)) {
			$this->authorObject = \TAO::datatype('users')->find($this->author);
		}
		return $this->authorObject;
	}

	public function authorName()
	{
		$author = $this->author();
		if ($author) {
			return $author->name;
		}
		return 'User removed';
	}
}
