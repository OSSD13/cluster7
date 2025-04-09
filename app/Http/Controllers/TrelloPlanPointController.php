<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardPlanPoint;
use Illuminate\Support\Facades\Log;

class TrelloPlanPointController extends Controller
{
    /**
     * บันทึกค่า Plan Point ของบอร์ด
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePlanPoint(Request $request)
    {
        try {
            // ตรวจสอบข้อมูลที่ส่งมา
            $validated = $request->validate([
                'board_id' => 'required|string',
                'board_name' => 'nullable|string',
                'plan_point' => 'required|numeric',
            ]);
            
            // บันทึกหรืออัปเดตข้อมูล
            BoardPlanPoint::updateOrCreate(
                ['board_id' => $validated['board_id']], // where board_id = ?
                [
                    'board_name' => $validated['board_name'] ?? '',
                    'plan_point' => $validated['plan_point'],
                ]
            );
            
            // บันทึก log
            Log::info('Plan point saved', [
                'board_id' => $validated['board_id'],
                'plan_point' => $validated['plan_point']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan point saved successfully',
                'data' => $validated
            ]);
        } catch (\Exception $e) {
            // บันทึก log ในกรณีเกิดข้อผิดพลาด
            Log::error('Error saving plan point', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ดึงค่า Plan Point ของบอร์ด
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlanPoint(Request $request)
    {
        try {
            // ตรวจสอบข้อมูลที่ส่งมา
            $boardId = $request->query('board_id');
            if (!$boardId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Board ID is required'
                ], 400);
            }
            
            // ค้นหาข้อมูล
            $planPoint = BoardPlanPoint::where('board_id', $boardId)->first();
            
            if ($planPoint) {
                return response()->json([
                    'success' => true,
                    'plan_point' => $planPoint->plan_point,
                    'board_name' => $planPoint->board_name
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'plan_point' => null,
                    'message' => 'No plan point found for this board'
                ]);
            }
        } catch (\Exception $e) {
            // บันทึก log ในกรณีเกิดข้อผิดพลาด
            Log::error('Error getting plan point', [
                'error' => $e->getMessage(),
                'board_id' => $request->query('board_id')
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
