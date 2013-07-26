<?php
namespace elasticsearch;

if(!function_exists('elasticsearch\includeIfExists')){
	function includeIfExists($file)
	{
	    return file_exists($file) ? include $file : false;
	}
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

$loader->add(null, __DIR__.'/../src', true);

return $loader;
?>