<?php

namespace App\WebSocket;

use App\Service\UserFactoryService;
use Hyperf\Coroutine\Coroutine;
use Swoole\WebSocket\Server as SwooleServer;
use Swoole\WebSocket\Frame;

use function Hyperf\Support\make;

class WebSocketHandler
{
    // Armazena os clientes pelo clientId enviado pelo front
    protected static array $clients = [];

    public function onOpen(SwooleServer $server, $request)
    {
        echo "Cliente conectado: fd={$request->fd}\n";
    }

    public function onMessage(SwooleServer $server, Frame $frame)
    {
        $data = json_decode($frame->data, true);

        if (!isset($data['action'])) return;

        switch ($data['action']) {
            case 'register':
                $clientId = $data['clientId']; // pega exatamente do front
                self::$clients[$clientId] = $frame->fd;

                // Usa pushToClient estático
                self::pushToClient($server, $clientId, [
                    'type' => 'registered',
                    'clientId' => $clientId, // retorna o mesmo que veio do front
                ]);
                break;

            case 'create_users':
                $service = make(UserFactoryService::class);
                $service->create($data['count'], $data['clientId'], $server);

                break;
        }
    }

    public static function pushToClient(SwooleServer $server, string $clientId, array $data)
    {
        if (!isset(self::$clients[$clientId])) {
            return;
        }

        $fd = self::$clients[$clientId];
        $server->push($fd, json_encode($data));
    }

    public function onClose(SwooleServer $server, $fd)
    {
        echo "Cliente desconectado: fd={$fd}\n";

        // Remove o cliente da lista
        $clientId = array_search($fd, self::$clients);
        if ($clientId !== false) {
            unset(self::$clients[$clientId]);
        }
    }
}
