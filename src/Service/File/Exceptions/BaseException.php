<?php

namespace App\Service\File\Exceptions;

use Exception;

class BaseException extends Exception {
	public function __construct() {
        parent::__construct($this->message, 0, null);
    }
}

?>