<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class CompanyProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyProfiles = CompanyProfile::get();
        
        return response()->json([
            'status' => 'success', 
            'data'   => $companyProfiles
        ], 200);
    }

    // عرض ملف شركة محدد برقم الـ ID بصيغة JSON
    public function show(int $id)
    {
        // البحث عن ملف الشركة، وإرجاع 404 إذا لم يوجد
        $companyProfile = CompanyProfile::findOrFail($id);
        
        return response()->json([
            'status' => 'success', 
            'data'   => $companyProfile
        ], 200);
    }

    // إضافة ملف شركة جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'       => 'required|string|max:255',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'address'            => 'nullable|string',
            'whatsapp_number'    => 'nullable|string|max:20',
            'support_number'     => 'nullable|string|max:20',
            'currency'           => 'required|string|max:10',
            'price_per_kwh'      => 'required|numeric|min:0',
            'reading_cycle_days' => 'required|integer|min:1',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('company_logos', 'public');
        }

        $companyProfile = CompanyProfile::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة بيانات الشركة بنجاح',
            'data'   => $companyProfile
        ], 201);
    }

    // تعديل بيانات شركة
    public function update(Request $request, int $id)
    {
        $companyProfile = CompanyProfile::findOrFail($id);

        $validated = $request->validate([
            'company_name'       => 'required|string|max:255',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'address'            => 'nullable|string',
            'whatsapp_number'    => 'nullable|string|max:20',
            'support_number'     => 'nullable|string|max:20',
            'currency'           => 'required|string|max:10',
            'price_per_kwh'      => 'required|numeric|min:0',
            'reading_cycle_days' => 'required|integer|min:1',
        ]);

        if ($request->hasFile('logo')) {
            // حذف الشعار القديم إن وجد
            if ($companyProfile->logo) {
                Storage::disk('public')->delete($companyProfile->logo);
            }
            $validated['logo'] = $request->file('logo')->store('company_logos', 'public');
        }

        $companyProfile->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات الشركة بنجاح',
            'data'   => $companyProfile
        ], 200);
    }

    // حذف ملف شركة
    public function destroy(int $id)
    {
        $companyProfile = CompanyProfile::findOrFail($id);
        
        if ($companyProfile->logo) {
            Storage::disk('public')->delete($companyProfile->logo);
        }

        $companyProfile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف ملف الشركة بنجاح'
        ], 200);
    }


    }
