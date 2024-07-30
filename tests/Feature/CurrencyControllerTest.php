<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class CurrencyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);
    }

    public function testIndexReturnsCurrenciesList()
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

        $response = $this->actingAs($this->user)->get('/api/currency/currencies');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'code', 'name', 'rate'
            ]
        ]);
    }

    public function testShowReturnsSpecificCurrency()
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
                </ValCurs>', 200)
        ]);

        $response = $this->actingAs($this->user)->get('/api/currency/currencies/USD');

        $response->assertStatus(200);
        $response->assertJson([
            'code' => 'USD',
            'name' => 'Р”РѕР»Р»Р°СЂ РЎРЁРђ',
            'rate' => 75.8500
        ]);
    }

    public function testConvertReturnsConvertedAmount()
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

        $response = $this->actingAs($this->user)->post('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'converted_amount' => 100 * 75.8500 / 90.1234
        ]);
    }
}
