<?php

namespace App\Http\Controllers\Web;

use App\Application\Workspace\Services\ExerciseAttemptRecorder;
use Illuminate\Http\RedirectResponse;
use App\Application\Workspace\Services\LearningSidebarBuilder;
use App\Application\Workspace\Services\WorkspaceFunctionRunner;
use App\Application\Workspace\Services\WorkspacePayloadBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceRunCodeRequest;
use App\Infrastructure\AI\Judge0\Judge0Gateway;
use App\Jobs\WarmLlmCacheJob;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;


class WorkspaceController extends Controller
{
    public function __construct(
        private Judge0Gateway $judge0Gateway,
        private WorkspacePayloadBuilder $payloadBuilder,
        private WorkspaceFunctionRunner $functionRunner,
        private ExerciseAttemptRecorder $attemptRecorder,
        private LearningSidebarBuilder $sidebarBuilder,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return $this->renderWorkspace($request, $this->sidebarBuilder->firstActiveExercise());
    }

    public function showExercise(Request $request, Exercise $exercise): Response
    {
        abort_unless($exercise->is_active, 404);
        WarmLlmCacheJob::dispatch($exercise->id);
        return $this->renderWorkspace($request, $exercise);
    }

    /**
     * Run code and return output.
     */
    public function runCode(WorkspaceRunCodeRequest $request): RedirectResponse
    {
        set_time_limit((int) config('services.workspace.execution_timeout', 60));

        $validated = $request->validated();
        $exercise = Exercise::query()
            ->where('is_active', true)
            ->findOrFail((int) $validated['exercise_id']);
        $code = (string) $validated['code'];
        $languageId = (int) $validated['language_id'];
        $judge0Code = $this->functionRunner->codeForJudge($exercise, $code);

        try {
            $payload = $this->judge0Gateway->run(
                code: $judge0Code,
                languageId: $languageId,
            );
            $payload = $this->functionRunner->withFunctionExerciseResults($exercise, $payload);

            $this->attemptRecorder->record($request->user(), $exercise, $code, $languageId, $payload);

            return redirect()->route('workspace.exercises.show', ['exercise' => $exercise]);
        } catch (Throwable $exception) {
            report($exception);

            $this->attemptRecorder->record($request->user(), $exercise, $code, $languageId, [
                'stderr' => 'No se pudo conectar con el servicio de ejecucion de codigo.',
                'status' => ['id' => null, 'description' => 'failed'],
            ]);
            \Illuminate\Support\Facades\Log::info('Judge0 response:', $response->json());
            return redirect()->route('workspace.exercises.show', ['exercise' => $exercise]);
        }
    }

    private function renderWorkspace(Request $request, ?Exercise $exercise): Response
    {
        return Inertia::render('workspace/index', $this->payloadBuilder->build($request, $exercise));
    }
}
