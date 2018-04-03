<?php

namespace TAO\Facades;

use Illuminate\Support\Facades\Facade;
use TAO\Frontend\Manager;

/**
 * Class Assets
 * @package TAO\Facades
 *
 * @method static void setMeta(string $name, string $value)
 * @method static void setMetas(array $metas)
 * @method static \Illuminate\View\View|\Illuminate\Contracts\View\Factory renderMeta()
 * @method static \Illuminate\View\View|\Illuminate\Contracts\View\Factory meta()
 * @method static void useFile(string $file, string | bool $scope)
 * @method static useBottomScript(string $file)
 * @method static string renderFile($file)
 * @method static string addLine(string $block, string $line)
 * @method static string addBottomLine(string $line)
 * @method static string textBlock(string $block)
 * @method static string bottomScripts()
 * @method static string scripts()
 * @method static string styles()
 * @method static void useLayout(string $name)
 * @method static void noLayout()
 * @method static Manager frontend(string | bool $name)
 * @method static void useFrontendStyle(string | bool $name)
 * @method static void useFrontendScript(string | bool $name)
 */
class Assets extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'tao.assets';
	}
}