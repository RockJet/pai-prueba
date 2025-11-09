<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        $users->each(function ($user) {
            Subscription::create([
                'user_id' => $user->id,
                'plan_name' => 'basic',
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => null,
                'stripe_subscription_id' => null,
            ]);
        });
    }
}
