<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[áàãâä]/u', 'a', $text);
    $text = preg_replace('/[éèêë]/u', 'e', $text);
    $text = preg_replace('/[íìîï]/u', 'i', $text);
    $text = preg_replace('/[óòõôö]/u', 'o', $text);
    $text = preg_replace('/[úùûü]/u', 'u', $text);
    $text = preg_replace('/[ç]/u', 'c', $text);
    $text = preg_replace('/[^a-z0-9\-]+/', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'item';
}

function csrf_field(): string
{
    return \App\Core\Csrf::field();
}

/**
 * Retorna um ícone SVG inline.
 * Uso: icon('carrinho') ou icon('carrinho', '16', '16', 'me-1')
 */
function icon(string $nome, string $w = '20', string $h = '20', string $extraClass = ''): string
{
    $class = 'icon-svg' . ($extraClass !== '' ? ' ' . $extraClass : '');

    $icons = [
        'wrench' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',

        'grid' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',

        'inbox' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',

        'tag' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>',

        'settings' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',

        'user-plus' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>',

        'user-shield' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>',

        'log-out' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>',

        'chevron-down' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="m6 9 6 6 6-6"/></svg>',

        'arrow-left' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>',

        'plus' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',

        'book' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>',

        'cart' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',

        'package' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',

        'line-chart' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',

        'bar-chart' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="8" rx="1"/><rect x="14" y="6" width="3" height="12" rx="1"/></svg>',

        'school' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M12 3L1 9l11 6 9-4.91V17"/><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82Z"/></svg>',

        'alert-triangle' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',

        'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',

        'party' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M5.8 11.3 2 22l10.7-3.79"/><path d="M4 3h.01"/><path d="M22 8h.01"/><path d="M15 2h.01"/><path d="M22 20h.01"/><path d="m22 2-2.24.75a2.9 2.9 0 0 0-1.96 3.12v0c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10"/><path d="m22 13-.82-.33c-.86-.34-1.82.2-1.98 1.11v0c-.11.7-.72 1.22-1.43 1.22H17"/><path d="m11 2 .33.82c.34.86-.2 1.82-1.11 1.98v0C9.52 4.9 9 5.52 9 6.23V7"/><path d="M11 13c1.9 1.9 2.44 3.9 1.58 5.09"/><path d="m22 22-1.74-5.24c-.34-.86.2-1.82 1.11-1.98v0a1.62 1.62 0 0 1 1.43 1.22l.3.9"/></svg>',

        'message-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$class.'"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',

        'circle-yellow' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" class="'.$class.'"><circle cx="12" cy="12" r="8" fill="currentColor"/></svg>',

        'circle-blue' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" class="'.$class.'"><circle cx="12" cy="12" r="8" fill="currentColor"/></svg>',

        'circle-red' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 24 24" class="'.$class.'"><circle cx="12" cy="12" r="8" fill="currentColor"/></svg>',
    ];

    return $icons[$nome] ?? '';
}

function pagina_atual(): int
{
    return max(1, (int) ($_GET['pagina'] ?? 1));
}

function total_paginas(int $total, int $porPagina): int
{
    return max(1, (int) ceil($total / $porPagina));
}

/**
 * Renderiza a navegação de paginação (Bootstrap 5).
 * Preserva parâmetros de busca/filtro informados em $query.
 */
function paginacao_html(int $total, int $porPagina, int $pagina, string $base, array $query = []): string
{
    $totalPaginas = total_paginas($total, $porPagina);

    if ($totalPaginas <= 1) {
        return '';
    }

    $query['pagina'] = '{PAGINA}';
    $baseUrl = rtrim($base, '/');

    $links = '<ul class="pagination paginacao justify-content-center">';

    $anterior = max(1, $pagina - 1);
    $qr = str_replace('{PAGINA}', (string) $anterior, http_build_query($query));
    $desabilitado = $pagina <= 1 ? ' disabled' : '';
    $links .= '<li class="page-item' . $desabilitado . '"><a class="page-link" href="' . e($baseUrl . '?' . $qr) . '">&laquo; Anterior</a></li>';

    $inicio = max(1, $pagina - 2);
    $fim = min($totalPaginas, $pagina + 2);
    for ($i = $inicio; $i <= $fim; $i++) {
        $qr = str_replace('{PAGINA}', (string) $i, http_build_query($query));
        $ativo = $i === $pagina ? ' active' : '';
        $links .= '<li class="page-item' . $ativo . '"><a class="page-link" href="' . e($baseUrl . '?' . $qr) . '">' . $i . '</a></li>';
    }

    $proxima = min($totalPaginas, $pagina + 1);
    $qr = str_replace('{PAGINA}', (string) $proxima, http_build_query($query));
    $desabilitado = $pagina >= $totalPaginas ? ' disabled' : '';
    $links .= '<li class="page-item' . $desabilitado . '"><a class="page-link" href="' . e($baseUrl . '?' . $qr) . '">Próxima &raquo;</a></li>';

    $links .= '</ul>';
    return $links;
}