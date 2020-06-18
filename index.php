<?php

require "vendor/autoload.php";
use App\Service\ComissionStrategy;

$providerType = readline('Select provider: (file, api1, api2, api3, etc.): '); 

try {
	$strategy = new ComissionStrategy($providerType);
	print_r($strategy->calculate());
} catch (App\Service\BaseException $ex) {
	print_r('log level 1:' . ' ' . $ex->getMessage());
} catch (Exception $e) {
	print_r('log level 2:' . ' ' . $e->getMessage());
}
?>