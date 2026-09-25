<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Product;

/**
 * "Calculadora de economia" -- diferente da calculadora de economia com
 * diesel do EcoDiffusore (o usuário pediu explicitamente uma calculadora
 * nova aqui). Estimativa de dimensionamento de bateria a partir do valor da
 * conta de energia, no mesmo espírito do que foi discutido na reunião de
 * kickoff (2026-09-11): uma prévia, não substitui o dimensionamento de um
 * engenheiro. Fórmula simplificada e claramente sinalizada como estimativa
 * -- a fórmula "de verdade" ainda depende de informação do Rafael (Viva
 * Bess) que nunca chegou (ver memória do projeto).
 */
class CalculatorController
{
    private const AVG_TARIFF_PER_KWH = 0.85; // R$/kWh, estimativa
    private const SAFETY_MARGIN = 1.2;

    public function show(): void
    {
        View::render('calculator', ['user' => Auth::user(), 'result' => null], 'loja');
    }

    public function calculate(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            View::render('calculator', ['user' => Auth::user(), 'result' => null, 'error' => 'Sessão expirada, tente de novo.'], 'loja');
            return;
        }

        $monthlyBill = (float) str_replace(',', '.', $_POST['monthly_bill'] ?? '0');
        $autonomyHours = (float) str_replace(',', '.', $_POST['autonomy_hours'] ?? '0');
        $propertyType = $_POST['property_type'] ?? 'residencial';

        if ($monthlyBill <= 0 || $autonomyHours <= 0) {
            View::render('calculator', [
                'user' => Auth::user(),
                'result' => null,
                'error' => 'Preencha o valor da conta e as horas de autonomia desejadas.',
                'old' => $_POST,
            ], 'loja');
            return;
        }

        $monthlyKwh = $monthlyBill / self::AVG_TARIFF_PER_KWH;
        $dailyKwh = $monthlyKwh / 30;
        $avgHourlyLoadKw = $dailyKwh / 24;
        $requiredKwh = $avgHourlyLoadKw * $autonomyHours * self::SAFETY_MARGIN;

        $category = $propertyType === 'residencial' ? 'Residencial' : 'Comercial/Industrial';
        $product = Product::smallestFitting($requiredKwh, $category) ?? Product::smallestFitting($requiredKwh);

        View::render('calculator', [
            'user' => Auth::user(),
            'result' => [
                'monthly_kwh' => $monthlyKwh,
                'required_kwh' => $requiredKwh,
                'product' => $product,
            ],
            'old' => $_POST,
        ], 'loja');
    }
}
