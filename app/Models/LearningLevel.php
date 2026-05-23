<?php

namespace App\Models;

use Database\Factories\LearningLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'title', 'description', 'position', 'is_active'])]
class LearningLevel extends Model
{
    /** @use HasFactory<LearningLevelFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function topics(): HasMany
    {
        return $this->hasMany(LearningTopic::class);
    }
}
