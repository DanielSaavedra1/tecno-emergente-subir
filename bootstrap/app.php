<?php

use App\Exceptions\DialogflowUnavailableException;
use App\Exceptions\LmStudioUnavailableException;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'La solicitud no paso la validacion.',
                    ],
                    'errors' => $exception->errors(),
                ], 422);
            }

            return null;
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    code: 'RATE_LIMITED',
                    message: 'Demasiadas solicitudes. Intenta nuevamente en un momento.',
                    status: 429,
                );
            }

            return null;
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    code: 'FORBIDDEN',
                    message: 'No autorizado para realizar esta accion.',
                    status: 403,
                );
            }

            return null;
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof DialogflowUnavailableException) {
                return ApiResponse::error(
                    code: 'DIALOGFLOW_UNAVAILABLE',
                    message: 'Servicio de clasificacion temporalmente no disponible',
                    status: 503,
                );
            }

            if ($exception instanceof LmStudioUnavailableException) {
                return ApiResponse::error(
                    code: 'SERVICE_UNAVAILABLE',
                    message: 'Servicio temporalmente no disponible',
                    status: 503,
                );
            }

            return null;
        });
    })->create();
