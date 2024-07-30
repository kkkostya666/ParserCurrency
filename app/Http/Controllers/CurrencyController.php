<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Currency API",
 *     version="1.0.0",
 *     description="API для работы с валютами"
 * )
 */
class CurrencyController extends Controller
{
    public function __construct(protected CurrencyService $currencyService) {}

    /**
     * @OA\Get(
     *     path="/api/currency/currencies",
     *     operationId="getCurrenciesList",
     *     tags={"Currencies"},
     *     summary="Get list of currencies",
     *     description="Returns a list of all currencies",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={{"api_key": {}}}
     * )
     */
    public function index()
    {
        return response()->json($this->currencyService->fetchCurrencies());
    }

    /**
     * @OA\Get(
     *     path="/api/currency/currencies/{code}",
     *     operationId="getCurrencyByCode",
     *     tags={"Currencies"},
     *     summary="Get a specific currency",
     *     description="Returns a single currency based on the code",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Currency code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *     ),
     *     @OA\Response(response=404, description="Currency not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={{"api_key": {}}}
     * )
     */
    public function show($code)
    {
        $currency = collect($this->currencyService->fetchCurrencies())->firstWhere('code', $code);

        if ($currency) {
            return response()->json($currency);
        } else {
            return response()->json(['error' => 'Currency not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/currency/convert",
     *     operationId="convertCurrency",
     *     tags={"Currencies"},
     *     summary="Convert currency",
     *     description="Convert from one currency to another",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="from", type="string", example="USD"),
     *             @OA\Property(property="to", type="string", example="EUR"),
     *             @OA\Property(property="amount", type="number", format="float", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="converted_amount", type="number", example=85.1234)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Invalid currency code"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     security={{"api_key": {}}}
     * )
     */
    public function convert(Request $request)
    {
        $currencies = $this->currencyService->fetchCurrencies();
        $fromCurrency = collect($currencies)->firstWhere('code', $request->input('from'));
        $toCurrency = collect($currencies)->firstWhere('code', $request->input('to'));

        if ($fromCurrency && $toCurrency) {
            $amountInRub = $request->input('amount') * $fromCurrency['rate'];

            return response()->json(['converted_amount' => $amountInRub / $toCurrency['rate']]);
        } else {
            return response()->json(['error' => 'Invalid currency code'], 404);
        }
    }
}
