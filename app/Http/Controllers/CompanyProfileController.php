<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;

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


    }
