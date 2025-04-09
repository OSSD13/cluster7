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
        try {
            // Try to get from settings table first
            $this->apiKey = Setting::get('trello_api_key', env('TRELLO_API_KEY', ''));
            $this->apiToken = Setting::get('trello_api_token', env('TRELLO_API_TOKEN', ''));
        } catch (\Exception $e) {
            // Fallback to env if settings table doesn't exist yet or there's any other error
            Log::info('Falling back to environment variables for Trello credentials: ' . $e->getMessage());
            $this->apiKey = env('TRELLO_API_KEY', '');
            $this->apiToken = env('TRELLO_API_TOKEN', '');
        }
    }

    /**
     * Check if Trello credentials are configured.
     *
     * @return bool
     */
    public function hasValidCredentials()
    {
        $keyExists = !empty($this->apiKey);
        $tokenExists = !empty($this->apiToken);
        
        // Log the status of credentials
        \Log::info('Trello credentials status', [
            'keyExists' => $keyExists,
            'tokenExists' => $tokenExists
        ]);
        
        return $keyExists && $tokenExists;
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
     * Get avatar URL base.
     *
     * @return string
     */
    public function getAvatarBaseUrl()
    {
        return 'https://trello-avatars.s3.amazonaws.com/';
    }
    
    /**
     * Get organization logo URL base.
     *
     * @return string
     */
    public function getLogoBaseUrl()
    {
        return 'https://trello-logos.s3.amazonaws.com/';
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

        return $this->getAvatarBaseUrl() . "{$avatarHash}/{$size}.png";
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

        return $this->getLogoBaseUrl() . "{$orgId}/{$logoHash}/{$size}.png";
    }

    /**
     * Test the API connection with current credentials
     *
     * @return array with success status and message
     */
    public function testConnection()
    {
        try {
            if (!$this->hasValidCredentials()) {
                Log::error('Trello test connection failed: Invalid credentials');
                return [
                    'success' => false,
                    'message' => 'Invalid API credentials. Please check your API key and token.'
                ];
            }
            
            $url = $this->baseUrl . 'members/me';
            
            // Log the request details for debugging
            Log::info('Testing Trello connection', [
                'url' => $url,
                'apiKeyLength' => strlen($this->apiKey),
                'apiTokenLength' => strlen($this->apiToken),
                'baseUrl' => $this->baseUrl,
                'requestHeaders' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
            
            $response = Http::withOptions([
                'verify' => false,  // Disable SSL verification for testing
                'timeout' => 30,    // Increase timeout
                'http_errors' => false // Don't throw exceptions for HTTP errors
            ])->get($url, [
                'key' => $this->apiKey,
                'token' => $this->apiToken,
                'boards' => 'open',
                'board_fields' => 'name,url',
                'board_limit' => 5
            ]);

            if ($response->successful()) {
                $user = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'user' => $user
                ];
            }

            \Log::warning('Trello API connection test failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect: ' . $response->status() . ' ' . $response->body()
            ];
        } catch (\Exception $e) {
            \Log::error('Exception testing Trello API connection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
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

    /**
     * Get the base URL for Trello API.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
