import { useMemo, useState } from 'react';
import { useChatApi } from '@/features/chat/lib/use-chat-api';
import type { ChatMessagePayload } from '@/features/chat/types';

export type ChatRole = 'user' | 'assistant';

export type ChatItem = {
    id: string;
    role: ChatRole;
    content: string;
};

type ChatContext = {
    exerciseId: number;
    sourceCode?: string | null;
    output?: string | null;
};

type ServiceErrorCode =
    | 'validation'
    | 'rate_limit'
    | 'service_unavailable'
    | 'network'
    | 'server_error'
    | 'client_error'
    | 'unknown';

type ChatError = {
    code: ServiceErrorCode;
    message: string;
};

type HttpError = {
    response?: {
        status?: number;
        data?: {
            error?: {
                message?: string;
            };
        };
    };
};

export function useChat(context?: ChatContext) {
    const [conversationId, setConversationId] = useState<string | null>(null);
    const [messages, setMessages] = useState<ChatItem[]>([]);
    const [memoryExchanges, setMemoryExchanges] = useState<number>(0);
    const [chatError, setChatError] = useState<ChatError | null>(null);

    const { sendMessage, processing, validationErrors, clearErrors } =
        useChatApi();

    const status = useMemo(() => {
        if (processing) {
            return 'loading' as const;
        }

        if (chatError !== null) {
            return 'error' as const;
        }

        if (messages.length > 0) {
            return 'success' as const;
        }

        return 'idle' as const;
    }, [chatError, messages.length, processing]);

    const handleError = (error: HttpError): ChatError => {
        const statusCode = error.response?.status;

        if (!statusCode) {
            return {
                code: 'network',
                message:
                    'No se pudo conectar con el servidor. Verifica tu conexión.',
            };
        }

        if (statusCode === 422) {
            return {
                code: 'validation',
                message: 'El mensaje no cumple las validaciones requeridas.',
            };
        }

        if (statusCode === 429) {
            return {
                code: 'rate_limit',
                message:
                    'Has alcanzado el límite de peticiones. Intenta en un momento.',
            };
        }

        if (statusCode === 503) {
            return {
                code: 'service_unavailable',
                message:
                    error.response?.data?.error?.message ??
                    'LM Studio no está disponible. Asegúrate de tener el modelo cargado.',
            };
        }

        if (statusCode >= 500) {
            return {
                code: 'server_error',
                message: 'Error del servidor. Intenta más tarde.',
            };
        }

        if (statusCode >= 400) {
            return {
                code: 'client_error',
                message:
                    'Error en la solicitud. Verifica los datos e intenta de nuevo.',
            };
        }

        return {
            code: 'unknown',
            message: 'Ocurrió un error inesperado al enviar tu mensaje.',
        };
    };

    const submitMessage = async (prompt: string): Promise<void> => {
        const normalizedPrompt = prompt.trim();

        if (normalizedPrompt.length === 0) {
            return;
        }

        clearErrors();
        setChatError(null);

        const userItem: ChatItem = {
            id: crypto.randomUUID(),
            role: 'user',
            content: normalizedPrompt,
        };

        setMessages((previous) => [...previous, userItem]);

        try {
            const payload: ChatMessagePayload = {
                prompt: normalizedPrompt,
                conversation_id: conversationId,
                exercise_id: context?.exerciseId ?? 0,
                source_code: context?.sourceCode ?? null,
                output: context?.output ?? null,
            };

            const response = await sendMessage(payload);

            const assistantItem: ChatItem = {
                id: crypto.randomUUID(),
                role: 'assistant',
                content: response.reply,
            };

            setMessages((previous) => [...previous, assistantItem]);
            setConversationId(response.conversation_id);
            setMemoryExchanges(response.memory_exchanges);
        } catch (error) {
            setMessages((previous) =>
                previous.filter((item) => item.id !== userItem.id),
            );
            setChatError(handleError(error as HttpError));
        }
    };

    return {
        status,
        processing,
        chatError,
        messages,
        memoryExchanges,
        validationErrors,
        submitMessage,
    };
}