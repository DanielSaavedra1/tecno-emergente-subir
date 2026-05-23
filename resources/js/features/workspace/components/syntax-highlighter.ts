/**
 * Python Syntax Highlighter
 * Extrae y colorea tokens de Python: keywords, builtins, strings, números, comentarios
 */

// Palabras reservadas de Python
const KEYWORDS = new Set([
    'False',
    'None',
    'True',
    'and',
    'as',
    'assert',
    'async',
    'await',
    'break',
    'class',
    'continue',
    'def',
    'del',
    'elif',
    'else',
    'except',
    'finally',
    'for',
    'from',
    'global',
    'if',
    'import',
    'in',
    'is',
    'lambda',
    'nonlocal',
    'not',
    'or',
    'pass',
    'raise',
    'return',
    'try',
    'while',
    'with',
    'yield',
    'match',
    'case',
]);

// Funciones built-in de Python
const BUILTINS = new Set([
    'print',
    'len',
    'range',
    'input',
    'int',
    'float',
    'str',
    'list',
    'dict',
    'set',
    'tuple',
    'type',
    'enumerate',
    'zip',
    'map',
    'sum',
    'min',
    'max',
    'abs',
    'open',
    'round',
    'sorted',
    'any',
    'all',
]);

// Regex para tokenizar Python
const TOKEN_REGEX =
    /#.*$|"(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*'|\b\d+(?:\.\d+)?\b|\b[A-Za-z_]\w*\b/g;

/**
 * Escapa caracteres HTML para evitar XSS
 */
function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

/**
 * Convierte código Python a HTML con colores
 * @param code - Código fuente Python
 * @returns HTML con spans de colores
 */
export function highlightPython(code: string): string {
    let html = '';
    let lastIndex = 0;
    let match;

    // Reset regex state para evitar problemas de estado
    TOKEN_REGEX.lastIndex = 0;

    while ((match = TOKEN_REGEX.exec(code)) !== null) {
        const token = match[0];
        const index = match.index;

        // Agregar texto sin colorear entre tokens
        html += escapeHtml(code.slice(lastIndex, index));

        // Detectar tipo de token y aplicar color
        if (token.startsWith('#')) {
            // Comentario
            html += `<span class="tk-comment">${escapeHtml(token)}</span>`;
        } else if (token.startsWith('"') || token.startsWith("'")) {
            // String
            html += `<span class="tk-string">${escapeHtml(token)}</span>`;
        } else if (/^\d/.test(token)) {
            // Número
            html += `<span class="tk-number">${escapeHtml(token)}</span>`;
        } else if (KEYWORDS.has(token)) {
            // Keyword
            html += `<span class="tk-keyword">${escapeHtml(token)}</span>`;
        } else if (BUILTINS.has(token)) {
            // Builtin
            html += `<span class="tk-builtin">${escapeHtml(token)}</span>`;
        } else {
            // Texto normal
            html += escapeHtml(token);
        }

        lastIndex = index + token.length;
    }

    // Agregar resto del código sin colorear
    return html + escapeHtml(code.slice(lastIndex));
}

/**
 * Obtiene la lista de keywords soportados (para debugging o autocompletado)
 */
export function getKeywords(): readonly string[] {
    return [...KEYWORDS];
}

/**
 * Obtiene la lista de builtins soportados
 */
export function getBuiltins(): readonly string[] {
    return [...BUILTINS];
}
