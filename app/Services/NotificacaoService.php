<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Configuracao;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class NotificacaoService
{
    public function emailNovoPedido(int $numero, string $escola, array $itens): bool
    {
        $config = new Configuracao();
        $destinatario = $config->get('email_secretaria');

        if (!$destinatario) {
            error_log('[Notificacao] E-mail da secretaria não configurado.');
            return false;
        }

        $host = (string) getenv('MAIL_HOST');

        // Evita travar o fluxo quando o SMTP ainda não foi configurado
        if ($host === '' || str_contains($host, 'seudominio')) {
            error_log('[Notificacao] SMTP não configurado (MAIL_HOST ausente ou placeholder).');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) getenv('MAIL_USERNAME');
            $mail->Password   = (string) getenv('MAIL_PASSWORD');
            $mail->Port       = (int) (getenv('MAIL_PORT') ?: 465);
            $mail->SMTPSecure = (string) (getenv('MAIL_ENCRYPTION') ?: 'ssl');

            $mail->CharSet = 'UTF-8';
            $mail->setFrom(
                (string) getenv('MAIL_USERNAME'),
                getenv('MAIL_FROM_NAME') ?: 'Central de Pedidos Escolares'
            );
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = 'Novo pedido #' . $numero . ' — ' . $escola;

            $linhasItens = '';
            foreach ($itens as $item) {
                $linhasItens .= '<li>' . e((int) $item['quantidade']) . 'x '
                    . e($item['produto_nome']) . '</li>';
            }

            $mail->Body = '<p>Olá! A escola <strong>' . e($escola) . '</strong> registrou o pedido '
                . '<strong>#' . (int) $numero . '</strong> pelo sistema.</p>'
                . '<p><strong>Produtos:</strong></p><ul>' . $linhasItens . '</ul>'
                . '<p>Acesse o painel administrativo para processar o pedido.</p>';

            return $mail->send();
        } catch (PHPMailerException $e) {
            error_log('[Notificacao] Falha no e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public function montarMensagemWhatsApp(string $escola, int $numero, array $itens): string
    {
        $linhas = [];
        foreach ($itens as $item) {
            $linhas[] = '- ' . (int) $item['quantidade'] . 'x ' . $item['produto_nome'];
        }

        $mensagem = "Olá! A escola {$escola} registrou o pedido #{$numero} pelo sistema.";
        $mensagem .= "\n\nProdutos:\n" . implode("\n", $linhas);
        $mensagem .= "\n\nAguardamos a confirmação/processamento.";

        return $mensagem;
    }

    public function linkWhatsApp(string $mensagem): string
    {
        $config = new Configuracao();
        $numero = $config->get('whatsapp_secretaria')
            ?: (string) getenv('WHATSAPP_FALLBACK');

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensagem);
    }
}