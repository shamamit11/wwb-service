<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $name) {
            Tag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @return list<string>
     */
    private function records(): array
    {
        return [
            'Laravel',
            'MCP',
            'OpenAI',
            'Claude',
            'Gemini',
            'AWS',
            'Bedrock',
            'LangChain',
            'LangGraph',
            'RAG',
            'Blogging',
            'WordPress',
            'Affiliate Marketing',
            'Technical SEO',
            'Prompt Engineering',
            'Automation',
        ];
    }
}
