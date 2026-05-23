import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ChatPanel } from '@/features/chat/components/chat-panel';
import { CodeEditor } from '@/features/workspace/components/code-editor';
import { CollapsibleChatPanel } from '@/features/workspace/components/collapsible-chat-panel';
import { OutputConsole } from '@/features/workspace/components/output-console';
import { ProblemSection } from '@/features/workspace/components/problem-section';
import { RunButton } from '@/features/workspace/components/run-button';
import { runCode } from '@/routes/workspace';

type OutputReportRow = {
    case: number;
    label: string;
    input: string;
    expected: string;
    received: string;
    passed: boolean;
};

type OutputReport = {
    status: 'success' | 'error';
    summary: string;
    rows: OutputReportRow[];
    rawOutput?: string;
};

type CurrentExercise = {
    id: number;
    number: string;
    title: string;
    description?: string | null;
    functionName?: string | null;
    starterCode?: string | null;
    inputDescription?: string | null;
    outputDescription?: string | null;
    examples?: Array<{
        arguments?: unknown[];
        expected?: unknown;
    }>;
    considerations?: string[];
};

type WorkspacePageProps = {
    lmStudioModel: string | null;
    currentExercise?: CurrentExercise | null;
    problemTitle?: string;
    problemDescription?: string;
    problemStatus?: 'pending' | 'done';
    sourceCode?: string;
    output?: string;
    outputReport?: OutputReport | null;
    fileName?: string;
};

export default function Workspace({
    lmStudioModel,
    currentExercise = null,
    problemTitle = 'Sin ejercicios',
    problemDescription = 'Todavia no hay ejercicios cargados para resolver.',
    problemStatus = 'pending',
    sourceCode = '',
    output = '[Consola lista...]',
    outputReport = null,
    fileName = 'solucion.py',
}: WorkspacePageProps) {
    const [chatCollapsed, setChatCollapsed] = useState(false);

    const { data, setData, processing, submit } = useForm({
        exercise_id: currentExercise?.id ?? null,
        code: sourceCode,
        language_id: 71,
    });

    useEffect(() => {
        setData({
            exercise_id: currentExercise?.id ?? null,
            code: sourceCode,
            language_id: 71,
        });
    }, [currentExercise?.id, sourceCode, setData]);

    const chatContext = currentExercise
        ? {
              exerciseId: currentExercise.id,
              sourceCode: data.code,
              output: output === '[Consola lista...]' ? null : output,
          }
        : undefined;

    return (
        <>
            <Head title="Workspace" />

            <div className="flex h-full min-h-0 overflow-hidden">
                {/* Center: Problem + Code Editor + Output */}
                <main className="flex-1 overflow-y-auto bg-[var(--color-bg-page)] px-8 py-6">
                    <div className="space-y-6 pb-8">
                        <ProblemSection
                            title={problemTitle}
                            description={problemDescription}
                            exercise={currentExercise}
                            status={problemStatus}
                            chatCollapsed={chatCollapsed}
                            onToggleChat={() =>
                                setChatCollapsed((current) => !current)
                            }
                        />

                        <div className="space-y-4">
                            <CodeEditor
                                value={data.code}
                                onChange={(code) => setData('code', code)}
                                fileName={fileName}
                            />

                            <div className="flex justify-end">
                                <RunButton
                                    processing={processing}
                                    text="COMPILAR"
                                    disabled={currentExercise === null}
                                    onClick={() => submit(runCode())}
                                />
                            </div>

                            <OutputConsole
                                output={output}
                                outputReport={outputReport}
                            />
                        </div>
                    </div>
                </main>

                {/* Right: Chat Panel — collapsible */}
                <CollapsibleChatPanel collapsed={chatCollapsed}>
                    <ChatPanel
                        modelName={lmStudioModel}
                        context={chatContext}
                    />
                </CollapsibleChatPanel>
            </div>
        </>
    );
}
