<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrelloSettingsController;
use Illuminate\Http\Request;
use App\Http\Controllers\TrelloTeamController;
use App\Http\Controllers\SavedReportController;
use App\Http\Controllers\SprintSettingsController;
use App\Http\Controllers\TrelloController;
use App\Http\Controllers\SprintReportController;
use App\Http\Controllers\BacklogController;
use App\Http\Controllers\ConfirmController;
use App\Http\Controllers\CompleteController;
use App\Http\Controllers\MinorCasesController;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Http\Controllers\TrelloPlanPointController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware(['auth', \App\Http\Middleware\CheckApproved::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Management Routes - Only for non-admin views
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // Add explicit route for users/report to prevent route confusion
    Route::get('users/report', [App\Http\Controllers\TrelloController::class, 'storyPointsReport'])->name('users.report');

    // Report Routes
    Route::get('/report', [App\Http\Controllers\TrelloController::class, 'storyPointsReport'])->name('story.points.report');

    // Add this route to fetch story points
    Route::get('/trello/data', [App\Http\Controllers\TrelloController::class, 'fetchStoryPoints'])->name('fetch.story.points');

    // Add this route to fetch bug cards
    Route::get('/trello/bug-cards', [App\Http\Controllers\TrelloController::class, 'fetchBugCards'])->name('fetch.bug.cards');

    // Add this route to save board selection
    Route::post('/save-board-selection', [App\Http\Controllers\TrelloController::class, 'saveBoardSelection'])->name('save.board.selection');

    // Saved Reports Routes
    Route::resource('/saved-reports', SavedReportController::class);
    Route::post('/save-report', [SavedReportController::class, 'store'])->name('report.save');
    Route::get('/saved-reports/{savedReport}/export-template', [SavedReportController::class, 'exportTemplate'])->name('saved-reports.export-template');
    Route::post('/export-to-csv', [SavedReportController::class, 'exportToCsv'])->name('export.to.csv');

    // Minor Cases Routes
    Route::prefix('minor-cases')->name('minor-cases.')->group(function () {
        Route::get('/', [App\Http\Controllers\MinorCasesController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\MinorCasesController::class, 'getMinorCasesData'])->name('data');
        Route::get('/api', [App\Http\Controllers\MinorCaseController::class, 'index'])->name('api.index');
        Route::post('/api', [App\Http\Controllers\MinorCaseController::class, 'store'])->name('api.store');
        Route::put('/api/{id}', [App\Http\Controllers\MinorCaseController::class, 'update'])->name('api.update');
        Route::delete('/api/{id}', [App\Http\Controllers\MinorCaseController::class, 'destroy'])->name('api.destroy');
    });

    // Admin Only Routes
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // Admin User Management Routes
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::delete('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

        // Trello API Settings
        Route::prefix('trello')->name('trello.')->group(function () {
            // Trello Settings
            Route::get('settings', [TrelloSettingsController::class, 'index'])->name('settings.index');
            Route::post('settings', [TrelloSettingsController::class, 'update'])->name('settings.update');
            Route::post('test-connection', [TrelloSettingsController::class, 'testApiConnection'])->name('test-connection');
        });

        // Sprint Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('sprint', [SprintSettingsController::class, 'index'])->name('sprint');
            Route::post('sprint', [SprintSettingsController::class, 'update'])->name('sprint.update');
            Route::post('sprint/generate-now', [SprintSettingsController::class, 'generateNow'])->name('sprint.generate-now');
            Route::post('sprint/set-current', [SprintSettingsController::class, 'setCurrentSprint'])->name('sprint.set-current');
        });
    });

    // Trello Teams - Accessible to all authenticated users
    Route::prefix('trello')->name('trello.')->group(function () {
        Route::get('teams', [TrelloTeamController::class, 'index'])->name('teams.index');
        Route::get('teams/{organization}', [TrelloTeamController::class, 'show'])->name('teams.show');
        Route::get('teams/refresh', [TrelloTeamController::class, 'refresh'])->name('teams.refresh');
        Route::get('teams/{id}', [TrelloTeamController::class, 'show'])->name('teams.show');
        Route::get('boards/{id}', [TrelloTeamController::class, 'viewBoard'])->name('boards.show');
        Route::get("home", function () {
            return view('dashboard');
        })->name('home');

        // Add route for trello.log
        Route::post('log', [TrelloController::class, 'logMessage'])->name('log');

        // Add routes for Plan Points
        Route::post('save-plan-point', [TrelloPlanPointController::class, 'savePlanPoint'])->name('save.plan.point');
        Route::get('get-plan-point', [TrelloPlanPointController::class, 'getPlanPoint'])->name('get.plan.point');
    });

    // Non-admin routes
    Route::get('sprint', [SprintSettingsController::class, 'index'])->name('sprint');
    Route::middleware([\App\Http\Middleware\NonAdminMiddleware::class])->group(function () {
        Route::get('my-teams', [TrelloTeamController::class, 'myTeams'])->name('my-teams.index');
        Route::get('my-teams/{id}/profile', [TrelloTeamController::class, 'generateTeamProfile'])->name('my-teams.profile');
    });

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Sprint Reports Routes
    Route::get('/sprints', [\App\Http\Controllers\SprintReportController::class, 'index'])->name('sprints.index');
    Route::get('/sprints/{sprint}', [\App\Http\Controllers\SprintReportController::class, 'showSprint'])->name('sprints.show');
    Route::get('/sprint-reports/{report}', [\App\Http\Controllers\SprintReportController::class, 'showReport'])->name('sprint-reports.show');
    Route::delete('/sprint-reports/{report}', [\App\Http\Controllers\SprintReportController::class, 'delete'])
        ->middleware(\App\Http\Middleware\AdminMiddleware::class)
        ->name('sprint-reports.delete');
    Route::get('reports', [\App\Http\Controllers\SprintReportController::class, 'getUserReports'])->name('reports');

    // Backlog Routes
    Route::get('/backlog', [\App\Http\Controllers\BacklogController::class, 'index'])->name('backlog.index');
    Route::delete('/backlog/{id}', [\App\Http\Controllers\BacklogController::class, 'destroy'])->name('backlog.destroy');
    Route::put('/backlog/{id}', [\App\Http\Controllers\BacklogController::class, 'update'])->name('backlog.update');
});

