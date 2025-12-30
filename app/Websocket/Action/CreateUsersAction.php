<?php 

namespace App\WebSocket\Action;

use App\Service\UserFactoryService;
use Swoole\WebSocket\Server as SwooleServer;

class CreateUsersAction implements WebSocketActionInterface
{
    protected UserFactoryService $service;

    public function __construct(UserFactoryService $service)
    {
        $this->service = $service;
    }

    public function handle(array $data, string $clientId, SwooleServer $server): void
    {
        $count = $data['count'] ?? 100;
        $this->service->create($count, $clientId, $server);
    }
}
