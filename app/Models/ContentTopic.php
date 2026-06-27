<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'cluster',
    'primary_keyword',
    'secondary_keywords',
    'search_intent',
    'priority_score',
    'score_breakdown',
    'discovery_metadata',
    'difficulty_note',
    'source',
    'status',
    'notes',
    'approved_at',
    'rejected_at',
    'used_at',
])]
class ContentTopic extends Model
{
    public const SEARCH_INTENT_MAX_LENGTH = 255;

    public const STATUS_SUGGESTED = 'suggested';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_USED = 'used';

    public const RECOMMENDATION_AUTO_QUEUE = 'auto_queue';

    public const RECOMMENDATION_REVIEW = 'review';

    public const RECOMMENDATION_LOW_SCORE = 'low_score';

    public const RECOMMENDATION_DISCARDED = 'discarded';

    public const RECOMMENDATION_DUPLICATE = 'duplicate';

    public const RECOMMENDATION_UNSCORED = 'unscored';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_AI_SUGGESTED = 'ai_suggested';

    public const SOURCE_NEWS_SIGNAL = 'news_signal';

    public const CLUSTER_AI_TOOLS = 'ai_tools';

    public const CLUSTER_AI_FOR_BLOGGING = 'ai_for_blogging';

    public const CLUSTER_SEO = 'seo';

    public const CLUSTER_CONTENT_MARKETING = 'content_marketing';

    public const CLUSTER_PRODUCTIVITY_AUTOMATION = 'productivity_automation';

    public const CLUSTER_DEVELOPER_AI = 'developer_ai';

    public const STATUSES = [
        self::STATUS_SUGGESTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_USED,
    ];

    public const CLUSTERS = [
        self::CLUSTER_AI_TOOLS,
        self::CLUSTER_AI_FOR_BLOGGING,
        self::CLUSTER_SEO,
        self::CLUSTER_CONTENT_MARKETING,
        self::CLUSTER_PRODUCTIVITY_AUTOMATION,
        self::CLUSTER_DEVELOPER_AI,
    ];

    public const SOURCES = [
        self::SOURCE_MANUAL,
        self::SOURCE_AI_SUGGESTED,
        self::SOURCE_NEWS_SIGNAL,
    ];

    public const RECOMMENDATIONS = [
        self::RECOMMENDATION_AUTO_QUEUE,
        self::RECOMMENDATION_REVIEW,
        self::RECOMMENDATION_LOW_SCORE,
        self::RECOMMENDATION_DISCARDED,
        self::RECOMMENDATION_DUPLICATE,
        self::RECOMMENDATION_UNSCORED,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'secondary_keywords' => 'array',
            'priority_score' => 'decimal:2',
            'score_breakdown' => 'array',
            'discovery_metadata' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    /**
     * @return HasMany<AiJob, $this>
     */
    public function draftGenerationJobs(): HasMany
    {
        return $this->hasMany(AiJob::class, 'entity_id')
            ->where('entity_type', 'content_topic')
            ->where('type', AiPromptTemplate::TYPE_BLOG_WRITER);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canGenerateDraft(): bool
    {
        return $this->isApproved();
    }

    public function editorialRecommendation(): string
    {
        $metadataRecommendation = $this->discoveryMetadataValue('recommendation');

        if (is_string($metadataRecommendation) && in_array($metadataRecommendation, self::RECOMMENDATIONS, true)) {
            return $metadataRecommendation;
        }

        if ($this->isDuplicateDiscovery()) {
            return self::RECOMMENDATION_DUPLICATE;
        }

        if (! is_numeric($this->priority_score)) {
            return self::RECOMMENDATION_UNSCORED;
        }

        $score = (float) $this->priority_score;

        if ($score >= 85.0) {
            return self::RECOMMENDATION_AUTO_QUEUE;
        }

        if ($score >= 70.0) {
            return self::RECOMMENDATION_REVIEW;
        }

        if ($score >= 50.0) {
            return self::RECOMMENDATION_LOW_SCORE;
        }

        return self::RECOMMENDATION_DISCARDED;
    }

    public function isDuplicateDiscovery(): bool
    {
        return $this->discoveryMetadataValue('is_duplicate') === true;
    }

    /**
     * @return list<string>
     */
    public function duplicateMatches(): array
    {
        $matches = $this->discoveryMetadataValue('duplicate_matches');

        if (! is_array($matches)) {
            return [];
        }

        return array_values(array_filter($matches, static fn (mixed $value): bool => is_string($value) && $value !== ''));
    }

    public function hasDraftGenerationJob(): bool
    {
        $loaded = $this->getAttribute('has_draft_generation_job');

        if (is_bool($loaded)) {
            return $loaded;
        }

        if (is_numeric($loaded)) {
            return (bool) $loaded;
        }

        return $this->draftGenerationJobs()->exists();
    }

    private function discoveryMetadataValue(string $key): mixed
    {
        $metadata = $this->getAttributeValue('discovery_metadata');

        if (! is_array($metadata) || ! array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }
}
