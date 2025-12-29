<?php

namespace App\Service;

use App\Model\User;
use App\WebSocket\WebSocketHandler;
use Faker\Factory as Faker;
use Hyperf\DbConnection\Db;
use Hyperf\Coroutine\Coroutine;
use Swoole\WebSocket\Server;

class UserFactoryService
{
    protected \Faker\Generator $faker;
    protected string $now;

    public function __construct()
    {
        $this->faker = Faker::create('pt_BR');
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * Cria usuários com progresso em tempo real via WebSocket
     */
    public function create(int $count = 100, ?string $clientId = null, ?Server $server = null): void
    {
        $this->truncateTables();

        $inserted = 0;
        $batchSize = 150;

        while ($inserted < $count) {
            $currentBatch = min($batchSize, $count - $inserted);
            $usersData = $this->generateUserBatch($currentBatch, $inserted);

            // Insert dos users
            User::insert($usersData);
            $firstId = (int) Db::getPdo()->lastInsertId();

            // Coroutine para inserir users_info e enviar progresso para cada sub-batch
            Coroutine::create(function() use ($usersData, $firstId, $clientId, $server, &$inserted, $count) {
                $infosData = [];
                foreach ($usersData as $i => $user) {
                    $infosData[] = [
                        'user_id' => $firstId + $i,
                        'phone' => $this->faker->phoneNumber,
                        'address' => $this->faker->address,
                        'birthdate' => $this->faker->date('Y-m-d'),
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ];

                    // envia progresso **para cada usuário**
                    if ($clientId && $server) {
                        WebSocketHandler::pushToClient($server, $clientId, [
                            'type' => 'progress',
                            'inserted' => $inserted + $i + 1,
                            'total' => $count,
                        ]);
                    }
                }
                Db::table('users_info')->insert($infosData);
            });

            $inserted += $currentBatch;
        }

        // Finalizado: envia mensagem com todos os usuários
        if ($clientId && $server) {
            WebSocketHandler::pushToClient($server, $clientId, [
                'type' => 'finished',
                'total' => $count,
                'users' => User::all(),
            ]);
        }
    }

    /**
     * Limpa tabelas antes de inserir
     */
    protected function truncateTables(): void
    {
        Db::statement('SET FOREIGN_KEY_CHECKS=0');
        Db::table('users_info')->truncate();
        Db::table('users')->truncate();
        Db::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Gera batch de usuários
     */
    protected function generateUserBatch(int $batchSize, int $offset = 0): array
    {
        $batch = [];
        for ($i = 0; $i < $batchSize; $i++) {
            // email gerado sem usar faker->unique() para evitar conflitos em coroutines
            $email = 'user' . ($offset + $i + 1) . '@example.com';
            $batch[] = [
                'name' => $this->faker->name,
                'email' => $email,
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];
        }
        return $batch;
    }
}
