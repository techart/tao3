<?php

use Faker\Generator as Faker;
use \TaoTests\Utils\SimpleDatatype as SimpleDatatype;

$factory->define(SimpleDatatype::class, function (Faker $faker) {
	return [
		'title' => $faker->name
	];
});