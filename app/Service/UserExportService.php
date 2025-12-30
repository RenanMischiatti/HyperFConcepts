<?php

namespace App\Service;

use App\Model\User;
use Faker\Factory as FakerFactory;

class UserExportService
{
    protected bool $useFake = true;
    protected int $fakeMultiplier = 1000;
    protected ?int $fakeTotal = null;
    protected int $chunkSize = 5000;

    /**
     * Define se deve usar dados falsos e o multiplicador
     */
    public function setUseFake(bool $useFake, int $multiplier = 1000): self
    {
        $this->useFake = $useFake;
        $this->fakeMultiplier = $multiplier;
        return $this;
    }

    /**
     * Define o total exato de registros fake
     */
    public function setFakeTotal(int $total): self
    {
        $this->fakeTotal = $total;
        return $this;
    }

    /**
     * Exporta usuários para CSV
     *
     * @param callable|null $onProgress Callback fn($exported, $total)
     */
    public function exportCsv(?callable $onProgress = null): string
    {
        $fileName = $this->generateFileName();
        $filePath = $this->prepareFilePath($fileName);

        $file = fopen($filePath, 'w');
        $this->writeHeader($file);

        $exported = 0;
        $total = $this->getTotalCount();

        if ($this->useFake) {
            // Fake data
            foreach ($this->getFakeDataChunks() as $row) {
                fputcsv($file, $row);
                $exported++;
                if ($onProgress && $exported % $this->chunkSize === 0) {
                    $onProgress($exported, $total);
                }
            }
        } else {
            // Real data
            User::query()->orderBy('id')->chunk($this->chunkSize, function ($users) use ($file, $onProgress, &$exported, $total) {
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->created_at,
                    ]);
                    $exported++;
                }

                if ($onProgress) {
                    $onProgress($exported, $total);
                }
            });
        }

        fclose($file);

        return "file-download?file=$fileName";
    }

    /**
     * Retorna o caminho completo do arquivo e cria diretório se necessário
     */
    protected function prepareFilePath(string $fileName): string
    {
        $path = BASE_PATH . '/public/exports';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path . '/' . $fileName;
    }

    /**
     * Gera nome do arquivo CSV
     */
    protected function generateFileName(): string
    {
        return 'users_' . date('Ymd_His') . '.csv';
    }

    /**
     * Escreve o cabeçalho do CSV
     */
    protected function writeHeader($file): void
    {
        fputcsv($file, ['ID', 'Nome', 'Email', 'Criado em']);
    }

    /**
     * Retorna o total de registros (real ou fake)
     */
    protected function getTotalCount(): int
    {
        if ($this->useFake) {
            if ($this->fakeTotal !== null) {
                return $this->fakeTotal;
            }
            return User::count() * $this->fakeMultiplier;
        }
        return User::count();
    }

    /**
     * Generator de dados fake
     */
    protected function getFakeDataChunks(): \Generator
    {
        $faker = FakerFactory::create();
        $total = $this->fakeTotal ?? (User::count() * $this->fakeMultiplier);

        for ($i = 0; $i < $total; $i++) {
            yield [
                $i + 1,
                $faker->name(),
                $faker->safeEmail(), // não usar unique() para grandes volumes
                $faker->dateTimeThisDecade()->format('Y-m-d H:i:s'),
            ];
        }
    }
}
