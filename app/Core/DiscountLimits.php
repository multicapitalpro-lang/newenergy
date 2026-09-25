<?php

namespace App\Core;

/**
 * Limite de desconto que cada papel pode dar num orçamento sem precisar de
 * aprovação -- "Liberação de preço" do EcoDiffusore. Números de exemplo
 * (fictícios, como combinado) até o usuário definir os reais.
 */
class DiscountLimits
{
    private const LIMITS = [
        Roles::VENDEDOR => 5.0,
        Roles::GESTOR => 10.0,
        Roles::LICENCIADO => 15.0,
        Roles::SUPERVISOR => 15.0,
        Roles::GERENTE => 100.0,
        Roles::ADMIN => 100.0,
    ];

    public static function forRole(string $role): float
    {
        return self::LIMITS[$role] ?? 0.0;
    }

    public static function exceeds(string $role, float $discountPercent): bool
    {
        return $discountPercent > self::forRole($role);
    }
}
