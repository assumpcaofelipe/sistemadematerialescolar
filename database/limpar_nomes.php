<?php

declare(strict_types=1);

/**
 * Limpa nomes de produtos que vieram concatenados com especificação técnica
 * (ex.: "RÉGUA 30 CM, com guia de leitura, confeccionada..." -> "RÉGUA 30 CM").
 *
 * A descrição original é mantida (no CSV ela já repete o nome completo + detalhes),
 * então nada de informação é perdido. Slugs são regenerados e mantidos únicos.
 *
 * Uso: php database/limpar_nomes.php
 */

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'localhost', getenv('DB_NAME') ?: 'central_pedidos');
$db = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function limpar_slug(string $texto): string
{
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = preg_replace('/[áàãâä]/u', 'a', $texto);
    $texto = preg_replace('/[éèêë]/u', 'e', $texto);
    $texto = preg_replace('/[íìîï]/u', 'i', $texto);
    $texto = preg_replace('/[óòõôö]/u', 'o', $texto);
    $texto = preg_replace('/[úùûü]/u', 'u', $texto);
    $texto = preg_replace('/[ç]/u', 'c', $texto);
    $texto = preg_replace('/[^a-z0-9\-]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto ?: 'item';
}

function slug_unico(PDO $db, string $slug, int $ignorarId = 0): string
{
    $final = $slug;
    $n = 2;
    while (true) {
        $st = $db->prepare('SELECT id FROM produtos WHERE slug = ? AND id <> ?');
        $st->execute([$final, $ignorarId]);
        if (!$st->fetchColumn()) {
            return $final;
        }
        $final = $slug . '-' . $n++;
    }
}

function extrair_nome(string $nome): string
{
    $introducers = [
        // fechamento de marca/uniões
        ' marca.:', ' marca:', ', marca',
        // períodos que abrem uma nova cláusula
        '. caixa', '. tamanho:', '. alt.:', '. padrão', '. composição',
        '. solúvel', '. lavável', '. atóxico', '. acompanha', '. produção', '. produto',
        '. ponta', '. tinta', '. corpo', '. tampa',
        '. gramatura', '. 4 ponteiras', '. rolos de espuma',
        // vírgulas que iniciam especificação
        ', para arquivamento', ', para uso', ' nos seguintes formatos',
        ', confeccionado', ', confeccionada', ', desenvolvido', ', desenvolvida', ', produzido', ', produzida',
        ', capacidade', ', formulados', ', formulado', ', resistente', ', traço', ', ponta chanfrada',
        ', corte', ', pequeno de mesa', ', pequena de mesa', ', formato', ', sem ',
        ', inflável', ', atóxico', ', indicado', ', transparente', ', modelo ', ', ideal para',
        ', com ', ', em ', ', na cor', ', c/ ', ', cola', ', tampa ',
        ', feito', ', feita', ', borda', ', apresentando', ', solúvel', ', não',
    ];

    $pos = null;
    $candidato = '';
    foreach ($introducers as $pat) {
        $p = mb_stripos($nome, $pat);
        if ($p !== false && ($pos === null || $p < $pos || ($p === $pos && mb_strlen($pat) > mb_strlen($candidato)))) {
            $pos = $p;
            $candidato = $pat;
        }
    }

    if ($pos === null || $pos < 9) {
        return trim($nome);
    }

    $antes = mb_substr($nome, 0, $pos);
    $resto = mb_substr($nome, $pos);
    if (mb_strlen($resto) < 12 && mb_strlen($nome) <= 120) {
        return trim($nome);
    }

    $antes = rtrim($antes);
    $antes = preg_replace('/[,.;\-:\s]+$/u', '', $antes);
    $antes = preg_replace('/\s{2,}/u', ' ', $antes);
    return rtrim($antes, '. ') ?: trim($nome);
}

/**
 * Extrai a cor/tonalidade que diferencia variantes do mesmo produto.
 * Ex.: "Cor Vermelho", "Cor.: Laranja", "Cor Verde claro", "cores: VERMELHA".
 * Retorna apenas 1 a 2 palavras (evita capturar trechos da especificação).
 */
function extrair_cor(?string $nome, ?string $descricao): string
{
    $texto = ($nome ?? '') . ' ' . mb_substr($descricao ?? '', 0, 200);

    // cor em maiúsculas (AZUL/PRETA/...) OU Capitalized + até 1 palavra em minúsculas (Vermelho, Verde claro)
    $palavraCor = '(?:[A-ZÁÉÍÓÚÂÊÔÃÕÇ]{2,20}|[A-ZÁÉÍÓÚÂÊÔÃÕÇ][a-záéíóúâêôãõçàèìòù]{1,20}(?:\s+[a-záéíóúâêôãõçàèìòù]{2,20})?)';

    if (preg_match('/(?:^|[.,])\s*Cor\s*[,:]?\s*(' . $palavraCor . ')/u', $texto, $m)) {
        return trim($m[1]);
    }
    if (preg_match('/\bcores:\s*(' . $palavraCor . ')/u', $texto, $m)) {
        return trim($m[1]);
    }
    return '';
}

$rows = $db->query('SELECT id, nome, slug, descricao FROM produtos ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$alterados = 0;
$relatorio = [];

foreach ($rows as $r) {
    $novoNome = extrair_nome($r['nome']);
    $novoNome = trim($novoNome);

    // Remove sufixo antigo de cor (idempotência): " - Cor XXXX ..."
    $novoNome = preg_replace('/\s*-\s*Cor\s+[^,.;]{0,60}$/u', '', $novoNome);
    $novoNome = trim($novoNome);

    $cor = extrair_cor($novoNome, $r['descricao']);
    if ($cor !== '' && !str_contains(mb_strtolower($novoNome), mb_strtolower($cor))) {
        $comCor = $novoNome . ' - Cor ' . $cor;
        if (mb_strlen($comCor) <= 130) {
            $novoNome = $comCor;
        }
    }

    if ($novoNome === $r['nome']) {
        continue;
    }

    if (mb_strlen($novoNome) > 130) {
        continue;
    }

    $novoSlug = slug_unico($db, limpar_slug($novoNome), (int) $r['id']);
    $st = $db->prepare('UPDATE produtos SET nome = ?, slug = ? WHERE id = ?');
    $st->execute([$novoNome, $novoSlug, $r['id']]);
    $alterados++;
    $relatorio[] = sprintf("[%s] (len %d -> %d)", $r['id'], mb_strlen($r['nome']), mb_strlen($novoNome))
        . "\n   antigo: " . mb_substr($r['nome'], 0, 110)
        . "\n   novo:   " . $novoNome;
}

echo "ALTERADOS: {$alterados}\n\n";
echo implode("\n", $relatorio) . "\n";

file_put_contents(
    sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'relatorio_limpeza_nomes.txt',
    "ALTERADOS: {$alterados}\n\n" . implode("\n", $relatorio)
);