<?php

return [
    'default' => 'sqlite',

	'connections' => [
		'sqlite' => [
			'driver'   => 'sqlite',
            'database' => ':memory:',
			'prefix'   => ''
        ],
		'circle' => array(
			'driver'    => 'mysql',
			'host'      => '127.0.01',
			'database'  => 'circle_test',
			'username'  => 'ubuntu',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
			)
    ]
];
