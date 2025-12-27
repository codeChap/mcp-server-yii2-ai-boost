<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Resources;

use Yii;

/**
 * Guidelines Resource
 *
 * Provides access to application and framework guidelines
 */
class GuidelinesResource extends BaseResource
{
    public function getName(): string
    {
        return 'Framework and Application Guidelines';
    }

    public function getDescription(): string
    {
        return 'Yii2 framework guidelines and best practices';
    }

    public function read(): mixed
    {
        $basePath = $this->basePath ?: Yii::getAlias('@app');
        $claudeFile = $basePath . '/CLAUDE.md';

        if (file_exists($claudeFile)) {
            return [
                'content' => file_get_contents($claudeFile),
                'type' => 'markdown',
            ];
        }

        return [
            'content' => 'Guidelines not yet installed. Run: php yii boost/install',
            'type' => 'text',
        ];
    }
}
