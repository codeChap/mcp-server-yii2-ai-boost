<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Resources;

use Yii;

/**
 * Boost Configuration Resource
 *
 * Provides access to the Yii2 AI Boost configuration
 */
class BoostConfigResource extends BaseResource
{
    public function getName(): string
    {
        return 'Yii2 AI Boost Configuration';
    }

    public function getDescription(): string
    {
        return 'Current Yii2 AI Boost package configuration and status';
    }

    public function read(): mixed
    {
        $basePath = $this->basePath ?: Yii::getAlias('@app');
        $configFile = $basePath . '/boost.json';

        if (file_exists($configFile)) {
            $content = json_decode(file_get_contents($configFile), true);
            return [
                'content' => json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'type' => 'json',
            ];
        }

        return [
            'content' => 'Boost configuration not found. Run: php yii boost/install',
            'type' => 'text',
        ];
    }
}
