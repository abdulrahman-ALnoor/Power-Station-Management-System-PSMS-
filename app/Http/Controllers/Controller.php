<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // AuthorizesRequests: يعطينا $this->authorize() اللي تربط تلقائياً
    // مع الـ Policy المطابقة لكل موديل (حسب اسمه) وترمي 403 لو ما نجح التحقق
    use AuthorizesRequests;
}
