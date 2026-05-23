import { PanelLeftCloseIcon, PanelLeftOpenIcon } from 'lucide-react';
import { createElement } from 'react';
import type { ReactNode } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { useSidebar } from '@/components/ui/sidebar';

type ExerciseExample = {
    arguments?: unknown[];
    expected?: unknown;
};

type ProblemExercise = {
    description?: string | null;
    functionName?: string | null;
    starterCode?: string | null;
    inputDescription?: string | null;
    outputDescription?: string | null;
    examples?: ExerciseExample[];
    considerations?: string[];
};

type ProblemSectionProps = {
    title: string;
    description: string;
    exercise?: ProblemExercise | null;
    status: 'pending' | 'done';
    chatCollapsed: boolean;
    onToggleChat: () => void;
};

function formatValue(value: unknown): string {
    if (value === null || value === undefined) {
        return '—';
    }

    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'number' || typeof value === 'boolean') {
        return String(value);
    }

    return JSON.stringify(value) ?? String(value);
}

function formatArguments(args: unknown[] | undefined): string {
    if (!Array.isArray(args) || args.length === 0) {
        return '—';
    }

    return args.map((argument) => formatValue(argument)).join(', ');
}

function SectionHeading({ children }: { children: ReactNode }) {
    return (
        <h2 className="text-base font-black tracking-[0.05em] text-[var(--color-text-main)] uppercase sm:text-lg">
            {children}
        </h2>
    );
}

function CodeBlock({ code }: { code: string }) {
    return createElement(
        'pre',
        {
            className:
                'max-h-72 overflow-auto border-2 border-[#1f1f1f] bg-[#f1eeee] px-4 py-3 font-mono text-[13px] leading-7 text-[#252525] shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] dark:border-zinc-100/70 dark:bg-zinc-950 dark:text-zinc-100',
        },
        code.replaceAll('\\n', '\n').trimEnd(),
    );
}

