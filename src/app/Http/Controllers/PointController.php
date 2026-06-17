<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Crm\MemberPointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    public function earn(Request $request, Member $member, MemberPointService $memberPointService): JsonResponse
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'activity_name' => ['nullable', 'string', 'max:100'],
            'send_whatsapp' => ['nullable', 'boolean'],
        ]);

        $transaction = $memberPointService->addPoints(
            member: $member,
            points: (int) $data['points'],
            userId: Auth::id(),
            activityName: $data['activity_name'] ?? 'Pembelian Produk',
            sendWhatsapp: $data['send_whatsapp'] ?? true,
        );

        return response()->json([
            'message' => 'Poin berhasil ditambahkan.',
            'transaction' => $transaction,
            'member' => $member->refresh(),
        ]);
    }

    public function redeem(Request $request, Member $member, MemberPointService $memberPointService): JsonResponse
    {
        $data = $request->validate([
            'send_whatsapp' => ['nullable', 'boolean'],
        ]);

        $transaction = $memberPointService->redeem(
            member: $member,
            userId: Auth::id(),
            sendWhatsapp: $data['send_whatsapp'] ?? true,
        );

        return response()->json([
            'message' => 'Redeem berhasil.',
            'transaction' => $transaction,
            'member' => $member->refresh(),
        ]);
    }
}
