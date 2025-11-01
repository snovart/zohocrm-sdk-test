<?php

namespace App\Services\Zoho\Contracts;

interface ZohoClient
{
    /**
     * Fetch a page of records from a Zoho module.
     * Expected $params: ['since' => 'ISO8601', 'cursor' => 'string', 'page' => 1, 'per_page' => 200]
     */
    public function fetchRecords(string $module, array $params = []): array;

    /**
     * Return the next cursor/token from a Zoho paginated response if any.
     */
    public function nextCursor(array $response): ?string;
}
