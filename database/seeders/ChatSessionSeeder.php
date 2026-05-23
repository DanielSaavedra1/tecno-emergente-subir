<?php

namespace Database\Seeders;

use App\Models\ChatSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ChatSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->demoSessions() as $session) {
            ChatSession::query()->updateOrCreate(
                ['conversation_id' => $session['conversation_id']],
                $session,
            );
        }
    }

    /**
     * @return array<int, array{conversation_id: string, user_id: null, title: string, metadata: array{demo: bool}, last_message_at: Carbon}>
     */
    private function demoSessions(): array
    {
        return [
            [
                'conversation_id' => 'demo-python-conditionals',
                'user_id' => null,
                'title' => 'Ayuda con condicionales',
                'metadata' => ['demo' => true],
                'last_message_at' => Carbon::parse('2026-01-01 09:00:00'),
            ],
            [
                'conversation_id' => 'demo-python-loops',
                'user_id' => null,
                'title' => 'Practica de bucles',
                'metadata' => ['demo' => true],
                'last_message_at' => Carbon::parse('2026-01-01 09:10:00'),
            ],
            [
                'conversation_id' => 'demo-python-functions',
                'user_id' => null,
                'title' => 'Funciones en Python',
                'metadata' => ['demo' => true],
                'last_message_at' => Carbon::parse('2026-01-01 09:20:00'),
            ],
        ];
    }
}
