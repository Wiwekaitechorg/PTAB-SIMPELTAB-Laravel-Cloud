<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [[
            'id'             => 1,
            'name'           => 'Master',
            'email'          => 'putualgoritma@gmail.com',
            'password'       => '$2y$10$pCetaAnyHYgcBsB631FyZ.vTymV47Jqh6IjytCTDYpjpYtjIdbfqS',
            'remember_token' => null,
            'email_verified_at' => null,
            'created_at'     => '2021-04-06 03:32:27',
            'updated_at'     => '2021-04-06 03:32:27',
            'deleted_at'     => null,
        ]];

        User::insert($users);
    }
}
