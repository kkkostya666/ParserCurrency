<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public function fetchCurrencies()
    {
        $response = Http::get('https://www.cbr.ru/scripts/XML_daily.asp');

        if ($response->successful()) {
            $currencies = [];

            foreach (simplexml_load_string($response->body())->Valute as $valute) {
                $currencies[] = [
                    'code' => (string) $valute->CharCode,
                    'name' => (string) $valute->Name,
                    'rate' => (float) str_replace(',', '.', $valute->Value),
                ];
            }

            return $currencies;
        } else {
            throw new \Exception('Failed to fetch data from CBR');
        }
    }
}
