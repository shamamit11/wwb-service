<?php

namespace App\AI\Tools;

use App\Modules\ContentTopics\Repositories\ContentTopicRepository;
use App\Modules\Posts\Repositories\PostRepository;

class CheckDuplicateTopicTool
{
    public function __construct(
        private readonly ContentTopicRepository $topics,
        private readonly PostRepository $posts,
    ) {}

    /**
     * @return array{is_duplicate: bool, matches: list<string>}
     */
    public function check(string $title, int $categoryId, ?string $primaryKeyword = null, ?string $slug = null): array
    {
        $matches = [];

        if ($this->topics->existsDuplicate($title, $categoryId, $primaryKeyword)) {
            $matches[] = 'content_topic';
        }

        if ($this->posts->existsPotentialDuplicate($title, $slug)) {
            $matches[] = 'post';
        }

        return [
            'is_duplicate' => $matches !== [],
            'matches' => $matches,
        ];
    }
}
