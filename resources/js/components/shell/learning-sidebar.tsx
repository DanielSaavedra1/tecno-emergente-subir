import { Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';

type ExerciseStatus = 'pending' | 'done';

export type LearningSidebarExercise = {
    id: number;
    number: string;
    title: string;
    href: string;
    status: ExerciseStatus;
    isActive: boolean;
};

export type LearningSidebarLevel = {
    id: number | null;
    number: number;
    title: string;
    progress: number;
    total: number;
    exercises: LearningSidebarExercise[];
};

const progressBarWidthClasses = [
    'w-0',
    'w-1/5',
    'w-2/5',
    'w-3/5',
    'w-4/5',
    'w-full',
];

export type LearningSidebarData = {
    currentLevel: LearningSidebarLevel;
    activeExerciseId: number | null;
    previousExerciseHref: string | null;
    nextExerciseHref: string | null;
};

export type LearningSidebarProgress = {
    learning: LearningSidebarData;
    currentProgress: number;
    progressBarWidthClass: string;
    isPreviousDisabled: boolean;
    isNextDisabled: boolean;
};

const emptyLearningSidebarData: LearningSidebarData = {
    currentLevel: {
        id: null,
        number: 1,
        title: 'Sin ejercicios',
        progress: 0,
        total: 0,
        exercises: [],
    },
    activeExerciseId: null,
    previousExerciseHref: null,
    nextExerciseHref: null,
};

export function useLearningSidebarProgress(
    learning?: LearningSidebarData,
): LearningSidebarProgress {
    const resolvedLearning = learning ?? emptyLearningSidebarData;
    const totalExercises = resolvedLearning.currentLevel.total;
    const currentProgress = resolvedLearning.currentLevel.progress;
    const progressBarIndex =
        totalExercises === 0
            ? 0
            : Math.min(
                  progressBarWidthClasses.length - 1,
                  Math.round((currentProgress / totalExercises) * 5),
              );

    return {
        learning: resolvedLearning,
        currentProgress,
        progressBarWidthClass: progressBarWidthClasses[progressBarIndex],
        isPreviousDisabled: resolvedLearning.previousExerciseHref === null,
        isNextDisabled: resolvedLearning.nextExerciseHref === null,
    };
}

export function LearningSidebar({
    progress,
}: {
    progress: LearningSidebarProgress;
}) {
    const currentLevel = progress.learning.currentLevel;

    return (
        <SidebarGroup className="px-2 py-0 group-data-[collapsible=icon]:hidden">
            <SidebarGroupLabel>Aprendizaje</SidebarGroupLabel>
            <SidebarGroupContent>
                <div className="flex flex-col gap-4 rounded-xl bg-background p-2">
                    <div className="flex flex-col gap-2">
                        <div
                            className="h-2 overflow-hidden rounded-full bg-muted"
                            aria-label={`Progreso: ${progress.currentProgress} de ${currentLevel.total}`}
                            role="progressbar"
                            aria-valuenow={progress.currentProgress}
                            aria-valuemin={0}
                            aria-valuemax={currentLevel.total}
                        >
                            <div
                                className={cn(
                                    'h-full rounded-full bg-rose-900 transition-all',
                                    progress.progressBarWidthClass,
                                )}
                            />
                        </div>

                        <div className="flex flex-col gap-1 px-1">
                            <h2 className="text-xs font-semibold tracking-wide text-foreground uppercase">
                                Tema {currentLevel.number}: {currentLevel.title}
                            </h2>
                            <p className="text-xs font-medium text-muted-foreground uppercase">
                                Progreso: {progress.currentProgress} de{' '}
                                {currentLevel.total}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-col gap-3">
                        <section
                            className="flex flex-col gap-2"
                            aria-labelledby={`tema-${currentLevel.id ?? 'empty'}-heading`}
                        >
                            <span
                                id={`tema-${currentLevel.id ?? 'empty'}-heading`}
                                className="sr-only"
                            >
                                Tema {currentLevel.number}: {currentLevel.title}
                            </span>

                            <div className="flex flex-col gap-2">
                                {currentLevel.exercises.length === 0 && (
                                    <p className="rounded-lg bg-muted px-3 py-2 text-xs text-muted-foreground">
                                        Todavía no hay ejercicios cargados.
                                    </p>
                                )}

                                {currentLevel.exercises.map((exercise) => (
                                    <Link
                                        key={exercise.id}
                                        href={exercise.href}
                                        aria-current={
                                            exercise.isActive
                                                ? 'step'
                                                : undefined
                                        }
                                        className={cn(
                                            'relative flex w-full items-center gap-2 rounded-lg bg-muted px-3 py-2 text-left text-sm transition-colors hover:bg-muted/80 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                                            exercise.isActive &&
                                                'text-rose-900 before:absolute before:top-2 before:bottom-2 before:left-0 before:w-1 before:rounded-full before:bg-rose-900',
                                        )}
                                    >
                                        <span className="flex size-5 shrink-0 items-center justify-center rounded-full bg-background text-xs font-semibold text-muted-foreground">
                                            {exercise.status === 'done' ? (
                                                <span className="flex size-5 items-center justify-center rounded-full bg-emerald-600 text-white">
                                                    <Check className="size-3" />
                                                </span>
                                            ) : (
                                                exercise.number
                                            )}
                                        </span>
                                        <span className="min-w-0 flex-1">
                                            <span className="block text-xs font-semibold text-muted-foreground">
                                                {exercise.number}
                                            </span>
                                            <span className="block leading-snug">
                                                {exercise.title}
                                            </span>
                                        </span>
                                    </Link>
                                ))}
                            </div>
                        </section>
                    </div>
                </div>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}

export function LearningSidebarPager({
    progress,
}: {
    progress: LearningSidebarProgress;
}) {
    const previousHref = progress.learning.previousExerciseHref;
    const nextHref = progress.learning.nextExerciseHref;

    return (
        <div className="grid grid-cols-2 gap-2 group-data-[collapsible=icon]:hidden">
            {previousHref === null ? (
                <Button type="button" variant="outline" size="sm" disabled>
                    Anterior
                </Button>
            ) : (
                <Button asChild variant="outline" size="sm">
                    <Link href={previousHref}>
                        Anterior
                    </Link>
                </Button>
            )}

            {nextHref === null ? (
                <Button type="button" size="sm" disabled>
                    Siguiente
                </Button>
            ) : (
                <Button asChild size="sm">
                    <Link href={nextHref}>
                        Siguiente
                    </Link>
                </Button>
            )}
        </div>
    );
}
