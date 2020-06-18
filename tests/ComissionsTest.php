<?php

namespace App;

use PHPUnit\Framework\TestCase;
use App\Service\File\Exceptions\InvalidInputException;
use App\Service\File\Exceptions\NoContentException;
use App\Service\File\Exceptions\ProviderNotExists;
use App\Service\ComissionStrategy;
use App\Service\File\InputTextProvider;

class ComissionsTest extends TestCase
{
    public function testComissionFailedIfWrongInput()
    {
        $this->expectException(ProviderNotExists::class);
        $strategy = new ComissionStrategy('f');
        $strategy->calculate();
    }

    public function testComissionFailedIfFileDoesNotExists()
    {
        $this->expectException(NoContentException::class);
        $strategy = new InputTextProvider('https://lookup.binlist.net/', 'https://api.exchangeratesapi.io/latest', 'i.txt');
        $strategy->calculate();
    }
    

    public function testComissionsAreReturnedBasedOnInputData()
    {
        $sut = $this
            ->getMockBuilder(SubInputTextProvider::class)
            ->setMethods([
                'exposedIsRowValid',
                'exposedParseFile',
                'exposedGetRates',
                'exposedGetRate',
                'exposedGetBin',
                'exposedGetProviderData',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $parsedFile = [
            '{"bin":"45417360","amount":"10000.00","currency":"JPY"}',
            '{"bin":"4745030","amount":"130.00","currency":"GBP"}',
        ];

        $sut->method('exposedParseFile')
            ->willReturn($parsedFile);

        $sut
            ->expects($this->any())
            ->method('exposedIsRowValid')
            ->willReturn(true);

        $rates = json_decode('{
            "rates":{
                "CAD":1.5201,
                "HKD":8.7039,
                "ISK":152.4,
                "PHP":56.273,
                "DKK":7.456,
                "HUF":344.5,
                "CZK":26.561,
                "AUD":1.6292,
                "RON":4.8355,
                "SEK":10.5123,
                "IDR":15948.04,
                "INR":85.5505,
                "BRL":5.8521,
                "RUB":78.1853,
                "HRK":7.546,
                "JPY":120.65,
                "THB":35.055,
                "CHF":1.0669,
                "SGD":1.565,
                "PLN":4.4467,
                "BGN":1.9558,
                "TRY":7.7012,
                "CNY":7.9602,
                "NOK":10.705,
                "NZD":1.7395,
                "ZAR":19.2289,
                "USD":1.1232,
                "MXN":24.9416,
                "ILS":3.8747,
                "GBP":0.89448,
                "KRW":1362.21,
                "MYR":4.8095
            },
            "base":"EUR",
            "date":"2020-06-17"
        }', true);

        $sut
            ->expects($this->any())
            ->method('exposedGetRates')
            ->willReturn($rates);

        $sut->expects($this->any())
             ->method('exposedGetBin')
             ->will($this->onConsecutiveCalls(
                [
                    'country' => [
                        'alpha2' => 'JP'
                    ]
                ],
                [
                    'country' => [
                        'alpha2' => 'GB'
                    ]
                ],
            ));

        $rts = $sut->exposedGetRates();

        $this->assertArrayHasKey('rates', $rts);
        $this->assertCount(32, $rts['rates']);

        $sut->expects($this->any())
             ->method('exposedGetRate')
             ->will($this->onConsecutiveCalls($rts['rates']['JPY'], $rts['rates']['GBP']));

        $result = $sut->calculate();
        
        $this->assertCount(2, $result);
        $this->assertEquals(1.66, $result[0]);
        $this->assertEquals(2.91, $result[1]);
    }

    public function testCheckIfWrongInputRowIsSkippedFromResults()
    {
        $sut = $this
            ->getMockBuilder(SubInputTextProvider::class)
            ->setMethods([
                'exposedIsRowValid',
                'exposedParseFile',
                'exposedGetRates',
                'exposedGetRate',
                'exposedGetBin',
                'exposedGetProviderData',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $parsedFile = [
            '{"bin":"45417360","amount":"10000.00","currency":"JPY"}',
            '{"bin":"4745030","amount":"130.00","currecy":"GBP"}',
        ];

        $sut->method('exposedParseFile')
            ->willReturn($parsedFile);

        $this->assertEquals(1, count($sut->calculate()));
    }

    public function testCheckIfComissionIsCalculatedIfNoBinResults()
    {
        $sut = $this
            ->getMockBuilder(SubInputTextProvider::class)
            ->setMethods([
                'exposedIsRowValid',
                'exposedParseFile',
                'exposedGetRates',
                'exposedGetRate',
                'exposedGetBin',
                'exposedGetProviderData',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $parsedFile = [
            '{"bin":"dummy","amount":"52.00","currency":"RON"}',
        ];

        $sut->method('exposedParseFile')
            ->willReturn($parsedFile);

            $rates = json_decode('{
                "rates":{
                    "CAD":1.5201,
                    "HKD":8.7039,
                    "ISK":152.4,
                    "PHP":56.273,
                    "DKK":7.456,
                    "HUF":344.5,
                    "CZK":26.561,
                    "AUD":1.6292,
                    "RON":4.8355,
                    "SEK":10.5123,
                    "IDR":15948.04,
                    "INR":85.5505,
                    "BRL":5.8521,
                    "RUB":78.1853,
                    "HRK":7.546,
                    "JPY":120.65,
                    "THB":35.055,
                    "CHF":1.0669,
                    "SGD":1.565,
                    "PLN":4.4467,
                    "BGN":1.9558,
                    "TRY":7.7012,
                    "CNY":7.9602,
                    "NOK":10.705,
                    "NZD":1.7395,
                    "ZAR":19.2289,
                    "USD":1.1232,
                    "MXN":24.9416,
                    "ILS":3.8747,
                    "GBP":0.89448,
                    "KRW":1362.21,
                    "MYR":4.8095
                },
                "base":"EUR",
                "date":"2020-06-17"
            }', true);
    
            $sut
                ->expects($this->any())
                ->method('exposedGetRates')
                ->willReturn($rates);


            $sut->expects($this->once())
                ->method('exposedGetRate')
                ->willReturn($rates['rates']['RON']);

            $result = $sut->calculate();

            $this->assertCount(1, $result);
            $this->assertEquals(0.22, $result[0]);
    }
}
