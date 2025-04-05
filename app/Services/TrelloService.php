<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TrelloService
{
    /**
     * The Trello API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * The Trello API token.
     *
     * @var string
     */
    protected $apiToken;

    /**
     * The base URL for Trello API.
     *
     * @var string
     */
    protected $baseUrl = 'https://api.trello.com/1/';

    /**
     * Create a new TrelloService instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiKey = Setting::get('trello_api_key', '');
        $this->apiToken = Setting::get('trello_api_token', '');
    }

    /**
     * Check if Trello credentials are configured.
     *
     * @return bool
     */
    public function hasValidCredentials()
    {
        return !empty($this->apiKey) && !empty($this->apiToken);
    }

    /**
     * Get default parameters for Trello API requests.
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        return [
            'key' => $this->apiKey,
            'token' => $this->apiToken
        ];
    }

    /**
     * Get information about the authenticated member (me).
     *
     * @param bool $useCache Whether to use cached results
     * @return array|null
     */
    public function getMe($useCache = true)
    {
        $cacheKey = 'trello_me';

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::get($this->baseUrl . 'members/me', $this->getDefaultParams());

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 3600); // Cache for 1 hour
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting Trello member info: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get boards the authenticated member has access to.
     *
     * @param array $fields Fields to include for boards
     * @param array $options Additional options for the request
     * @param bool $useCache Whether to use cached results
     * @return array
     */
    public function getBoards($fields = ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'], $options = [], $useCache = true)
    {
        $cacheKey = 'trello_boards_' . md5(json_encode($fields) . json_encode($options));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields),
                'filter' => $options['filter'] ?? 'open',
            ]);

            if (isset($options['members']) && $options['members']) {
                $params['members'] = 'all';
                $params['member_fields'] = $options['member_fields'] ?? 'fullName,username,avatarHash';
            }

            if (isset($options['organization']) && $options['organization']) {
                $params['organization'] = true;
                $params['organization_fields'] = $options['organization_fields'] ?? 'name,displayName';
            }

            $response = Http::get($this->baseUrl . 'members/me/boards', $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 1800); // Cache for 30 minutes
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting Trello boards: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get a specific board by ID.
     *
     * @param string $boardId
     * @param array $fields
     * @param bool $useCache
     * @return array|null
     */
    public function getBoard($boardId, $fields = ['name', 'desc', 'url', 'idOrganization'], $useCache = true)
    {
        $cacheKey = 'trello_board_' . $boardId . '_' . md5(json_encode($fields));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields)
            ]);

            $response = Http::get($this->baseUrl . "boards/{$boardId}", $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 1800); // Cache for 30 minutes
                return $data;
            }
        } catch (\Exception $e) {
            Log::error("Error getting Trello board {$boardId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get members of a board.
     *
     * @param string $boardId
     * @param array $fields
     * @param bool $useCache
     * @return array
     */
    public function getBoardMembers($boardId, $fields = ['fullName', 'username', 'avatarHash'], $useCache = true)
    {
        $cacheKey = 'trello_board_members_' . $boardId . '_' . md5(json_encode($fields));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields)
            ]);

            $response = Http::get($this->baseUrl . "boards/{$boardId}/members", $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 1200); // Cache for 20 minutes
                return $data;
            }
        } catch (\Exception $e) {
            Log::error("Error getting members for Trello board {$boardId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get organizations (workspaces) the user belongs to.
     *
     * @param array $fields
     * @param bool $useCache
     * @return array
     */
    public function getOrganizations($fields = ['name', 'displayName', 'url', 'website', 'desc', 'logoHash'], $useCache = true)
    {
        $cacheKey = 'trello_organizations_' . md5(json_encode($fields));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields)
            ]);

            $response = Http::get($this->baseUrl . 'members/me/organizations', $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 3600); // Cache for 1 hour
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting Trello organizations: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get a specific organization by ID.
     *
     * @param string $organizationId
     * @param array $fields
     * @param bool $useCache
     * @return array|null
     */
    public function getOrganization($organizationId, $fields = ['name', 'displayName', 'url', 'website', 'desc', 'logoHash'], $useCache = true)
    {
        $cacheKey = 'trello_organization_' . $organizationId . '_' . md5(json_encode($fields));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields)
            ]);

            $response = Http::get($this->baseUrl . "organizations/{$organizationId}", $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 3600); // Cache for 1 hour
                return $data;
            }
        } catch (\Exception $e) {
            Log::error("Error getting Trello organization {$organizationId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get members of an organization.
     *
     * @param string $organizationId
     * @param array $fields
     * @param bool $useCache
     * @return array
     */
    public function getOrganizationMembers($organizationId, $fields = ['fullName', 'username', 'avatarHash'], $useCache = true)
    {
        $cacheKey = 'trello_organization_members_' . $organizationId . '_' . md5(json_encode($fields));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = array_merge($this->getDefaultParams(), [
                'fields' => implode(',', $fields),
                'members' => 'all'
            ]);

            $response = Http::get($this->baseUrl . "organizations/{$organizationId}/members", $params);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 1800); // Cache for 30 minutes
                return $data;
            }
        } catch (\Exception $e) {
            Log::error("Error getting members for Trello organization {$organizationId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get member email by ID.
     *
     * @param string $memberId
     * @return string|null
     */
    public function getMemberEmail($memberId)
    {
        $cacheKey = 'trello_member_email_' . $memberId;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::get($this->baseUrl . "members/{$memberId}/email", $this->getDefaultParams());

            if ($response->successful()) {
                $email = $response->json()['_value'] ?? null;
                if ($email) {
                    Cache::put($cacheKey, $email, 86400); // Cache for 24 hours
                    return $email;
                }
            }
        } catch (\Exception $e) {
            Log::info("Unable to fetch email for Trello member {$memberId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get avatar URL from hash.
     *
     * @param string $userId
     * @param string $avatarHash
     * @param int $size
     * @return string|null
     */
    public function getAvatarUrl($userId, $avatarHash, $size = 50)
    {
        if (!$avatarHash) {
            return null;
        }

        return "https://trello-avatars.s3.amazonaws.com/{$avatarHash}/{$size}.png";
    }

    /**
     * Get organization logo URL.
     *
     * @param string $orgId
     * @param string $logoHash
     * @param int $size
     * @return string|null
     */
    public function getOrganizationLogoUrl($orgId, $logoHash, $size = 170)
    {
        if (!$logoHash) {
            return null;
        }

        return "https://trello-logos.s3.amazonaws.com/{$orgId}/{$logoHash}/{$size}.png";
    }

    /**
     * Test the Trello API connection.
     *
     * @param string $apiKey
     * @param string $apiToken
     * @return array
     */
    public function testConnection($apiKey, $apiToken)
    {
        try {
            // Clean the API key and token
            $apiKey = trim($apiKey);
            $apiToken = trim($apiToken);

            $baseUrl = $this->baseUrl;
            $params = [
                'key' => $apiKey,
                'token' => $apiToken
            ];

            // Get member basic info
            $memberResponse = Http::get($baseUrl . "members/me", $params);

            if ($memberResponse->successful()) {
                $memberData = $memberResponse->json();
                $username = $memberData['username'] ?? 'Unknown';
                $fullName = $memberData['fullName'] ?? 'Unknown';
                $avatarHash = $memberData['avatarHash'] ?? null;
                $avatarUrl = null;

                // If we have an avatar hash, get the avatar image
                if ($avatarHash) {
                    $avatarUrl = "https://trello-members.s3.amazonaws.com/{$memberData['id']}/{$avatarHash}/170.png";
                }

                // Try to get the user's boards to further verify API access
                $boardsResponse = Http::get($baseUrl . "members/me/boards", array_merge($params, [
                    'fields' => 'name,url',
                    'lists' => 'open',
                    'limit' => 3
                ]));

                $boards = [];
                if ($boardsResponse->successful()) {
                    $boardsData = $boardsResponse->json();
                    foreach ($boardsData as $board) {
                        $boards[] = [
                            'name' => $board['name'] ?? 'Unnamed Board',
                            'url' => $board['url'] ?? '#',
                        ];
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Connected successfully to Trello API',
                    'username' => $username,
                    'fullName' => $fullName,
                    'avatarUrl' => $avatarUrl,
                    'boards' => $boards
                ];
            } else {
                $statusCode = $memberResponse->status();
                $body = $memberResponse->body();

                // Attempt to decode the JSON response for more details
                $errorDetails = json_decode($body, true);
                $errorMessage = isset($errorDetails['message']) ? $errorDetails['message'] : $body;

                return [
                    'success' => false,
                    'message' => "Failed to connect to Trello API: {$statusCode} - {$errorMessage}",
                    'debug_info' => [
                        'status' => $statusCode,
                        'body' => $body,
                    ]
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error connecting to Trello API', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error connecting to Trello API: ' . $e->getMessage(),
                'debug_info' => [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    /**
     * Clear cached Trello data.
     *
     * @return void
     */
    public function clearCache()
    {
        // Laravel's Cache facade doesn't support wildcard deletion directly
        // We'll use the cache store to manually clear all Trello-related cache

        // Clear specific commonly used keys
        Cache::forget('trello_me');

        // Get the cache prefix (if any)
        $prefix = config('cache.prefix', '');

        // Use cache repository to clear cache based on tags if available
        $cacheStore = Cache::getStore();

        if (method_exists($cacheStore, 'flush')) {
            // If using Redis or Memcached, we can attempt to flush all Trello keys
            // For Redis with tags, you could use Cache::tags('trello')->flush()
            // But for a simple approach that works with file store too:

            // Get all cache keys (this works with file cache driver)
            $cacheDir = storage_path('framework/cache/data');
            if (file_exists($cacheDir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($files as $file) {
                    $cacheKey = str_replace('.php', '', $file->getBasename());
                    // If this is a serialized cache file and contains trello in the key
                    if (strpos($cacheKey, 'trello_') !== false) {
                        Cache::forget($cacheKey);
                    }
                }
            }
        }

        // Explicitly clear all known Trello cache keys
        $this->forgetCacheKey('trello_me');
        $this->forgetCacheKeyWithPattern('trello_boards_');
        $this->forgetCacheKeyWithPattern('trello_board_');
        $this->forgetCacheKeyWithPattern('trello_organization_');
        $this->forgetCacheKeyWithPattern('trello_organizations_');
        $this->forgetCacheKeyWithPattern('trello_member_email_');

        // Log the cache clearing operation
        Log::info('Cleared Trello API cache data');
    }

    /**
     * Helper method to forget cache keys with a specific pattern
     *
     * @param string $pattern Pattern to match in cache keys
     * @return void
     */
    private function forgetCacheKeyWithPattern($pattern)
    {
        // For file cache driver
        $cacheDir = storage_path('framework/cache/data');
        if (file_exists($cacheDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                $filename = $file->getBasename();
                if (strpos($filename, $pattern) !== false) {
                    $cacheKey = str_replace('.php', '', $filename);
                    Cache::forget($cacheKey);
                }
            }
        }
    }

    /**
     * Helper method to forget a specific cache key
     *
     * @param string $key The cache key to forget
     * @return void
     */
    private function forgetCacheKey($key)
    {
        Cache::forget($key);
    }
    /**
     * Get story points from Agile Tools plugin data.
     *
     * @param array $pluginData
     * @return int
     */
    private function getStoryPoints($pluginData)
    {
        $agileToolsPluginId = '59d4ef8cfea15a55b0086614';

        foreach ($pluginData as $data) {
            if ($data['idPlugin'] === $agileToolsPluginId) {
                $value = json_decode($data['value'], true);
                return $value['points'] ?? 0;
            }
        }

        return 0;
    }
}
