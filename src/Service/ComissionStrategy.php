<?php

namespace App\Service;

use Exception;
use App\Service\File\InputTextProvider;
use App\Service\File\Exceptions\ProviderNotExists;


class ComissionStrategy {


	protected $provider;


	public function __construct($type)
	{
		switch ($type) {
			case 'file':
				$this->setStrategy(new InputTextProvider('https://lookup.binlist.net/', 'https://api.exchangeratesapi.io/latest', 'input.txt'));
				break;
			default:
				throw new ProviderNotExists("Please do your implementation here " . basename(__FILE__, '.php'));
				break;

		}
	}


	public function setStrategy($provider)
	{
		$this->provider = $provider;
	}

	public function calculate()
	{
		return $this->provider->calculate();
	}

}


?>