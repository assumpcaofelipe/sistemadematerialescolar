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
        $whatsapp = preg_replace('/\D/', '', (string) ($_POST['whatsapp'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        $model->set('whatsapp_secretaria', $whatsapp);
        $model->set('email_secretaria', $email);

        $this->flash('success', 'Configurações salvas.');
        $this->redirect('/admin/configuracoes');
    }
}