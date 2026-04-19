<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Constants\Auth\AuthConstant;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $authorRoleId     = Role::where('name', AuthConstant::AUTHOR)->value('id');
        $normalUserRoleId = Role::where('name', AuthConstant::NORMAL_USER)->value('id');

        User::factory(15)->create(['role_id' => $authorRoleId]);
        User::factory(30)->create(['role_id' => $normalUserRoleId]);
    }
}
