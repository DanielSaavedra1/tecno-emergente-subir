import { useHttp } from '@inertiajs/react';
import type {
    ChatMessageApiResponse,
    ChatMessagePayload,
} from '@/features/chat/types';
import { store } from '@/routes/api/v1/chat/messages';

export function useChatApi() {
    const http = useHttp<ChatMessagePayload, ChatMessageApiResponse>({
        prompt: '',
        exercise_id: 0,
    });

    const sendMessage = async (
        payload: ChatMessagePayload,
    ): Promise<ChatMessageApiResponse> => {
        http.transform(() => payload);

        return http.submit(store());
    };

    return {
        sendMessage,
        processing: http.processing,
        validationErrors: http.errors,
        clearErrors: http.clearErrors,
    };
}