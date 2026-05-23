import { useCallback, useRef, useState } from 'react';
import type { ChangeEvent, FormEvent, KeyboardEvent } from 'react';

type PromptInputProps = {
    onSubmit: (prompt: string) => Promise<void>;
    disabled: boolean;
    placeholder?: string;
};

export function PromptInput({
    onSubmit,
    disabled,
    placeholder = 'Escribe aqui...',
}: PromptInputProps) {
    const [prompt, setPrompt] = useState('');
    const [processing, setProcessing] = useState(false);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    const adjustHeight = useCallback(() => {
        const textarea = textareaRef.current;

        if (textarea) {
            textarea.style.height = 'auto';
            const newHeight = Math.min(
                Math.max(textarea.scrollHeight, 22),
                100,
            );
            textarea.style.height = `${newHeight}px`;
        }
    }, []);

    const handleChange = (e: ChangeEvent<HTMLTextAreaElement>) => {
        setPrompt(e.target.value);
        adjustHeight();
    };

    const handleKeyDown = (e: KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();

            if (prompt.trim().length > 0 && !isDisabled) {
                const form = e.currentTarget.closest('form');

                if (form) {
                    form.dispatchEvent(
                        new Event('submit', {
                            bubbles: true,
                            cancelable: true,
                        }),
                    );
                }
            }
        }
    };

    const submit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const normalized = prompt.trim();

        if (normalized.length === 0) {
            return;
        }

        setProcessing(true);

        try {
            await onSubmit(normalized);
            setPrompt('');

            if (textareaRef.current) {
                textareaRef.current.style.height = 'auto';
            }
        } finally {
            setProcessing(false);
        }
    };

    const isDisabled = disabled || processing || prompt.trim().length === 0;

    return (
        <form
            onSubmit={submit}
            className="flex items-center gap-2 rounded-[8px] border border-[var(--color-input-border)] bg-[var(--color-input-bg)] px-2 py-[0.35rem] focus-within:border-[var(--color-secondary)] focus-within:shadow-[0_0_0_3px_rgba(158,131,131,0.15)]"
        >
            <textarea
                ref={textareaRef}
                value={prompt}
                onChange={handleChange}
                onKeyDown={handleKeyDown}
                placeholder={placeholder}
                rows={2}
                disabled={disabled}
                className="max-h-[100px] min-h-[22px] flex-1 resize-none self-center overflow-hidden border-0 bg-transparent px-1 py-2 font-sans text-sm leading-[1.45] text-[var(--color-text-main)] outline-none placeholder:text-[var(--color-text-muted)] placeholder:opacity-100"
                spellCheck={false}
            />
            <button
                type="submit"
                disabled={isDisabled}
                className="flex size-9 shrink-0 items-center justify-center self-center rounded-full border-0 bg-[var(--color-primary)] text-white transition-[opacity,transform] duration-200 enabled:hover:opacity-85 enabled:active:scale-95 disabled:cursor-not-allowed disabled:opacity-35"
                aria-label="Enviar mensaje"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
        </form>
    );
}
