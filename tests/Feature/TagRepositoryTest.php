<?php

namespace Tests\Feature;

use App\Modules\Tags\Data\CreateTagData;
use App\Modules\Tags\Data\UpdateTagData;
use App\Modules\Tags\Repositories\TagRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_and_post_tags_tables_match_the_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasColumns('tags', [
            'id',
            'ulid',
            'name',
            'slug',
            'description',
            'is_active',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('post_tags'));
        $this->assertTrue(Schema::hasColumns('post_tags', [
            'post_id',
            'tag_id',
            'created_at',
        ]));
    }

    public function test_repository_supports_tag_crud_and_lookups(): void
    {
        $repository = app(TagRepository::class);

        $tag = $repository->create(new CreateTagData(
            name: 'Memory',
            slug: 'memory',
            description: 'Memory-related content.',
            isActive: true,
        ));

        $updated = $repository->update($tag, new UpdateTagData(
            name: 'Architecture',
            slug: 'architecture',
            description: 'Architecture content.',
            isActive: false,
        ));

        $this->assertSame($updated->id, $repository->findById($updated->id)?->id);
        $this->assertSame($updated->id, $repository->findBySlug('architecture')?->id);
        $this->assertTrue($repository->existsBySlug('architecture'));
        $this->assertFalse($repository->existsBySlug('missing-tag'));

        $activeTag = $repository->create(new CreateTagData(
            name: 'Agents',
            slug: 'agents',
            description: null,
            isActive: true,
        ));

        $this->assertSame([$activeTag->id, $updated->id], $repository->getAllOrdered()->modelKeys());
        $this->assertSame([$activeTag->id], $repository->getActiveOrdered()->modelKeys());

        $repository->delete($updated);

        $this->assertNull($repository->findById($updated->id));
        $this->assertSame(26, strlen($tag->ulid));
    }

    public function test_repository_supports_service_side_post_tag_assignments(): void
    {
        $repository = app(TagRepository::class);

        $firstTag = $repository->create(new CreateTagData(
            name: 'Memory',
            slug: 'memory',
            description: null,
            isActive: true,
        ));

        $secondTag = $repository->create(new CreateTagData(
            name: 'Architecture',
            slug: 'architecture',
            description: null,
            isActive: true,
        ));

        $thirdTag = $repository->create(new CreateTagData(
            name: 'Agents',
            slug: 'agents',
            description: null,
            isActive: false,
        ));

        $repository->syncPostTags(42, [$firstTag->id, $secondTag->id, $secondTag->id]);

        $this->assertSame([$firstTag->id, $secondTag->id], $repository->getTagIdsForPost(42));

        $repository->syncPostTags(42, [$thirdTag->id]);

        $this->assertSame([$thirdTag->id], $repository->getTagIdsForPost(42));
    }
}
