<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Crm\MemberPointService;
use App\Support\CrmAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class PointController extends Controller
{
    public function earn(
        Request $request,
        Member $member,
        MemberPointService $memberPointService,
    ): JsonResponse {
        abort_unless(
            CrmAccess::canManagePoints($request->user()),
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses untuk menambahkan poin.',
        );

        $data = $request->validate([
            'points' => [
                'required',
                'integer',
                'in:'.MemberPointService::POINTS_PER_PURCHASE,
            ],
            'activity_name' => ['nullable', 'string', 'max:100'],
            'send_whatsapp' => ['nullable', 'boolean'],
        ], [
            'points.in' => 'Setiap pembelian hanya menambahkan 1 poin.',
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

    public function redeem(
        Request $request,
        Member $member,
        MemberPointService $memberPointService,
    ): JsonResponse {
        abort_unless(
            CrmAccess::canManagePoints($request->user()),
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses untuk melakukan redeem.',
        );

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
