<?php

namespace App\Core;

/**
 * Grupos de papel da SB New Energy. Inspirado no app/Core/Roles.php do painel
 * EcoDiffusore (mesmo dono, mesmo padrão de rede de licenciados) mas
 * simplificado pro que a New Energy precisa hoje: só dois níveis de parceiro
 * (Licenciado e Vendedor) em vez dos 8 papéis do EcoDiffusore. Adicionar um
 * papel novo (ex: Supervisor regional) no futuro é só criar a constante aqui
 * e incluir nos grupos certos -- não precisa mexer no resto do sistema.
 */
class Roles
{
    public const ADMIN = 'admin';
    public const LICENCIADO = 'licenciado'; // dono de uma região/rede, cadastra vendedores
    public const VENDEDOR = 'vendedor'; // vende sob um licenciado, não cadastra ninguém

    // Quem pode ver a rede toda de um licenciado (equipe, pedidos do time).
    public const MANAGEMENT = [self::ADMIN, self::LICENCIADO];

    // Quem pode cadastrar/editar vendedores (só o próprio licenciado dono deles, ou admin).
    public const USER_MANAGEMENT = [self::ADMIN, self::LICENCIADO];

    // Todo mundo que efetivamente vende (usado pra escopar pedidos por vendedor).
    public const SELLER_ROLES = [self::LICENCIADO, self::VENDEDOR];

    public static function label(string $role): string
    {
        return match ($role) {
            self::ADMIN => 'Administrador',
            self::LICENCIADO => 'Licenciado',
            self::VENDEDOR => 'Vendedor',
            default => $role,
        };
    }
}