function StructuredExercisePrompt({ exercise }: { exercise: ProblemExercise }) {
    const functionBullets = [
        exercise.functionName ? (
            <>
                Se llame <strong>{exercise.functionName}</strong>.
            </>
        ) : null,
        exercise.inputDescription,
        exercise.outputDescription,
    ].filter(Boolean);
    const considerations = exercise.considerations?.filter(Boolean) ?? [];
    const examples = exercise.examples ?? [];

    return (
        <div className="space-y-8 text-[var(--color-text-main)]">
            {exercise.description && (
                <MarkdownPrompt description={exercise.description} />
            )}

            {exercise.starterCode && (
                <section className="space-y-3">
                    <p className="text-base font-black text-[var(--color-text-main)] sm:text-lg">
                        Complete el siguiente programa en PYTHON:
                    </p>

                    <CodeBlock code={exercise.starterCode} />
                </section>
            )}

            {functionBullets.length > 0 && (
                <section className="space-y-4">
                    <SectionHeading>
                        ESCRIBA UNA FUNCIÓN EN PYTHON QUE:
                    </SectionHeading>

                    <ul className="space-y-3 pl-9 text-base leading-8 text-[var(--color-text-muted,#555)] marker:text-[1.05rem] marker:text-[var(--color-text-main)] sm:text-lg">
                        {functionBullets.map((bullet, index) => (
                            <li key={index}>{bullet}</li>
                        ))}
                    </ul>
                </section>
            )}

            {considerations.length > 0 && (
                <section className="space-y-4">
                    <SectionHeading>CONSIDERACIONES:</SectionHeading>

                    <ul className="space-y-3 pl-9 text-base leading-8 text-[var(--color-text-muted,#555)] marker:text-[1.05rem] marker:text-[var(--color-text-main)] sm:text-lg">
                        {considerations.map((consideration) => (
                            <li key={consideration}>{consideration}</li>
                        ))}
                    </ul>
                </section>
            )}

            {examples.length > 0 && (
                <section className="space-y-4">
                    <p className="text-base font-black text-[var(--color-text-main)] sm:text-lg">
                        Por ejemplo:
                    </p>

                    <div className="w-full overflow-x-auto">
                        <table className="min-w-[16rem] border-collapse text-sm text-[var(--color-text-main)]">
                            <thead>
                                <tr className="bg-[#efefef] dark:bg-zinc-900">
                                    <th className="border border-[#b8b8b8] px-3 py-2 text-left font-black">
                                        Entrada
                                    </th>
                                    <th className="border border-[#b8b8b8] px-3 py-2 text-left font-black">
                                        Resultado
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {examples.map((example, index) => (
                                    <tr
                                        className="bg-[#f7f7f7] dark:bg-zinc-950"
                                        key={`${formatArguments(example.arguments)}-${index}`}
                                    >
                                        <td className="border border-[#b8b8b8] px-3 py-2 font-mono text-xs">
                                            {formatArguments(example.arguments)}
                                        </td>
                                        <td className="border border-[#b8b8b8] px-3 py-2 font-mono text-xs">
                                            {formatValue(example.expected)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            )}
        </div>
    );
}

function MarkdownPrompt({ description }: { description: string }) {
    return (
        <div className="prose prose-sm max-w-none text-[var(--color-text-muted,#555)]">
            <ReactMarkdown remarkPlugins={[remarkGfm]}>
                {description}
            </ReactMarkdown>
        </div>
    );
}

function hasStructuredExercisePrompt(
    exercise: ProblemExercise | null | undefined,
): exercise is ProblemExercise {
    return Boolean(
        exercise &&
        (exercise.functionName ||
            exercise.starterCode ||
            exercise.inputDescription ||
            exercise.outputDescription ||
            exercise.considerations?.length ||
            exercise.examples?.length),
    );
}

export function ProblemSection({
    title,
    description,
    exercise,
    status,
    chatCollapsed,
    onToggleChat,
}: ProblemSectionProps) {
    const { state, toggleSidebar } = useSidebar();

    return (
        <section className="space-y-7">
            <div className="flex items-center gap-2">
                <button
                    type="button"
                    onClick={toggleSidebar}
                    aria-label={
                        state === 'expanded'
                            ? 'Contraer sidebar'
                            : 'Expandir sidebar'
                    }
                    className="flex h-9 w-9 items-center justify-center rounded-md bg-[var(--color-chat-header)] text-[var(--color-text-muted)] shadow-sm transition-colors hover:bg-[var(--color-secondary)] hover:text-white"
                >
                    {state === 'expanded' ? (
                        <PanelLeftCloseIcon className="size-3.5" />
                    ) : (
                        <PanelLeftOpenIcon className="size-3.5" />
                    )}
                </button>

                <span
                    className={`inline-block rounded-full border px-3 py-1 text-[11px] font-bold tracking-wide text-white transition-colors ${
                        status === 'done'
                            ? 'border-[var(--color-success-border,#247a45)] bg-[var(--color-success,#2e9f5a)]'
                            : 'border-[var(--color-error-border,#a93b3b)] bg-[var(--color-error,#c74e4e)]'
                    }`}
                >
                    {status === 'done' ? 'Terminado' : 'No terminado'}
                </span>

                <button
                    type="button"
                    onClick={onToggleChat}
                    aria-label={
                        chatCollapsed ? 'Expandir chat' : 'Contraer chat'
                    }
                    className="ml-auto flex h-9 w-9 items-center justify-center rounded-md bg-[var(--color-chat-header)] text-[var(--color-text-muted)] shadow-sm transition-colors hover:bg-[var(--color-secondary)] hover:text-white"
                >
                    {chatCollapsed ? (
                        <PanelLeftOpenIcon className="size-3.5 rotate-180" />
                    ) : (
                        <PanelLeftCloseIcon className="size-3.5 rotate-180" />
                    )}
                </button>
            </div>

            <article className="max-w-5xl space-y-8">
                <h1 className="text-center text-2xl font-black tracking-[0.12em] text-[var(--color-text-main)] uppercase sm:text-3xl">
                    {title}
                </h1>

                {hasStructuredExercisePrompt(exercise) ? (
                    <StructuredExercisePrompt exercise={exercise} />
                ) : (
                    <MarkdownPrompt description={description} />
                )}
            </article>
        </section>
    );
}
