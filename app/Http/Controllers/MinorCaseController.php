<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MinorCaseRequest;
use App\Http\Resources\MinorCaseResource;
use App\Services\MinorCaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class MinorCaseController extends Controller
{
    /**
     * The minor case service.
     *
     * @var MinorCaseService
     */
    private MinorCaseService $minorCaseService;

    /**
     * Create a new controller instance.
     *
     * @param MinorCaseService $minorCaseService
     */
    public function __construct(MinorCaseService $minorCaseService)
    {
        $this->middleware('auth')->except(['index']);
        $this->minorCaseService = $minorCaseService;
    }

    /**
     * Get the user ID, falling back to null if no authenticated user.
     */
    private function getUserId(): ?int
    {
        return auth()->check() ? auth()->id() : null;
    }

    /**
     * Display a listing of minor cases for a specific board.
     *
     * @param Request $request
     * @return ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'board_id' => 'required|string',
        ]);

        $minorCases = $this->minorCaseService->getByBoard(
            $request->input('board_id'),
            $this->getUserId()
        );

        return MinorCaseResource::collection($minorCases);
    }

    /**
     * Store a newly created minor case.
     *
     * @param MinorCaseRequest $request
     * @return JsonResponse
     */
    public function store(MinorCaseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $this->getUserId();

        $minorCase = $this->minorCaseService->create($data);

        return response()->json(
            new MinorCaseResource($minorCase),
            201
        );
    }

    /**
     * Update the specified minor case.
     *
     * @param MinorCaseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(MinorCaseRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $this->getUserId();

        $minorCase = $this->minorCaseService->update($id, $data, $this->getUserId());

        return response()->json(
            new MinorCaseResource($minorCase)
        );
    }

    /**
     * Remove the specified minor case.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->minorCaseService->delete($id, $this->getUserId());

        return response()->json(null, 204);
    }
} 