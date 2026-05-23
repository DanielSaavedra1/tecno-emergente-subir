import { useCallback, useMemo, useRef } from 'react';
import { highlightPython } from './syntax-highlighter';

/**
 * Props para el componente CodeEditor
 */
type CodeEditorProps = {
    value: string;
    onChange: (code: string) => void;
    fileName?: string;
    placeholder?: string;
};

/**
 * Mapeo de apertura -> cierre para auto-pairs
 */
const AUTO_PAIRS: Record<string, string> = {
    '(': ')',
    '[': ']',
    '{': '}',
    '"': '"',
    "'": "'",
    '`': '`',
};

/**
 * Caracteres que abren un pair (para verificar si hay que cerrar automáticamente)
 */
const OPENING_CHARS = new Set(Object.keys(AUTO_PAIRS));
const CLOSING_CHARS = new Set(Object.values(AUTO_PAIRS));

const MIN_EDITOR_LINES = 14;
const MAX_EDITOR_LINES = 22;
const EDITOR_LINE_HEIGHT_PX = 24; // leading-[1.5rem]
const EDITOR_VERTICAL_PADDING_PX = 32; // p-4

export function CodeEditor({
    value,
    onChange,
    fileName = 'solucion.py',
    placeholder = '# Escribe tu codigo aqui',
}: CodeEditorProps) {
    // Referencias a elementos del DOM
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const highlightRef = useRef<HTMLDivElement>(null);
    const lineNumbersRef = useRef<HTMLPreElement>(null);

    const highlighted = useMemo(() => highlightPython(value), [value]);

    // Memoized: número de líneas
    const lineCount = useMemo(
        () => Math.max(value.split('\n').length, 1),
        [value],
    );

    // Memoized: texto de números de línea
    const lineNumbersText = useMemo(
        () => Array.from({ length: lineCount }, (_, i) => i + 1).join('\n'),
        [lineCount],
    );

    const visibleLineCount = useMemo(
        () => Math.min(Math.max(lineCount, MIN_EDITOR_LINES), MAX_EDITOR_LINES),
        [lineCount],
    );

    const editorHeight = useMemo(
        () =>
            visibleLineCount * EDITOR_LINE_HEIGHT_PX +
            EDITOR_VERTICAL_PADDING_PX,
        [visibleLineCount],
    );

    // Callback: sincronizar scroll entre textarea y highlight
    const handleScroll = useCallback(() => {
        const textarea = textareaRef.current;
        const highlight = highlightRef.current;
        const lineNumbers = lineNumbersRef.current;

        if (textarea && highlight) {
            highlight.scrollTop = textarea.scrollTop;
            highlight.scrollLeft = textarea.scrollLeft;
        }

        if (textarea && lineNumbers) {
            lineNumbers.scrollTop = textarea.scrollTop;
        }
    }, []);

    /**
     * Inserta texto en la posición actual del cursor
     */
    const insertTextAtCursor = useCallback(
        (newText: string, cursorOffset = 0) => {
            const textarea = textareaRef.current;

            if (!textarea) {
                return;
            }

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const before = value.substring(0, start);
            const after = value.substring(end);

            onChange(before + newText + after);

            // Restaurar posición del cursor después del update
            requestAnimationFrame(() => {
                textarea.selectionStart = start + cursorOffset;
                textarea.selectionEnd = start + cursorOffset;
                textarea.focus();
            });
        },
        [value, onChange],
    );

    /**
     * Maneja combinaciones de teclas especiales
     */
    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
            const textarea = textareaRef.current;

            if (!textarea) {
                return;
            }

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const key = e.key;
            const hasSelection = start !== end;

            // Tab -> 4 espacios (o dedent con Shift)
            if (key === 'Tab') {
                e.preventDefault();

                if (e.shiftKey) {
                    // Dedent: remover hasta 4 espacios del inicio de línea
                    const lineStart = value.lastIndexOf('\n', start - 1) + 1;
                    const currentLine = value.substring(lineStart, start);
                    const leadingSpaces =
                        currentLine.match(/^\s{1,4}/)?.[0] || '';

                    if (leadingSpaces) {
                        onChange(
                            value.substring(0, lineStart) +
                                currentLine.substring(leadingSpaces.length),
                        );
                        requestAnimationFrame(() => {
                            textarea.selectionStart =
                                start - leadingSpaces.length;
                            textarea.selectionEnd =
                                start - leadingSpaces.length;
                        });
                    }
                } else {
                    insertTextAtCursor('    ', 4);
                }

                return;
            }

            // Auto-pairs: insertar apertura + cierre
            if (
                OPENING_CHARS.has(key) &&
                !e.ctrlKey &&
                !e.metaKey &&
                !e.altKey
            ) {
                e.preventDefault();

                if (hasSelection) {
                    // Wrapping: rodear selección con el pair
                    const selected = value.substring(start, end);
                    insertTextAtCursor(key + selected + AUTO_PAIRS[key], 1);
                } else {
                    // Solo_insertar_pair
                    insertTextAtCursor(key + AUTO_PAIRS[key], 1);
                }

                return;
            }

            // Backspace: eliminar pair completo si están juntos
            if (key === 'Backspace') {
                const prevChar = value[start - 1];
                const nextChar = value[start];

                if (
                    prevChar &&
                    CLOSING_CHARS.has(nextChar) &&
                    AUTO_PAIRS[prevChar] === nextChar
                ) {
                    e.preventDefault();
                    onChange(
                        value.substring(0, start - 1) +
                            value.substring(start + 1),
                    );
                    requestAnimationFrame(() => {
                        textarea.selectionStart = start - 1;
                        textarea.selectionEnd = start - 1;
                    });
                }

                return;
            }

            // Enter: auto-indent después de dos puntos
            if (key === 'Enter') {
                const lineStart = value.lastIndexOf('\n', start - 1) + 1;
                const currentLine = value.substring(lineStart, start);

                // Si la línea termina con ":", agregar indent adicional
                if (currentLine.trimEnd().endsWith(':')) {
                    e.preventDefault();
                    const indent = (currentLine.match(/^\s*/) || [''])[0];
                    insertTextAtCursor(
                        '\n' + indent + '    ',
                        indent.length + 4,
                    );

                    return;
                }
            }
        },
        [value, onChange, insertTextAtCursor],
    );

    /**
     * Callback: actualizar valor cuando el usuario escribe
     */
    const handleChange = useCallback(
        (e: React.ChangeEvent<HTMLTextAreaElement>) => {
            onChange(e.target.value);
        },
        [onChange],
    );

    return (
        <div className="flex flex-col">
            {/* Barra de pestaña */}
            <div className="flex items-center gap-2 rounded-t-lg bg-secondary px-3 py-2 text-xs font-medium text-secondary-foreground">
                <span className="font-mono text-base font-bold">&lt;/&gt;</span>
                <span>{fileName}</span>
            </div>

            {/* Shell del editor */}
            <div
                className="flex min-h-[320px] overflow-hidden rounded-b-lg border border-[var(--color-border)] bg-[var(--color-editor-shell,#F8F4F4)]"
                style={{
                    height: `${editorHeight}px`,
                    maxHeight: `${MAX_EDITOR_LINES * EDITOR_LINE_HEIGHT_PX + EDITOR_VERTICAL_PADDING_PX}px`,
                }}
            >
                {/* Números de línea */}
                <pre
                    ref={lineNumbersRef}
                    className="max-w-[3.5rem] min-w-[2.5rem] flex-shrink-0 overflow-hidden border-r border-[var(--color-border)] bg-[var(--color-editor-gutter,#F0ECEC)] py-4 pr-[0.6rem] pl-2 text-right font-mono text-xs leading-[1.5rem] text-muted-foreground select-none"
                    aria-hidden="true"
                >
                    {lineNumbersText}
                </pre>

                {/* Área del editor */}
                <div className="relative flex-1 overflow-hidden">
                    {/* Capa de highlight -visible detrás del textarea */}
                    <div
                        ref={highlightRef}
                        className="pointer-events-none absolute inset-0 overflow-auto p-4 font-mono text-xs leading-[1.5rem] break-words whitespace-pre-wrap"
                        aria-hidden="true"
                        // Safe: el highlight solo contiene caracteres escapados del código fuente
                        dangerouslySetInnerHTML={{ __html: highlighted || ' ' }}
                    />

                    {/* Textarea - texto transparente para ver highlight */}
                    <textarea
                        ref={textareaRef}
                        value={value}
                        onChange={handleChange}
                        onKeyDown={handleKeyDown}
                        onScroll={handleScroll}
                        rows={MIN_EDITOR_LINES}
                        placeholder={placeholder}
                        aria-label="Python code editor"
                        spellCheck={false}
                        className="absolute inset-0 h-full w-full resize-none overflow-y-auto border-0 bg-transparent p-4 font-mono text-xs leading-[1.5rem] break-words whitespace-pre-wrap text-transparent caret-[#8C3D3D] outline-none placeholder:text-[var(--color-text-subtle,#aaa)]"
                    />
                </div>
            </div>
        </div>
    );
}
