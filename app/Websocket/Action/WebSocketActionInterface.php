<?php

namespace App\WebSocket\Action;

use Swoole\WebSocket\Server as SwooleServer;

interface WebSocketActionInterface
{
    public function handle(array $data, string $clientId, SwooleServer $server): void;
}
