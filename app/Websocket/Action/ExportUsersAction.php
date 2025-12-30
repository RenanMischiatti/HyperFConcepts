<?php 

namespace App\WebSocket\Action;

use App\Service\UserExportService;
use App\WebSocket\WebSocketHandler;
use Swoole\WebSocket\Server as SwooleServer;
use Hyperf\Coroutine\Coroutine;

class ExportUsersAction implements WebSocketActionInterface
{
    protected UserExportService $service;

    public function __construct(UserExportService $service)
    {
        $this->service = $service;
    }

    public function handle(array $data, string $clientId, SwooleServer $server): void
    {
        Coroutine::create(function() use ($data, $clientId, $server) {
            $fileUrl = $this->service->exportCsv(function ($exported, $total) use ($clientId, $server) {
                WebSocketHandler::pushToClient($server, $clientId, [
                    'type' => 'export_progress',
                    'exported' => $exported,
                    'total' => $total,
                ]);
            });

            WebSocketHandler::pushToClient($server, $clientId, [
                'type' => 'export_ready',
                'url' => $fileUrl,
            ]);
        });
    }
}
