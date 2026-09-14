<?php $pageTitle = 'Importar estoque'; include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="h4 mb-0"><?= icon('upload', '20', '20', 'me-1') ?>Importar estoque</h2>
</div>

<div class="d-flex gap-2 mb-3">
    <a href="/admin/estoque" class="btn btn-outline-secondary btn-sm">Estoque</a>
    <a href="/admin/estoque/importar" class="btn btn-primary btn-sm">Importar</a>
    <a href="/admin/estoque/exportar" class="btn btn-outline-secondary btn-sm">Exportar</a>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Enviar arquivo CSV</div>
            <div class="card-body">
                <form method="post" action="/admin/estoque/importar" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="arquivo">Arquivo CSV *</label>
                        <input type="file" class="form-control" id="arquivo" name="arquivo"
                               accept=".csv,.txt,text/csv" required>
                        <div class="form-text">
                            Formato: <code>PRODUTO;CATEGORIA;ESTOQUE</code> (uma linha por produto).
                            A planilha exportada já vem nestes campos e pode ser editada no Excel.
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="substituir" name="substituir" value="1">
                            <label class="form-check-label" for="substituir">
                                Zerar estoque dos produtos que não estiverem no arquivo
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Importar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Formato do arquivo</div>
            <div class="card-body">
                <pre class="bg-light p-3 mb-0 rounded text-muted small">PRODUTO;CATEGORIA;ESTOQUE
Lápis Escolar;Material Escolar;50
Borracha Branca;Material Escolar;120
Caderno 10 Matérias;papelaria;0</pre>
                <div class="form-text mt-2">
                    <strong>PRODUTO</strong> = nome exato como aparece no catálogo.
                    <strong>CATEGORIA</strong> = nome exato da categoria.
                    <strong>ESTOQUE</strong> = nova quantidade, sem pontos ou vírgulas.
                </div>
                <div class="alert alert-info mt-3 mb-0 py-2 small">
                    Dica: exporte a planilha primeiro para ver os nomes exatos
                    e edite diretamente a coluna <strong>ESTOQUE</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php';