<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCompanyProfileRequest;
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
                    return $this->error('بيانات الشركة غير موجودة', 404);
                }
        return $this->success('تم جلب بيانات الشركة بنجاح', new CompanyProfileResource($company), 200);
    }

    /**
     * ملف الشركة سجل وحيد (Singleton) — نتجاهل الـ id الممرر ونرجع نفس السجل دايماً،
     * بنفس منطق update()، عشان يتوافق مع راوت GET /company-profiles/{id}.
     */
    public function show()
    {
        return $this->index();
    }

    /**
     * إنشاء ملف الشركة — يُسمح مرة وحدة بس (لو موجود أصلاً، نرفض ونطلب استخدام update).
     */
    public function store(StoreCompanyProfileRequest $request)
    {
        if (CompanyProfile::exists()) {
            return $this->error('بيانات الشركة موجودة بالفعل. استخدم التحديث بدلاً من الإنشاء.', 422);
        }

        $company = CompanyProfile::create($request->validated());

        return $this->success('تم إنشاء بيانات الشركة بنجاح', new CompanyProfileResource($company), 201);
    }

    public function update(UpdateCompanyProfileRequest $request)
    {
        $company = CompanyProfile::first();

                if (!$company) {
            return $this->error('بيانات الشركة غير موجودة', 404);
        }
        $company->update($request->validated());

        return $this->success('تم تحديث بيانات الشركة بنجاح', new CompanyProfileResource($company), 200);
    }

    /**
     * حذف ملف الشركة معطّل عمداً — بيانات الشركة (خصوصاً سعر الكيلوواط) يعتمد
     * عليها احتساب كل قراءات العدادات والفواتير، فحذفها يكسر النظام بالكامل.
     */
    public function destroy()
    {
        return $this->error(
            'لا يمكن حذف بيانات الشركة لأن باقي النظام (الفواتير وقراءات العدادات) يعتمد عليها. عدّلها بدلاً من حذفها.',
            403
        );
    }
}
