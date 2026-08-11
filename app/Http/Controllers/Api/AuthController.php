<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. التحقق من المدخلات (مطابقة للواجهة: اسم المستخدم وكلمة المرور)
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. البحث عن القارئ في قاعدة البيانات
        $user = User::where('username', $request->username)->first();

        // 3. التحقق من صحة البيانات
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
            ], 401);
        }

        // 4. إنشاء التوكن الخاص بالقارئ
        $token = $user->createToken('ReaderAccess')->plainTextToken;

        // 5. إرجاع الاستجابة بنجاح
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'reader_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ],
                'token' => $token
            ]
        ], 200);
    }
}