import { useCallback, useEffect, useRef, useState } from 'react';
import type { PointerEvent, ReactNode } from 'react';

const MIN_CHAT_PANEL_WIDTH_PX = 280;
const MIN_MAIN_PANEL_WIDTH_PX = 360;
const DEFAULT_CHAT_PANEL_WIDTH_PX = 420;

type CollapsibleChatPanelProps = {
    children: ReactNode;
    collapsed: boolean;
};

export function CollapsibleChatPanel({
    children,
    collapsed,
}: CollapsibleChatPanelProps) {
    const asideRef = useRef<HTMLElement>(null);
    const isResizingRef = useRef(false);
    const panelWidthRef = useRef<number>(DEFAULT_CHAT_PANEL_WIDTH_PX);
    const [isResizing, setIsResizing] = useState(false);
    const [panelWidth, setPanelWidth] = useState<number>(
        DEFAULT_CHAT_PANEL_WIDTH_PX,
    );

    const getClampedPanelWidth = useCallback((nextWidth: number): number => {
        const parent = asideRef.current?.parentElement;

        if (!parent) {
            return Math.max(nextWidth, MIN_CHAT_PANEL_WIDTH_PX);
        }

        const parentWidth = parent.getBoundingClientRect().width;
        const maxPanelWidth = Math.max(
            MIN_CHAT_PANEL_WIDTH_PX,
            parentWidth - MIN_MAIN_PANEL_WIDTH_PX,
        );

        return Math.min(
            Math.max(nextWidth, MIN_CHAT_PANEL_WIDTH_PX),
            maxPanelWidth,
        );
    }, []);

    const handlePointerMove = useCallback(
        (event: PointerEvent | globalThis.PointerEvent): void => {
            if (!isResizingRef.current) {
                return;
            }

            const parent = asideRef.current?.parentElement;

            if (!parent) {
                return;
            }

            const bounds = parent.getBoundingClientRect();
            const widthFromRight = bounds.right - event.clientX;
            const nextWidth = getClampedPanelWidth(widthFromRight);

            panelWidthRef.current = nextWidth;

            if (asideRef.current) {
                asideRef.current.style.width = `${nextWidth}px`;
            }
        },
        [getClampedPanelWidth],
    );

    const stopResizing = useCallback((): void => {
        if (!isResizingRef.current) {
            return;
        }

        isResizingRef.current = false;
        setIsResizing(false);
        setPanelWidth(panelWidthRef.current);
        document.body.style.userSelect = '';
        document.body.style.cursor = '';
    }, []);

    const startResizing = useCallback(
        (event: PointerEvent<HTMLButtonElement>): void => {
            event.preventDefault();

            isResizingRef.current = true;
            setIsResizing(true);
            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'col-resize';
        },
        [],
    );

    useEffect(() => {
        const onPointerMove = (event: globalThis.PointerEvent): void => {
            handlePointerMove(event);
        };

        const onPointerUp = (): void => {
            stopResizing();
        };

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp);

        return () => {
            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            stopResizing();
        };
    }, [handlePointerMove, stopResizing]);

    useEffect(() => {
        if (collapsed) {
            const defaultWidth = getClampedPanelWidth(
                DEFAULT_CHAT_PANEL_WIDTH_PX,
            );
            panelWidthRef.current = defaultWidth;
            // Reset to default width when collapsing - intentional state sync
            setPanelWidth(defaultWidth);

            return;
        }

        setPanelWidth((current) => {
            const clamped = getClampedPanelWidth(current);
            panelWidthRef.current = clamped;

            return clamped;
        });
    }, [collapsed, getClampedPanelWidth]);

    return (
        <aside
            ref={asideRef}
            aria-hidden={collapsed}
            className={`relative flex h-full flex-shrink-0 flex-col overflow-hidden ${
                isResizing
                    ? ''
                    : 'transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]'
            } ${
                collapsed
                    ? 'w-0 max-w-0 min-w-0 border-l-0 opacity-0'
                    : 'border-l border-[var(--color-panel-border)] opacity-100'
            }`}
            style={collapsed ? { width: 0 } : { width: `${panelWidth}px` }}
        >
            {!collapsed && (
                <button
                    type="button"
                    aria-label="Redimensionar panel de chat"
                    onPointerDown={startResizing}
                    className="absolute top-0 -left-1 z-30 h-full w-2 cursor-col-resize"
                >
                    <span className="absolute top-1/2 left-1/2 h-16 w-1 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[var(--color-panel-border)] opacity-60 transition hover:opacity-100" />
                </button>
            )}

            <div
                className={`flex h-full flex-col overflow-hidden transition-opacity duration-200 ${
                    collapsed ? 'pointer-events-none opacity-0' : 'opacity-100'
                }`}
            >
                {children}
            </div>
        </aside>
    );
}
