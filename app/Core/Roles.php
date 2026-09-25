<?php

namespace App\Core;

/**
 * Grupos de papel da SB New Energy. Estrutura de hierarquia inspirada
 * diretamente no app/Core/Roles.php do painel EcoDiffusore (mesmo dono,
 * mesma rede de licenciados) -- 5 papéis de parceiro + admin, iguais aos de
 * lá, só que pro produto de energia em vez de dispositivo automotivo.
 *
 * Duas hierarquias independentes (não confundir uma com a outra):
 * 1) users.manager_id -- árvore de equipe regional:
 *      Vendedor/Gestor -> o Licenciado dono deles
 *      Supervisor      -> o Gerente que o cadastrou
 *      Gerente         -> topo, sem manager
 * 2) users.supervisor_id -- atribuição de suporte nacional, separada:
 *      Licenciado -> o Supervisor designado pra apoiar ele (atribuído por um
 *      Gerente depois do cadastro, não é o mesmo vínculo do manager_id)
 */
class Roles
{
    public const ADMIN = 'admin';
    public const GERENTE = 'gerente';       // suporte nacional, cadastra Supervisores, aprova onboarding
    public const SUPERVISOR = 'supervisor'; // suporte nacional, apoia Licenciados, só visualiza CRM deles
    public const LICENCIADO = 'licenciado'; // dono de uma região, cadastra Gestor/Vendedor
    public const GESTOR = 'gestor';         // gerencia uma equipe sob um Licenciado
    public const VENDEDOR = 'vendedor';     // vende, não cadastra ninguém

    public const ALL = [self::ADMIN, self::GERENTE, self::SUPERVISOR, self::LICENCIADO, self::GESTOR, self::VENDEDOR];

    // Suporte nacional: Gerente e Supervisor, que enxergam/apoiam várias redes regionais.
    public const NATIONAL_SUPPORT = [self::ADMIN, self::GERENTE, self::SUPERVISOR];

    // Só Gerente cadastra/atribui Supervisor a um Licenciado.
    public const SUPERVISOR_ASSIGNMENT = [self::ADMIN, self::GERENTE];

    // Quem gerencia uma rede/equipe regional (vê equipe, pedidos do time, etc).
    public const MANAGEMENT = [self::ADMIN, self::LICENCIADO, self::GESTOR];

    // Só o Licenciado (dono da região) cadastra Gestor/Vendedor sob ele.
    public const USER_MANAGEMENT = [self::ADMIN, self::LICENCIADO];

    // Equipe operacional (não é dona de região, mas trabalha nela).
    public const STAFF = [self::GESTOR, self::VENDEDOR];

    // Todo mundo que efetivamente vende (escopo de pedidos/comissão por vendedor).
    public const SELLER_ROLES = [self::LICENCIADO, self::GESTOR, self::VENDEDOR];

    // Quem pode ver leads/orçamentos/pedidos de uma rede regional inteira.
    public const REGIONAL_OWNER = [self::ADMIN, self::LICENCIADO];

    public static function label(string $role): string
    {
        return match ($role) {
            self::ADMIN => 'Administrador',
            self::GERENTE => 'Gerente',
            self::SUPERVISOR => 'Supervisor',
            self::LICENCIADO => 'Licenciado',
            self::GESTOR => 'Gestor',
            self::VENDEDOR => 'Vendedor',
            default => $role,
        };
    }
}
