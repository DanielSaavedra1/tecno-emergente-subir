import { usePage } from '@inertiajs/react';
import { BotMessageSquare } from 'lucide-react';
import type { LearningSidebarData } from '@/components/shell/learning-sidebar';
import {
    LearningSidebar,
    LearningSidebarPager,
    useLearningSidebarProgress,
} from '@/components/shell/learning-sidebar';
import { NavMain } from '@/components/shell/nav-main';
import { NavUser } from '@/components/shell/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { index as workspace } from '@/routes/workspace';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const page = usePage();
    const learningSidebarProgress = useLearningSidebarProgress(
        page.props.learning as LearningSidebarData | undefined,
    );
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const workspaceRoute = workspace();
    const isWorkspaceCurrent = isCurrentOrParentUrl(workspaceRoute);
    const workspaceItem: NavItem = {
        title: 'Workspace',
        href: workspaceRoute,
        icon: BotMessageSquare,
    };

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarContent>
                {!isWorkspaceCurrent && <NavMain items={[workspaceItem]} />}
                {isWorkspaceCurrent && (
                    <LearningSidebar progress={learningSidebarProgress} />
                )}
            </SidebarContent>

            <SidebarFooter>
                {isWorkspaceCurrent && (
                    <LearningSidebarPager progress={learningSidebarProgress} />
                )}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
