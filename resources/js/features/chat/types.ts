export type ChatMessagePayload = {
    prompt: string;
    conversation_id?: string | null;
    exercise_id: number; 
    source_code?: string | null;
    output?: string | null;
};

export type ChatMessageApiResponse = {
    status: 'success';
    reply: string;
    conversation_id: string;
    memory_exchanges: number;
};