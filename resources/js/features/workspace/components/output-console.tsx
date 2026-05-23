import { CheckCircle2, XCircle } from 'lucide-react';

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

type OutputConsoleProps = {
    output: string;
    outputReport?: OutputReport | null;
    label?: string;
};

function formatCellValue(value: string): string {
    if (value === 'null' || value === null) {
        return '—';
    }

    return String(value);
}

export function OutputConsole({
    output,
    outputReport,
    label = 'Output',
}: OutputConsoleProps) {
    if (outputReport && outputReport.rows.length > 0) {
        return (
            <div className="space-y-2">
                <div className="flex items-center gap-2 text-sm font-semibold text-[var(--color-primary,#8C3D3D)]">
                    <span className="flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-[var(--color-primary,#8C3D3D)] text-xs font-bold">
                        i
                    </span>
                    <span>{label}</span>
                </div>

                <div className="overflow-x-auto rounded-md border border-[var(--color-border)] bg-[var(--color-editor-shell,#F8F4F4)] px-4 py-3 font-mono text-xs leading-relaxed text-[var(--color-text-main,#2d2d2d)]">
                    <p className="mb-2 text-sm font-semibold text-[var(--color-text-main,#2d2d2d)]">
                        {outputReport.summary}
                    </p>

                    <table className="w-full min-w-[640px] border-collapse text-xs">
                        <thead>
                            <tr className="border-b border-[var(--color-border)]">
                                <th className="py-1.5 pr-3 text-left font-semibold">
                                    Entrada
                                </th>
                                <th className="py-1.5 pr-3 text-left font-semibold">
                                    Esperado
                                </th>
                                <th className="py-1.5 pr-3 text-left font-semibold">
                                    Conseguido
                                </th>
                                <th className="py-1.5 text-center font-semibold">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {outputReport.rows.map((row) => (
                                <tr
                                    key={row.case}
                                    className="border-b border-[var(--color-border)] last:border-b-0"
                                >
                                    <td className="py-1.5 pr-3 whitespace-pre-wrap">
                                        {formatCellValue(row.input)}
                                    </td>
                                    <td className="py-1.5 pr-3 whitespace-pre-wrap">
                                        {formatCellValue(row.expected)}
                                    </td>
                                    <td
                                        className={`py-1.5 pr-3 whitespace-pre-wrap ${row.passed ? '' : 'font-semibold'}`}
                                    >
                                        {formatCellValue(row.received)}
                                    </td>
                                    <td className="py-1.5 text-center">
                                        {row.passed ? (
                                            <>
                                                <CheckCircle2
                                                    className="mx-auto h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                                    aria-hidden="true"
                                                />
                                                <span className="sr-only">
                                                    Passed
                                                </span>
                                            </>
                                        ) : (
                                            <>
                                                <XCircle
                                                    className="mx-auto h-4 w-4 text-rose-600 dark:text-rose-400"
                                                    aria-hidden="true"
                                                />
                                                <span className="sr-only">
                                                    Failed
                                                </span>
                                            </>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {outputReport.rawOutput && (
                    <div className="rounded-md border border-[var(--color-border)] bg-[var(--color-editor-shell,#F8F4F4)] px-4 py-3 font-mono text-xs leading-relaxed text-[var(--color-text-main,#2d2d2d)]">
                        <p className="mb-1 text-sm font-semibold">Error</p>
                        <pre className="whitespace-pre-wrap">
                            {outputReport.rawOutput}
                        </pre>
                    </div>
                )}
            </div>
        );
    }

    if (outputReport && outputReport.rawOutput) {
        return (
            <div className="space-y-2">
                <div className="flex items-center gap-2 text-sm font-semibold text-[var(--color-primary,#8C3D3D)]">
                    <span className="flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-[var(--color-primary,#8C3D3D)] text-xs font-bold">
                        i
                    </span>
                    <span>{label}</span>
                </div>

                <div className="rounded-md border border-[var(--color-border)] bg-[var(--color-editor-shell,#F8F4F4)] px-4 py-3 font-mono text-xs leading-relaxed text-[var(--color-text-main,#2d2d2d)]">
                    <p className="mb-1 text-sm font-semibold">
                        Error de ejecución
                    </p>
                    <pre className="whitespace-pre-wrap">
                        {outputReport.rawOutput}
                    </pre>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-2">
            <div className="flex items-center gap-2 text-sm font-semibold text-[var(--color-primary,#8C3D3D)]">
                <span className="flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-[var(--color-primary,#8C3D3D)] text-xs font-bold">
                    i
                </span>
                <span>{label}</span>
            </div>

            <div className="min-h-[60px] overflow-y-auto rounded-md border border-[var(--color-border)] bg-[var(--color-editor-shell,#F8F4F4)] px-4 py-3 font-mono text-xs leading-relaxed whitespace-pre-wrap text-[var(--color-text-main,#2d2d2d)]">
                {output}
            </div>
        </div>
    );
}
