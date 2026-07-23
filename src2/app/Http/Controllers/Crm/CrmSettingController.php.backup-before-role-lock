<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'setting' => CrmSetting::current(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'redeem_required_points' => ['required', 'integer', 'min:1', 'max:100'],
            'reward_name' => ['required', 'string', 'max:100'],
            'promo_is_active' => ['required', 'boolean'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'retention_send_time' => ['required', 'date_format:H:i'],
            'auto_send_whatsapp' => ['required', 'boolean'],
            'point_message_template' => ['nullable', 'string', 'max:1000'],
            'redeem_message_template' => ['nullable', 'string', 'max:1000'],
            'retention_message_template' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['retention_send_time'] .= ':00';
        $data['updated_by'] = Auth::id();

        $setting = CrmSetting::current();
        $setting->update($data);

        return response()->json([
            'message' => 'Konfigurasi CRM berhasil diperbarui.',
            'setting' => $setting->refresh(),
        ]);
    }
}
