<?php

$alertas = [];

$chaves = [
    'success'     => 'success',
    'error'       => 'danger',
    'auth_error'  => 'danger',
    'pedido_erro' => 'warning',
];

foreach ($chaves as $chaveFlash => $tipo) {
    if (!isset($_SESSION['flash'][$chaveFlash])) {
        continue;
    }
    $valor = $_SESSION['flash'][$chaveFlash];
    unset($_SESSION['flash'][$chaveFlash]);
    $emLista = is_array($valor);
    $alertas[] = [
        'tipo'  => $tipo,
        'texto' => $emLista ? 'Confira os itens abaixo:' : (string) $valor,
        'lista' => $emLista ? array_values($valor) : null,
    ];
}

if (isset($_SESSION['flash']['pedido_detalhes'])) {
    $detalhes = $_SESSION['flash']['pedido_detalhes'];
    unset($_SESSION['flash']['pedido_detalhes']);
    if (is_array($detalhes) && count($detalhes) > 0) {
        $alertas[] = [
            'tipo'  => 'danger',
            'texto' => 'Itens indisponíveis ou com estoque insuficiente:',
            'lista' => array_values($detalhes),
        ];
    }
}

if (isset($erro) && $erro !== null && $erro !== '') {
    $alertas[] = ['tipo' => 'danger', 'texto' => (string) $erro, 'lista' => null];
}

if (count($alertas) === 0) {
    return;
}

$temAlerta = false;
foreach ($alertas as $a) {
    if ($a['tipo'] !== 'success') {
        $temAlerta = true;
        break;
    }
}
$titulo = $temAlerta ? 'Atenção' : 'Tudo certo!';
?>
<div class="modal fade" id="modalFlashAlertas" tabindex="-1" aria-labelledby="modalFlashAlertasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalFlashAlertasLabel"><?= e($titulo) ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php foreach ($alertas as $alerta): ?>
                    <div class="alert alert-<?= e($alerta['tipo']) ?> py-2 mb-2">
                        <?= e($alerta['texto']) ?>
                        <?php if (!empty($alerta['lista'])): ?>
                            <ul class="mb-0 mt-1 ps-3">
                                <?php foreach ($alerta['lista'] as $item): ?>
                                    <li><?= e((string) $item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('modalFlashAlertas');
    if (el) {
        new bootstrap.Modal(el).show();
    }
});
</script>