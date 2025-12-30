<?php

namespace App\WebSocket;

use App\WebSocket\Action\{
    WebSocketActionInterface,
    CreateUsersAction,
    ExportUsersAction
};
use Hyperf\Support\make;
use Swoole\WebSocket\Server as SwooleServer;
use Swoole\WebSocket\Frame;

use function Hyperf\Support\make;

class WebSocketHandler
{
    /**
     * Armazena os clients pelo clientId
     * @var array<string,int>
     */
    protected static array $clients = [];

    /**
     * Mapeamento de ações
     * @var array<string,class-string<WebSocketActionInterface>>
     */
    protected static array $actions = [
        'create_users' => CreateUsersAction::class,
        'export_users' => ExportUsersAction::class,
    ];

    public function onOpen(SwooleServer $server, $request): void
    {
        echo "Cliente conectado: fd={$request->fd}\n";
    }

    public function onMessage(SwooleServer $server, Frame $frame): void
    {
        $data = json_decode($frame->data, true);
        if (!isset($data['action']) || !isset($data['clientId'])) {
            return;
        }

        $clientId = $data['clientId'];

        // Registra o fd do clientId se ainda não registrado
        if (!isset(self::$clients[$clientId])) {
            self::$clients[$clientId] = $frame->fd;
        }

        // Executa a ação
        if (isset(self::$actions[$data['action']])) {
            
            /** @var WebSocketActionInterface $action */
            $action = make(self::$actions[$data['action']]);
            $action->handle($data, $clientId, $server);
            return;
        }

        echo "Ação desconhecida: {$data['action']}\n";
    }

    /**
     * Envia dados para um client específico
     */
    public static function pushToClient(SwooleServer $server, string $clientId, array $data): void
    {
        if (!isset(self::$clients[$clientId])) return;
        $server->push(self::$clients[$clientId], json_encode($data));
    }

    public function onClose(SwooleServer $server, int $fd): void
    {
        $clientId = array_search($fd, self::$clients);
        if ($clientId !== false) {
            unset(self::$clients[$clientId]);
        }
        echo "Cliente desconectado: fd={$fd}\n";
    }
}
