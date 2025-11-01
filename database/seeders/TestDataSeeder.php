<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Message;
use App\Models\Document;
use App\Models\Application;
use App\Models\Conversation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin2x',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);

        Application::factory(10)->create();
        Conversation::factory(10)->create();
        foreach (Conversation::all() as $conversation) {
            $roles = ['user', 'assistant'];
            foreach (range(1, 10) as $index) {
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $roles[$index % 2] == 'user' ? $conversation->user_id : null,
                ]);
            }
            Document::factory(10)->create([
                'conversation_id' => $conversation->id,
            ]);
        }
    }
}
