<?php

declare(strict_types=1);

/**
 * Aplica índices adicionais de desempenho de maneira segura e
 * idempotente (só cria o que ainda não existe).
 *
 * Uso: php database/otimizar_banco.php
 */

require __DIR__ . '/../bootstrap/autoload.php';
require __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance();

$indices = [
    ['produtos', 'idx_produtos_status_estoque', ['status', 'quantidade_estoque']],
    ['pedidos',  'idx_pedidos_usuario_status',  ['usuario_id', 'status']],
    ['pedidos',  'idx_pedidos_created_at',      ['created_at']],
];

$alterados = 0;
$existentes = 0;

foreach ($indices as [$tabela, $nome, $colunas]) {
    $stmt = $db->prepare(
        'SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?'
    );
    $stmt->execute([$tabela, $nome]);
    $jaExiste = (int) $stmt->fetchColumn() > 0;

    if ($jaExiste) {
        $existentes++;
        echo "[ok] {$nome} ja existe.\n";
        continue;
    }

    $colunasSql = implode(', ', array_map(fn ($c) => "`{$c}`", $colunas));
    $db->exec("ALTER TABLE `{$tabela}` ADD INDEX `{$nome}` ({$colunasSql})");
    $alterados++;
    echo "[+ ] {$nome} criado em {$tabela}.\n";
}

echo "Resumo: {$alterados} criados, {$existentes} ja existentes.\n";