<?php

use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        User::create([
            "name" => "Admin",
            "email" => "admin@gmail.com",
            "password" => bcrypt("password")
        ]);
    }
}
