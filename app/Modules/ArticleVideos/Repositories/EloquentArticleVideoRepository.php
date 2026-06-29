<?php

namespace App\Modules\ArticleVideos\Repositories;

use App\Models\ArticleVideo;
use App\Modules\ArticleVideos\Data\CreateArticleVideoData;
use App\Modules\ArticleVideos\Data\UpdateArticleVideoData;
use App\Modules\ArticleVideos\Enums\ArticleVideoStatus;
use Illuminate\Database\Eloquent\Collection;

class EloquentArticleVideoRepository implements ArticleVideoRepository
{
    public function create(CreateArticleVideoData $data): ArticleVideo
    {
        $video = ArticleVideo::query()->create([
            'post_id' => $data->postId,
            'article_video_recommendation_id' => $data->articleVideoRecommendationId,
            'status' => $data->status,
            'render_mode' => $data->renderMode,
            'hook' => $data->hook,
            'voiceover_text' => $data->voiceoverText,
            'script_json' => $data->scriptJson,
            'captions_json' => $data->captionsJson,
            'voiceover_path' => $data->voiceoverPath,
            'captions_path' => $data->captionsPath,
            'thumbnail_path' => $data->thumbnailPath,
            'video_path' => $data->videoPath,
            'duration_seconds' => $data->durationSeconds,
            'approved_at' => $data->approvedAt,
            'rendered_at' => $data->renderedAt,
            'error_message' => $data->errorMessage,
        ]);

        return $this->refreshWithRelations($video);
    }

    public function update(ArticleVideo $video, UpdateArticleVideoData $data): ArticleVideo
    {
        $video->update([
            'article_video_recommendation_id' => $data->articleVideoRecommendationId,
            'status' => $data->status,
            'render_mode' => $data->renderMode,
            'hook' => $data->hook,
            'voiceover_text' => $data->voiceoverText,
            'script_json' => $data->scriptJson,
            'captions_json' => $data->captionsJson,
            'voiceover_path' => $data->voiceoverPath,
            'captions_path' => $data->captionsPath,
            'thumbnail_path' => $data->thumbnailPath,
            'video_path' => $data->videoPath,
            'duration_seconds' => $data->durationSeconds,
            'approved_at' => $data->approvedAt,
            'rendered_at' => $data->renderedAt,
            'error_message' => $data->errorMessage,
        ]);

        return $this->refreshWithRelations($video);
    }

    public function findById(int $id): ?ArticleVideo
    {
        return ArticleVideo::query()
            ->with($this->relations())
            ->find($id);
    }

    public function findActiveDraftForPostId(int $postId): ?ArticleVideo
    {
        return ArticleVideo::query()
            ->with($this->relations())
            ->where('post_id', $postId)
            ->whereIn('status', [
                ArticleVideoStatus::Draft->value,
                ArticleVideoStatus::Approved->value,
                ArticleVideoStatus::Rendering->value,
            ])
            ->latest('id')
            ->first();
    }

    public function hasRenderedVideoForPostId(int $postId): bool
    {
        return ArticleVideo::query()
            ->where('post_id', $postId)
            ->where('status', ArticleVideoStatus::Rendered->value)
            ->exists();
    }

    /**
     * @return Collection<int, ArticleVideo>
     */
    public function findByPostId(int $postId): Collection
    {
        return ArticleVideo::query()
            ->with($this->relations())
            ->where('post_id', $postId)
            ->orderByDesc('id')
            ->get();
    }

    private function refreshWithRelations(ArticleVideo $video): ArticleVideo
    {
        return $video->refresh()->load($this->relations());
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['post', 'recommendation', 'memoryEntries'];
    }
}
