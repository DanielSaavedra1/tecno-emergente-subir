import type { ReactNode } from 'react';
import { AppShell } from '@/components/shell/app-shell';
import { AppSidebar } from '@/components/shell/app-sidebar';

type Props = {
    children: ReactNode;
};

export default function WorkspaceLayout({ children }: Props) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <div className="flex h-svh min-h-0 w-full flex-col overflow-hidden [contain:layout_paint_size]">
                <div className="flex min-h-0 flex-1 flex-col overflow-hidden">
                    {children}
                </div>
            </div>
        </AppShell>
    );
}
