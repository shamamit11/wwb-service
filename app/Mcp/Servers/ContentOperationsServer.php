<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\BlogDraftPrompt;
use App\Mcp\Prompts\ContentBriefPrompt;
use App\Mcp\Prompts\DraftRewritePrompt;
use App\Mcp\Prompts\MetadataSuggestionPrompt;
use App\Mcp\Prompts\SeoReviewPrompt;
use App\Mcp\Prompts\TitleExcerptRefinementPrompt;
use App\Mcp\Prompts\TopicDiscoveryPrompt;
use App\Mcp\Resources\ApprovedTopicsResource;
use App\Mcp\Resources\KnowledgeBaseEntriesResource;
use App\Mcp\Resources\RecentAiJobsResource;
use App\Mcp\Resources\SuggestedTopicsResource;
use App\Mcp\Tools\CreateTopicSuggestionTool;
use App\Mcp\Tools\GenerateBlogDraftTool;
use App\Mcp\Tools\GenerateContentBriefTool;
use App\Mcp\Tools\GetAiJobStatusTool;
use App\Mcp\Tools\ListContentTopicsTool;
use App\Mcp\Tools\RefinePostTitleExcerptTool;
use App\Mcp\Tools\RewritePostDraftTool;
use App\Mcp\Tools\SearchKnowledgeBaseTool;
use App\Mcp\Tools\SuggestPostMetadataTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('WideWebBlog Content Operations')]
#[Version('1.0.0')]
#[Instructions(
    'Use this server for safe editorial content workflows only. It can read knowledge-base context, list and create topic suggestions, generate content briefs, queue blog drafts, queue draft rewrites, queue metadata suggestion runs, queue title/excerpt refinement runs, and inspect AI job status. It never publishes posts and should follow the same approval rules as the admin API.'
)]
class ContentOperationsServer extends Server
{
    protected array $tools = [
        SearchKnowledgeBaseTool::class,
        ListContentTopicsTool::class,
        CreateTopicSuggestionTool::class,
        GenerateContentBriefTool::class,
        GenerateBlogDraftTool::class,
        RewritePostDraftTool::class,
        SuggestPostMetadataTool::class,
        RefinePostTitleExcerptTool::class,
        GetAiJobStatusTool::class,
    ];

    protected array $resources = [
        KnowledgeBaseEntriesResource::class,
        SuggestedTopicsResource::class,
        ApprovedTopicsResource::class,
        RecentAiJobsResource::class,
    ];

    protected array $prompts = [
        TopicDiscoveryPrompt::class,
        ContentBriefPrompt::class,
        BlogDraftPrompt::class,
        DraftRewritePrompt::class,
        MetadataSuggestionPrompt::class,
        TitleExcerptRefinementPrompt::class,
        SeoReviewPrompt::class,
    ];
}
