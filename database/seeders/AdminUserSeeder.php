<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $password    = Hash::make(env('ADMIN_PASSWORD', 'password'));

        $emails = array_filter(array_map(
            'trim',
            explode(',', env('ADMIN_EMAILS', ''))
        ));

        foreach ($emails as $index => $email) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => 'Admin ' . ($index + 1),
                    'role_id'           => $adminRoleId,
                    'password'          => $password,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
