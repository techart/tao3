<?php return [

	'routers' => [
		'fspages' => \TAO\FSPages\Router::class,
		'admin' => \TAO\Admin\Router::class,
		'models' => \TAO\ORM\Router::class,
	],

	'fields' => [
		'dummy' => \TAO\Fields\Type\Dummy::class,
		'string' => \TAO\Fields\Type\StringField::class,
		'remember_token' => \TAO\Fields\Type\RememberToken::class,
		'date_integer' => \TAO\Fields\Type\DateInteger::class,
		'date_sql' => \TAO\Fields\Type\DateSQL::class,
		'integer' => \TAO\Fields\Type\Integer::class,
		'text' => \TAO\Fields\Type\Text::class,
		'checkbox' => \TAO\Fields\Type\Checkbox::class,
		'password' => \TAO\Fields\Type\Password::class,
		'multilink' => \TAO\Fields\Type\Multilink::class,
		'multilink_tags' => \TAO\Fields\Type\MultilinkTags::class,
		'select' => \TAO\Fields\Type\Select::class,
		'upload' => \TAO\Fields\Type\Upload::class,
		'image' => \TAO\Fields\Type\Image::class,
		'attaches' => \TAO\Fields\Type\Attaches::class,
		'documents' => \TAO\Fields\Type\Documents::class,
		'gallery' => \TAO\Fields\Type\Gallery::class,
		'public_upload' => \TAO\Fields\Type\PublicUpload::class,
		'radio' => \TAO\Fields\Type\Radio::class,
		'multicheckbox' => \TAO\Fields\Type\MultiCheckbox::class,
		'html' => \TAO\Fields\Type\Html::class,
		'coordinates' => \TAO\Fields\Type\Coordinates::class,
		'array' => \TAO\Fields\Type\ArrayField::class,
		'pairs' => \TAO\Fields\Type\Pairs::class,
		'float' => \TAO\Fields\Type\FloatField::class,
		'recaptcha' => \TAO\Fields\Type\Recaptcha::class,
	],

	'text' => [
		'processors' => [
			'markdown' => \TAO\Text\Processor\Parser\Markdown::class,
			'translit' => \TAO\Text\Processor\Translit::class,
			'translit_for_url' => \TAO\Text\Processor\TranslitForUrl::class,
			'arrays' => \TAO\Text\Processor\Parser\Arrays::class,
		]
	],

	'datatypes' => [
		'users' => \TAO\ORM\Model\User::class,
		'roles' => \TAO\ORM\Model\Role::class,
	],

];
