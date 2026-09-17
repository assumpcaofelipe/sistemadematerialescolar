<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Configuracao;

class ConfiguracaoController extends Controller
{
    public function __construct()
    {
        Auth::requireAcessoAdmin();
    }

    public function index(): void
    {
        $this->view('admin/configuracoes/index', [
            'configuracoes' => (new Configuracao())->all(),
        ]);
    }

    public function update(): void
    {
        if (!Csrf::validate()) {
            $this->flash('error', 'Sessão expirada. Tente novamente.');
            $this->redirect('/admin/configuracoes');
        }

        $model = new Configuracao();

        if (Auth::isAdmin()) {
            $whatsapp = preg_replace('/\D/', '', (string) ($_POST['whatsapp'] ?? ''));
            $model->set('whatsapp_secretaria', $whatsapp);
        }

        $this->flash('success', 'Configurações salvas.');
        $this->redirect('/admin/configuracoes');
    }
}