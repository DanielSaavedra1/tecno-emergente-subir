<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ChatMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatSession::query()
            ->whereIn('conversation_id', array_keys($this->messagesByConversation()))
            ->each(function (ChatSession $session): void {
                foreach ($this->messagesByConversation()[$session->conversation_id] as $message) {
                    ChatMessage::query()->updateOrCreate(
                        [
                            'chat_session_id' => $session->id,
                            'role' => $message['role'],
                            'content' => $message['content'],
                        ],
                        [
                            'tokens_in' => null,
                            'tokens_out' => null,
                            'created_at' => $message['created_at'],
                        ],
                    );
                }
            });
    }

    /**
     * @return array<string, array<int, array{role: string, content: string, created_at: Carbon}>>
     */
    private function messagesByConversation(): array
    {
        return [
            'demo-python-conditionals' => [
                [
                    'role' => 'user',
                    'content' => 'No entiendo cuando usar if y else.',
                    'created_at' => Carbon::parse('2026-01-01 09:00:00'),
                ],
                [
                    'role' => 'assistant',
                    'content' => 'Usa if para evaluar una condicion y else para cubrir el caso contrario.',
                    'created_at' => Carbon::parse('2026-01-01 09:01:00'),
                ],
            ],
            'demo-python-loops' => [
                [
                    'role' => 'user',
                    'content' => 'Como acumulo valores dentro de un for?',
                    'created_at' => Carbon::parse('2026-01-01 09:10:00'),
                ],
                [
                    'role' => 'assistant',
                    'content' => 'Crea una variable acumuladora antes del bucle y actualizala en cada vuelta.',
                    'created_at' => Carbon::parse('2026-01-01 09:11:00'),
                ],
            ],
            'demo-python-functions' => [
                [
                    'role' => 'user',
                    'content' => 'Mi funcion imprime, pero el ejercicio espera un return.',
                    'created_at' => Carbon::parse('2026-01-01 09:20:00'),
                ],
                [
                    'role' => 'assistant',
                    'content' => 'Reemplaza print por return para devolver el resultado a la plataforma.',
                    'created_at' => Carbon::parse('2026-01-01 09:21:00'),
                ],
            ],
        ];
    }
}
