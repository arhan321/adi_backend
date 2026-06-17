<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Whatsapp\TwilioWhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $digits = preg_replace('/[^0-9]/', '', $request->string('phone')->toString());

        $member = Member::query()
            ->where('phone', 'like', "%{$digits}%")
            ->first();

        return response()->json([
            'found' => (bool) $member,
            'member' => $member,
        ]);
    }

    public function store(Request $request, TwilioWhatsappService $twilioWhatsappService): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['phone'] = $twilioWhatsappService->normalizePhone($data['phone']);
        $data['member_code'] = $this->generateMemberCode();
        $data['created_by'] = Auth::id();
        $data['status'] = Member::STATUS_ACTIVE;
        $data['total_points'] = 0;

        $member = Member::query()->create($data);

        return response()->json([
            'message' => 'Member berhasil dibuat.',
            'member' => $member,
        ], 201);
    }

    protected function generateMemberCode(): string
    {
        do {
            $code = 'KB-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (Member::query()->where('member_code', $code)->exists());

        return $code;
    }
}
