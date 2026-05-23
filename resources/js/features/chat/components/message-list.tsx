import { AlertCircleIcon } from 'lucide-react';
import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import type { ChatItem } from '@/features/chat/hooks/use-chat';

type MessageListProps = {
    items: ChatItem[];
    isLoading: boolean;
};

export function MessageList({ items, isLoading }: MessageListProps) {
    if (items.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center px-4 py-8 text-center text-[var(--color-text-main)]">
                <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-primary)] text-3xl text-white">
                    🤖
                </div>
                <h2 className="mb-2 text-[1.1rem] font-semibold">
                    ¡Hola! Soy tu tutor de programacion
                </h2>
                <p className="max-w-[220px] text-[0.8rem] text-[var(--color-text-muted)]">
                    Escribe tu pregunta abajo y te ayudare a aprender.
                </p>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-2">
            {items.map((item) => (
                <article
                    key={item.id}
                    className={`max-w-[85%] animate-[msgFadeIn_0.3s_ease] rounded-[14px] px-4 py-3 text-sm leading-[1.55] [overflow-wrap:anywhere] text-white ${
                        item.role === 'assistant'
                            ? 'self-start rounded-bl-[4px] bg-[var(--color-primary)]'
                            : 'self-end rounded-br-[4px] bg-[var(--color-secondary)]'
                    }`}
                >
                    <Markdown
                        remarkPlugins={[remarkGfm]}
                        skipHtml
                        components={{
                            p: ({ children }) => (
                                <p className="mb-2 last:mb-0">{children}</p>
                            ),
                            ul: ({ children }) => (
                                <ul className="mb-2 list-disc pl-5">
                                    {children}
                                </ul>
                            ),
                            ol: ({ children }) => (
                                <ol className="mb-2 list-decimal pl-5">
                                    {children}
                                </ol>
                            ),
                            li: ({ children }) => (
                                <li className="mb-1">{children}</li>
                            ),
                            code: ({ children }) => (
                                <code className="rounded bg-black/20 px-1 py-0.5 font-mono text-[0.8em]">
                                    {children}
                                </code>
                            ),
                            pre: ({ children }) => (
                                <pre className="mb-2 overflow-x-auto">
                                    {children}
                                </pre>
                            ),
                            a: ({ href, children }) => (
                                <a
                                    href={href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="underline underline-offset-2"
                                >
                                    {children}
                                </a>
                            ),
                        }}
                    >
                        {item.content}
                    </Markdown>
                </article>
            ))}

            {isLoading && (
                <article className="flex max-w-[85%] animate-[msgFadeIn_0.3s_ease] items-center gap-[0.3rem] self-start rounded-[14px] rounded-bl-[4px] bg-[var(--color-primary)] px-5 py-3 text-sm leading-[1.55] [overflow-wrap:anywhere] text-white">
                    <span className="h-[0.45rem] w-[0.45rem] animate-[typingBounce_1s_infinite_ease-in-out] rounded-full bg-current opacity-70" />
                    <span className="h-[0.45rem] w-[0.45rem] animate-[typingBounce_1s_infinite_ease-in-out] rounded-full bg-current opacity-70 [animation-delay:0.2s]" />
                    <span className="h-[0.45rem] w-[0.45rem] animate-[typingBounce_1s_infinite_ease-in-out] rounded-full bg-current opacity-70 [animation-delay:0.4s]" />
                </article>
            )}
        </div>
    );
}

export function ErrorMessage({ message }: { message: string }) {
    return (
        <div className="flex max-w-[85%] animate-[msgFadeIn_0.3s_ease] items-start gap-2 self-start rounded-[14px] rounded-bl-[4px] border border-[var(--color-error-border)] bg-[var(--color-error)] px-4 py-3 text-sm leading-[1.55] [overflow-wrap:anywhere] text-white">
            <AlertCircleIcon className="size-3.5 flex-shrink-0" />
            <span>{message}</span>
        </div>
    );
}
