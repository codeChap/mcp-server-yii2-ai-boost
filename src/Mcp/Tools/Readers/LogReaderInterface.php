<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Readers;

/**
 * Interface for log readers
 *
 * Defines the contract for reading logs from different sources
 * (file, database, memory, etc.)
 */
interface LogReaderInterface
{
    /**
     * Read logs from the source
     *
     * @param array $params Filter and pagination parameters:
     *   - levels: array of level names (error, warning, info, trace, profile)
     *   - categories: array of category patterns
     *   - limit: max number of entries to return
     *   - offset: pagination offset
     *   - search: keyword search in message
     *   - time_range: array with 'start' and 'end' Unix timestamps
     *   - include_trace: whether to include stack traces
     * @return array Normalized log entries with summary
     */
    public function read(array $params): array;

    /**
     * Check if this reader is available (target configured)
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get the source name (for identification in results)
     *
     * @return string
     */
    public function getSource(): string;
}
