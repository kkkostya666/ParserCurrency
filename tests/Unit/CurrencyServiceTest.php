<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\CurrencyService;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchCurrenciesReturnsValidData()
    {
        Http::fake([
            'www.cbr.ru/*' => Http::response('<?xml version="1.0" encoding="windows-1251"?>
            <ValCurs Date="02.03.2023" name="Foreign Currency Market">
                <Valute ID="R01235">
                    <NumCode>840</NumCode>
                    <CharCode>USD</CharCode>
                    <Nominal>1</Nominal>
                    <Name>Доллар США</Name>
                    <Value>75,8500</Value>
                </Valute>
                <Valute ID="R01239">
                    <NumCode>978</NumCode>
                    <CharCode>EUR</CharCode>
                    <Nominal>1</Nominal>
                    <Name>Евро</Name>
                    <Value>90,1234</Value>
                </Valute>
            </ValCurs>', 200)
        ]);

        $service = new CurrencyService();

        $currencies = $service->fetchCurrencies();

        $this->assertIsArray($currencies);
        $this->assertCount(2, $currencies);

        $this->assertArrayHasKey('code', $currencies[0]);
        $this->assertArrayHasKey('name', $currencies[0]);
        $this->assertArrayHasKey('rate', $currencies[0]);
        $this->assertEquals('USD', $currencies[0]['code']);
        $this->assertEquals('Р”РѕР»Р»Р°СЂ РЎРЁРђ', $currencies[0]['name']);
        $this->assertEquals(75.8500, $currencies[0]['rate']);

        $this->assertArrayHasKey('code', $currencies[1]);
        $this->assertArrayHasKey('name', $currencies[1]);
        $this->assertArrayHasKey('rate', $currencies[1]);
        $this->assertEquals('EUR', $currencies[1]['code']);
        $this->assertEquals('Р•РІСЂРѕ', $currencies[1]['name']);
        $this->assertEquals(90.1234, $currencies[1]['rate']);
    }
}
