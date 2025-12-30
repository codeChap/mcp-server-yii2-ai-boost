<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use codechap\yii2boost\Mcp\Tools\Base\BaseTool;
use yii\helpers\FileHelper;

/**
 * Tool for searching and retrieving Yii2 AI Guidelines
 */
class SearchGuidelinesTool extends BaseTool
{
    /**
     * @var string Path to guidelines directory
     */
    private $guidelinesPath;

    public function init(): void
    {
        parent::init();
        $this->guidelinesPath = $this->basePath . '/.ai/guidelines';
    }

    public function getName(): string
    {
        return 'search_guidelines';
    }

    public function getDescription(): string
    {
        return 'Searches the local Yii2 AI Guidelines database for framework-specific context, best practices, and code examples. Use this when the user asks "How do I..." questions about Yii2.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'The search term (e.g., "migration", "active record", "controller")',
                ],
                'category' => [
                    'type' => 'string',
                    'enum' => [
                        'all', 'core', 'database', 'http_web', 'console', 
                        'views_templating', 'forms_validation', 'security', 'auth_rbac'
                    ],
                    'description' => 'Optional category to filter results',
                    'default' => 'all'
                ]
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $query = strtolower($arguments['query']);
        $category = $arguments['category'] ?? 'all';
        $results = [];

        if (!is_dir($this->guidelinesPath)) {
            return "No guidelines found at {$this->guidelinesPath}. Run 'php yii boost/install' first.";
        }

        // 1. Find relevant files
        $files = FileHelper::findFiles($this->guidelinesPath, [
            'only' => ['*.md'],
            'recursive' => true,
        ]);

        foreach ($files as $file) {
            $relativePath = str_replace($this->guidelinesPath . '/', '', $file);
            $fileCategory = dirname($relativePath);

            // Filter by category if specified
            if ($category !== 'all' && $fileCategory !== $category && strpos($fileCategory, $category) === false) {
                continue;
            }

            $content = file_get_contents($file);
            $filename = basename($file);
            
            // Heuristic Score
            $score = 0;

            // Filename Match (High value)
            if (strpos(strtolower($filename), $query) !== false) {
                $score += 10;
            }

            // Content Match (Occurrences)
            $matches = substr_count(strtolower($content), $query);
            $score += min($matches, 5); // Cap at 5 points for content matches

            if ($score > 0) {
                $results[] = [
                    'path' => $relativePath,
                    'score' => $score,
                    'content' => $content
                ];
            }
        }

        // 2. Sort by score
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 3. Limit and Format Output
        $topResults = array_slice($results, 0, 3); // Return top 3 matches
        
        if (empty($topResults)) {
            return "No guidelines found matching '{$query}'.";
        }

        $formattedOutput = "Found " . count($topResults) . " relevant guidelines:\n\n";

        foreach ($topResults as $result) {
            $formattedOutput .= "--- File: {$result['path']} ---\n";
            $formattedOutput .= $result['content'] . "\n\n";
        }

        return $formattedOutput;
    }
}
