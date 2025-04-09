use App\Models\MinorCase;
use App\Policies\MinorCasePolicy;

// ... existing code ...

    protected $policies = [
        // ... existing code ...
        MinorCase::class => MinorCasePolicy::class,
    ];

// ... existing code ...