// Only enable in development environment
if (app()->environment('local')) {
    // Debugging route
    Route::get('/debug/users', function () {
        return response()->json([
            'users' => \App\Models\User::all(),
            'count' => \App\Models\User::count()
        ]);
    });

    // Test route for sprint logic
    Route::get('/debug/sprint-info', function () {
        $nextSprintNumber = \App\Models\Sprint::getNextSprintNumber();
        $currentSprint = \App\Models\Sprint::getCurrentSprint();

        // Get current sprint settings
        $sprintSettingsController = new \App\Http\Controllers\SprintSettingsController();
        $currentSprintNumber = $sprintSettingsController->getCurrentSprintNumber();

        return response()->json([
            'next_sprint_number' => $nextSprintNumber,
            'current_sprint' => $currentSprint,
            'controller_current_sprint_number' => $currentSprintNumber,
            'existing_sprints' => \App\Models\Sprint::orderBy('sprint_number', 'desc')->get(['id', 'sprint_number', 'start_date', 'end_date', 'status'])
        ]);
    });
}

// Trello Story Points Report routes
Route::get('trello/data', [TrelloController::class, 'fetchTrelloData']);
Route::get('trello/bug-cards', [TrelloController::class, 'fetchBugCards']);
Route::get('trello/sprint-info', [TrelloController::class, 'getSprintInfo']);

Route::get('/sendemail', function () {
    return view('sendEmail');
});

// routes/web.php
Route::get('/confirm', [ConfirmController::class, 'index'])->name('confirm');

// routes/web.php
Route::get('/complete', [CompleteController::class, 'index'])->name('complete');

// Test Trello API route
Route::get('/test-trello-api', function () {
    return view('trello.api-test');
});

// Trello API test endpoints
Route::prefix('trello-api-test')->name('trello.api.test.')->group(function () {
    Route::get('/connection', function () {
        $trelloService = app(App\Services\TrelloService::class);
        $result = $trelloService->testConnection();

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Connection test failed',
            'user' => $result['user'] ?? null,
        ]);
    })->name('connection');

    Route::get('/boards', function () {
        $trelloService = app(App\Services\TrelloService::class);

        try {
            $boards = $trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                [
                    'filter' => 'open',
                    'organization' => true,
                    'organization_fields' => 'name,displayName'
                ]
            );

            return response()->json([
                'success' => true,
                'boards' => $boards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching boards: ' . $e->getMessage()
            ], 500);
        }
    })->name('boards');

    Route::get('/board/{boardId}/cards', function ($boardId) {
        $trelloService = app(App\Services\TrelloService::class);

        try {
            $response = Http::withOptions(['verify' => false])->get($trelloService->getBaseUrl() . "boards/{$boardId}/cards", [
                'key' => Setting::get('trello_api_key'),
                'token' => Setting::get('trello_api_token'),
                'fields' => 'id,name,desc,idList,labels',
                'members' => 'true',
                'member_fields' => 'id,fullName,username',
                'limit' => 10
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'cards' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch cards: ' . $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cards: ' . $e->getMessage()
            ], 500);
        }
    })->name('board.cards');
});

// Add a route for boards
Route::get('/api/boards', function () {
    $trelloService = app(App\Services\TrelloService::class);

    try {
        $boards = $trelloService->getBoards(['id', 'name']);
        return response()->json($boards);
    } catch (\Exception $e) {
        return response()->json([]);
    }
});

// Add new routes for updating and deleting minor cases
Route::put('/api/minor-cases/{id}', [MinorCasesController::class, 'update'])->name('minor-cases.update');
Route::delete('/api/minor-cases/{id}', [MinorCasesController::class, 'destroy'])->name('minor-cases.destroy');
