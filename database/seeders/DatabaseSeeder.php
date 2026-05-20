<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(QuestionBankSeeder::class);

        Admin::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@squadlimpul.local')],
            [
                'password_hash' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => env('ADMIN_ROLE', 'owner'),
            ],
        );

        Community::query()->updateOrCreate(
            ['slug' => 'squad-limpul'],
            [
                'name' => 'Squad Limpul',
                'branding' => [
                    'managed_by' => 'Managed Squad Limpul',
                ],
                'is_active' => true,
            ],
        );

        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ],
        );
    }
}
