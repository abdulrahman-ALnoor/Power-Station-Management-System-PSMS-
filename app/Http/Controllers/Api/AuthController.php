<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. التحقق من المدخلات
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. البحث عن المستخدم عبر البريد الإلكتروني
        $user = User::where('email', $request->email)->first();

        // 3. التحقق من وجود المستخدم
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني غير موجود في قاعدة البيانات.',
            ], 404);
        }

        // 4. التحقق من صحة كلمة المرور
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور غير صحيحة.',
            ], 401);
        }

        // 5. إنشاء التوكن (Token) بنجاح
        $token = $user->createToken('ReaderAccess')->plainTextToken;

        // 6. إرجاع الاستجابة المطلوبة
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token
            ]
        ], 200);
    }
}
