<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Utils\Codec\Json;

class ExportController extends AbstractController
{
    public function export(ServerRequestInterface $request, ResponseInterface $response)
    {
        $file = $request->getQueryParams()['file'] ?? null;

        if (!$file) {
            return $response->json(['error' => 'Arquivo não informado'], 400);
        }

        $filePath = BASE_PATH . '/public/exports/' . basename($file);

        if (!file_exists($filePath)) {
            return $response->json(['error' => 'Arquivo não encontrado'], 404);
        }

        return $response->download($filePath);
    }




}
