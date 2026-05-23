import { AlertCircleIcon } from 'lucide-react';
import { MessageList } from '@/features/chat/components/message-list';
import { PromptInput } from '@/features/chat/components/prompt-input';
import { useChat } from '@/features/chat/hooks/use-chat';

type ChatContext = {
    exerciseId: number;
    sourceCode?: string | null;
    output?: string | null;
};

type ChatPanelProps = {
    modelName?: string | null;
    botName?: string;
    subtitle?: string;
    context?: ChatContext;
};

export function ChatPanel({
    modelName,
    botName = 'ChatLedge',
    subtitle = 'Chatbot de ayuda',
    context,
}: ChatPanelProps) {
    const { processing, chatError, messages, validationErrors, submitMessage } =
        useChat(context);

    return (
        <div className="flex h-full max-h-full min-h-0 flex-col overflow-hidden bg-[var(--color-chat-root)]">
            {/* Header */}
            <div className="flex flex-shrink-0 items-center gap-3 border-b border-[var(--color-panel-border)] bg-[var(--color-chat-header)] p-[0.85rem]">
                <div className="flex flex-col">
                    <h1 className="m-0 text-[0.95rem] leading-tight font-bold text-[var(--color-text-main)]">
                        {botName}
                    </h1>
                    <span className="text-[0.7rem] text-[var(--color-secondary)]">
                        {subtitle}
                    </span>
                </div>
            </div>

            {/* Messages */}
            <div className="chat-container flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto scroll-smooth p-5">
                {chatError && (
                    <div className="flex max-w-[85%] animate-[msgFadeIn_0.3s_ease] items-start gap-2 self-start rounded-[14px] rounded-bl-[4px] border border-[var(--color-error-border)] bg-[var(--color-error)] px-4 py-3 text-sm leading-[1.55] [overflow-wrap:anywhere] text-white">
                        <AlertCircleIcon className="size-3.5 flex-shrink-0" />
                        <span>{chatError.message}</span>
                    </div>
                )}

                <MessageList items={messages} isLoading={processing} />
            </div>

            {/* Input */}
            <div className="flex-shrink-0 bg-[var(--color-chat-root)] p-3">
                <PromptInput
                    onSubmit={submitMessage}
                    disabled={processing}
                    placeholder="Escribe aqui..."
                />
                {validationErrors.prompt && (
                    <p className="mt-2 text-xs text-[var(--color-error)]">
                        {validationErrors.prompt}
                    </p>
                )}
            </div>

            {/* Footer */}
            <div className="flex-shrink-0 p-2 text-center text-[0.7rem] text-[var(--color-text-muted)]">
                Powered by {modelName ?? 'gemma 3'}
            </div>
        </div>
    );
}