<?php
$loader = require __DIR__.'/../../src/bootstrap.php';
$loader->add(null, __DIR__.'/../../lib', true);
$loader->add(null, __DIR__.'/../unit-tests', true);
$loader->add(null, __DIR__.'/../integration-tests', true);
?>