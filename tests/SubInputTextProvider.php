<?php

namespace App;

use App\Service\File\InputTextProvider;

class SubInputTextProvider extends InputTextProvider {

    public function calculate()
	{
        $output = [];
		$rates = $this->exposedGetRates();
		
		foreach ($this->exposedParseFile() as $row) {

    		$parsedRow = json_decode($row, true);
			
			if(!$this->isRowValid($parsedRow)) {
				// log
				continue;
			}
            
            $output[] = $this->exposedComission($rates, $parsedRow);
		}
		
	
		return $output;
    }
    
    public function exposedGetRates()
    {
        return $this->rates();
    }

    public function exposedGetRate($rates, $currency)
    {
        return $this->rate($rates, $currency);
    }

    public function exposedGetBin($bin)
    {
        return $this->bin($bin);
    }

    public function exposedIsRowValid($row)
    {
        return $this->isRowValid($row);
    }

    public function exposedParseFile()
    {
        return $this->parseFile();
    }

    public function exposedComission($rates, $row)
    {
        $binResults = $this->exposedGetBin($row['bin']);
        $isEu = $binResults ? in_array($binResults['country']['alpha2'], self::ABBREVIATIONS) : false; 
        $rate = $this->exposedGetRate($rates, $row['currency']);

		$amount = $rate ? $row['amount'] / $rate : $row['amount'];
		$comission = $amount * ($isEu ? 0.01 : 0.02);
			
		return $this->ceil($comission);
    }

    public function exposedGetProviderData($provider)
    {
        return $this->getProviderData($provider);
    }

}

?>