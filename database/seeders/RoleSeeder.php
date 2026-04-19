<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Constants\Auth\AuthConstant;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => AuthConstant::ADMIN,       'description' => 'Full system access'],
            ['name' => AuthConstant::SYSTEM_USER, 'description' => 'Internal system account'],
            ['name' => AuthConstant::AUTHOR,      'description' => 'Novel translator / author'],
            ['name' => AuthConstant::NORMAL_USER, 'description' => 'Regular reader'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
