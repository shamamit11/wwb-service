<?php

namespace App\Modules\Ai\Services;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Modules\Ai\Data\RenderedPromptData;

class RenderAiPromptTemplateService
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public function render(AiPromptTemplate|AiPromptTemplateVersion $prompt, array $variables): RenderedPromptData
    {
        $version = $prompt instanceof AiPromptTemplate ? $prompt->activeVersion : $prompt;

        if (! $version instanceof AiPromptTemplateVersion) {
            return new RenderedPromptData('', '', null, [], []);
        }

        $declaredVariables = array_values(array_unique(array_filter(
            is_array($version->variables) ? $version->variables : [],
            static fn ($value): bool => is_string($value) && $value !== '',
        )));

        $missingVariables = array_values(array_filter(
            $declaredVariables,
            static fn (string $variable): bool => ! array_key_exists($variable, $variables),
        ));

        return new RenderedPromptData(
            systemPrompt: $this->replaceVariables($version->system_prompt, $variables),
            userPrompt: $this->replaceVariables($version->user_prompt, $variables),
            outputSchema: $version->output_schema,
            variables: $declaredVariables,
            missingVariables: $missingVariables,
        );
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function replaceVariables(string $template, array $variables): string
    {
        return (string) preg_replace_callback(
            '/{{\s*([a-zA-Z0-9_.-]+)\s*}}/',
            static function (array $matches) use ($variables): string {
                $key = (string) ($matches[1] ?? '');

                if (! array_key_exists($key, $variables)) {
                    return $matches[0];
                }

                $value = $variables[$key];

                if (is_scalar($value) || $value === null) {
                    return (string) $value;
                }

                return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $matches[0];
            },
            $template,
        ) ?? $template;
    }
}
