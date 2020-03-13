<?php return [

	'frontend_env' => env('FRONTEND_ENV', false),
	
	'routers' => [
		'users' => \TAO\Users\Router::class,
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
		'multilink_ids' => \TAO\Fields\Type\MultilinkIds::class,
		'huge_multilink' => \TAO\Fields\Type\HugeMultilink::class,
		'select' => \TAO\Fields\Type\Select::class,
		'huge_select' => \TAO\Fields\Type\HugeSelect::class,
		'upload' => \TAO\Fields\Type\Upload::class,
		'image' => \TAO\Fields\Type\Image::class,
		'attaches' => \TAO\Fields\Type\Attaches::class,
		'documents' => \TAO\Fields\Type\Documents::class,
		'gallery' => \TAO\Fields\Type\Gallery::class,
		'public_upload' => \TAO\Fields\Type\PublicUpload::class,
		'public_image' => \TAO\Fields\Type\PublicImage::class,
		'radio' => \TAO\Fields\Type\Radio::class,
		'multicheckbox' => \TAO\Fields\Type\MultiCheckbox::class,
		'html' => \TAO\Fields\Type\Html::class,
		'coordinates' => \TAO\Fields\Type\Coordinates::class,
		'array' => \TAO\Fields\Type\ArrayField::class,
		'pairs' => \TAO\Fields\Type\Pairs::class,
		'float' => \TAO\Fields\Type\FloatField::class,
		'recaptcha' => \TAO\Fields\Type\Recaptcha::class,
		'iframe' => \TAO\Fields\Type\IFrame::class,
		'decimal' => \TAO\Fields\Type\Decimal::class,
	],

	'text' => [
		'processors' => [
			'markdown' => \TAO\Text\Processor\Parser\Markdown::class,
			'translit' => \TAO\Text\Processor\Translit::class,
			'insertions' => \TAO\Text\Processor\Insertions::class,
			'translit_for_url' => \TAO\Text\Processor\TranslitForUrl::class,
			'arrays' => \TAO\Text\Processor\Parser\Arrays::class,
			'typographer' => \TAO\Text\Processor\Typographer::class,
		]
	],

	'datatypes' => [
		'users' => \TAO\ORM\Model\User::class,
		'roles' => \TAO\ORM\Model\Role::class,
	],
	
	'resources_paths' => [],

	'insertions' => [
		'img' => [
			'action' => \TAO\Insertions\Img::class,
			'params' => [
				'preview_mods' => 'fit200x200',
				'full_mods' => 'fit800x800',
				'block' => 'b-public-image',
			],
		],
	],
];
