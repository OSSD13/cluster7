<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrelloService;
use Illuminate\Support\Facades\Log;

class CacheTrelloData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trello:cache {--force : Force refresh of cache even if not expired}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache Trello data to improve application performance';

    /**
     * The Trello service instance.
     *
     * @var \App\Services\TrelloService
     */
    protected $trelloService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\TrelloService  $trelloService
     * @return void
     */
    public function __construct(TrelloService $trelloService)
    {
        parent::__construct();
        $this->trelloService = $trelloService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            $this->error('Trello API credentials are not configured.');
            return 1;
        }

        $forceRefresh = $this->option('force');
        
        $this->info('Starting to cache Trello data...');
        
        try {
            // Cache basic user info
            $this->info('Caching user information...');
            $user = $this->trelloService->getMe($useCache = !$forceRefresh);
            if (!$user) {
                $this->warn('Failed to cache user information.');
            }
            
            // Cache organizations
            $this->info('Caching organizations...');
            $organizations = $this->trelloService->getOrganizations(
                ['name', 'displayName', 'url', 'website', 'desc', 'logoHash'], 
                !$forceRefresh
            );
            $this->info('Cached ' . count($organizations) . ' organizations.');
            
            // Cache all boards with members
            $this->info('Caching boards...');
            $boards = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                ['members' => true, 'member_fields' => 'fullName,username,avatarHash'],
                !$forceRefresh
            );
            $this->info('Cached ' . count($boards) . ' boards.');
            
            // Cache board members for each board
            $this->info('Caching board members...');
            $memberCount = 0;
            foreach ($boards as $board) {
                $members = $this->trelloService->getBoardMembers($board['id'], ['fullName', 'username', 'avatarHash'], !$forceRefresh);
                $memberCount += count($members);
                
                // Cache member emails
                foreach ($members as $member) {
                    $this->trelloService->getMemberEmail($member['id']);
                }
            }
            $this->info("Cached member data for {$memberCount} board members.");
            
            // Cache organization members
            $this->info('Caching organization members...');
            $orgMemberCount = 0;
            foreach ($organizations as $org) {
                $orgMembers = $this->trelloService->getOrganizationMembers(
                    $org['id'], 
                    ['fullName', 'username', 'avatarHash'],
                    !$forceRefresh
                );
                $orgMemberCount += count($orgMembers);
            }
            $this->info("Cached member data for {$orgMemberCount} organization members.");
            
            $this->info('All Trello data has been cached successfully!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error caching Trello data: ' . $e->getMessage());
            Log::error('Error caching Trello data: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }
    }
}
