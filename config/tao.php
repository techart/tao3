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

	'services' => [
		'binds' => [
			'pdf' => \TAO\Foundation\Pdf::class,
			'scss' => \TAO\Foundation\Scss::class,
			'tao.http' => \TAO\Foundation\HTTP::class,
			'tao.mail.transport' => \TAO\Mail\PHPTransport::class,
		],

		'singletons' => [
			'context' => \TAO\Foundation\AppContext::class,
			'sitemap.manager' => \TAO\Components\Sitemap\Manager::class,
			'session' => '*makeSessionService',
			'tao' => '*makeTaoService',
			'tao.admin' => \TAO\Admin::class,
			'tao.assets' => \TAO\Foundation\Assets::class . '*init',
			'tao.fields' => \TAO\Fields::class . '*init',
			'tao.images' => \TAO\Foundation\Images::class . '*init',
			'tao.view' => \TAO\View::class . '*init',
			'tao.utils'=> \TAO\Foundation\Utils::class,
			'view.finder' => '*makeViewFinderService',
			'unisender' => [\TAO\Components\Unisender\API::class, 'makeInstance'],
			'url' => '*makeUrlService',
		],
	],
];
