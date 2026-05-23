<?php

namespace App\Providers;

use App\Application\Chat\Contracts\ChatGatewayContract;
use App\Application\Chat\Contracts\ConversationRepositoryContract;
use App\Application\Chat\Contracts\IntentPromptBuilderContract;
use App\Application\Chat\Services\WorkspaceIntentPromptBuilder;
use App\Application\Dialogflow\Contracts\IntentDetectorContract;
use App\Infrastructure\AI\Dialogflow\DialogflowIntentDetector;
use App\Infrastructure\AI\LmStudio\LmStudioGateway;
use App\Infrastructure\Persistence\EloquentConversationRepository;
use Illuminate\Support\ServiceProvider;

class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChatGatewayContract::class, LmStudioGateway::class);
        $this->app->bind(ConversationRepositoryContract::class, EloquentConversationRepository::class);
        $this->app->bind(IntentDetectorContract::class, DialogflowIntentDetector::class);
        $this->app->bind(IntentPromptBuilderContract::class, WorkspaceIntentPromptBuilder::class);
    }

    public function boot(): void
    {
        //
    }
}
