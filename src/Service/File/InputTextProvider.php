<?php

namespace App\Service\File;

use App\Service\AbstractProvider;
use App\Service\File\Exceptions\InvalidInputException;
use App\Service\File\Exceptions\NoContentException;
use App\Service\File\Exceptions\ProviderNotExists;;

class InputTextProvider  extends AbstractProvider {

	protected $binProvider;
	protected $ratesProvider;
	protected $resource;


	const ABBREVIATIONS = [
		'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PO',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
	];

	public function __construct($binUrl, $ratesUrl, $resource)
	{
		$this->binProvider = $binUrl;
		$this->ratesProvider = $ratesUrl;
		$this->resource = $resource;
	}


	public function calculate()
	{
		$output = [];
		$rates = $this->rates();
		
		foreach ($this->parseFile() as $row) {

    		$parsedRow = json_decode($row, true);
			
			if(!$this->isRowValid($parsedRow)) {
				// log
				continue;
			}

			$output[] = $this->comission($rates, $parsedRow);
		}
		
	
		return $output;
	}

	protected function rate($rates, $currency)
	{
		return isset($rates[$currency]) && $currency !== 'EUR' ? $rates[$currency] : 0;
	}

	protected function rates()
	{
		$result = $this->getProviderData($this->ratesProvider);
		return $result ? $result['rates'] : [];
	}

	protected function bin($bin)
	{
		$result = $this->getProviderData($this->binProvider . $bin);
		return $result ? $result : [];
	}

	protected function isRowValid($parsedRow)
	{
		return $parsedRow && array_key_exists('bin', $parsedRow) && array_key_exists('amount', $parsedRow) && array_key_exists('currency', $parsedRow);
	}

	protected function comission($rates, $row)
	{
		$binResults = $this->bin($row['bin']);
		$isEu = $binResults ? in_array($binResults['country']['alpha2'], self::ABBREVIATIONS) : false; 
		$rate = $this->rate($rates, $row['currency']);
		$amount = $rate ? $row['amount'] /= $rate : $row['amount'];
		
		$comission = $amount * ($isEu ? 0.01 : 0.02);
			
		return $this->ceil($comission);
	}

	protected function getProviderData($provider)
	{
		return json_decode(@file_get_contents($provider), true);
	}

	private function parseFile()
	{
		if (!file_exists($this->resource)) {
			throw new NoContentException();
		}


    	$input = explode(PHP_EOL, file_get_contents($this->resource));

    	if (!$input) {
    		throw new InvalidInputException();
    	}


		return $input;
	}


}

?>