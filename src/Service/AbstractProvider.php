<?php

namespace App\Service;


abstract class AbstractProvider {


	abstract public function calculate();

	protected function ceil($value) {
		return number_format(round($value, 2), 2, '.', ' ');
	}
}

?>