import { AppContent } from '@/components/shell/app-content';
import { AppShell } from '@/components/shell/app-shell';
import { AppSidebar } from '@/components/shell/app-sidebar';
import { AppSidebarHeader } from '@/components/shell/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
