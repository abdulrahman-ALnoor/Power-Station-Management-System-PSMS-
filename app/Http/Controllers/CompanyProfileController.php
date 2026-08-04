<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyProfileResource;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    /**
     * مسار الإحصائيات (company-profiles/stats)
     */
    public function stats()
    {
        $company = CompanyProfile::first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'company_name'       => $company?->company_name,
                'price_per_kwh'      => $company?->price_per_kwh,
                'currency'           => $company?->currency,
                'reading_cycle_days' => $company?->reading_cycle_days,
            ]
        ], 200);
    }

    public function index()
    {
        $company = CompanyProfile::first();

        if (!$company) {
            return response()->json([
                'status'  => 'error',
                'message' => 'بيانات الشركة غير موجودة'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new CompanyProfileResource($company)
        ], 200);
    }

    public function update(Request $request)
    {
        $company = CompanyProfile::first();

        if (!$company) {
            return response()->json([
                'status'  => 'error',
                'message' => 'بيانات الشركة غير موجودة'
            ], 404);
        }

        $validated = $request->validate([
            'company_name'       => 'sometimes|string|max:200',
            'logo'               => 'nullable|string|max:255',
            'address'            => 'nullable|string',
            'whatsapp_number'    => 'nullable|string|max:30',
            'support_number'     => 'nullable|string|max:30',
            'currency'           => 'sometimes|string|max:20',
            'price_per_kwh'      => 'sometimes|numeric|min:0',
            'reading_cycle_days' => 'nullable|integer|min:1',
        ]);

        $company->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'تم تحديث بيانات الشركة بنجاح',
            'data'    => new CompanyProfileResource($company)
        ], 200);
    }
}