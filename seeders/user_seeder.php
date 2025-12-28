<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;
use App\Model\User;
use App\Model\UserInfo;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int|null $count Número de usuários a criar. Se null, usa 100.
     */
    public function run(?int $count = null): void
    {
        $count = $count ?? 100;
        $faker = Faker::create('pt_BR');

        // Desabilita FK temporariamente
        Db::statement('SET FOREIGN_KEY_CHECKS=0');

        Db::table('users_info')->truncate();
        Db::table('users')->truncate();

        Db::statement('SET FOREIGN_KEY_CHECKS=1');

        for ($i = 0; $i < $count; $i++) {

            $user = User::create([
                'name'       => $faker->name,
                'email'      => $faker->unique()->safeEmail,
                'password'   => password_hash('password', PASSWORD_BCRYPT)
            ]);

            UserInfo::create([
                'user_id'    => $user->id,
                'phone'      => $faker->phoneNumber,
                'address'    => $faker->address,
                'birthdate'  => $faker->date('Y-m-d')
            ]);
        }
    }
}
