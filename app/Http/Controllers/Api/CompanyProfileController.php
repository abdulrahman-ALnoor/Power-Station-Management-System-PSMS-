<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateCompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\CompanyProfile;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyProfileController extends Controller
{
    use ApiResponse;

    public function stats()
    {
        $company = CompanyProfile::first();

        $data = [
            'company_name'       => $company?->company_name,
            'price_per_kwh'      => $company?->price_per_kwh,
            'currency'           => $company?->currency,
            'reading_cycle_days' => $company?->reading_cycle_days,
        ];

        return $this->success('تم جلب إحصائيات الشركة بنجاح', $data, 200);
    }

    public function index()
    {
        $company = CompanyProfile::first();

        if (!$company) {
            return response()->json(['message' => 'بيانات الشركة غير موجودة'], 404);
        }

        return $this->success('تم جلب بيانات الشركة بنجاح', new CompanyProfileResource($company), 200);
    }

    public function update(UpdateCompanyProfileRequest $request)
    {
        $company = CompanyProfile::first();

        if (!$company) {
            return response()->json(['message' => 'بيانات الشركة غير موجودة'], 404);
        }

        $company->update($request->validated());

        return $this->success('تم تحديث بيانات الشركة بنجاح', new CompanyProfileResource($company), 200);
    }
}