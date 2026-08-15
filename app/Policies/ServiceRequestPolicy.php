<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    /**
     * ملاحظة: لا داعي للتحقق من دور "admin" هنا،
     * لأن Gate::before بـ AppServiceProvider يعطيه صلاحية كل شي تلقائياً
     * ويوقف تنفيذ باقي الميثودات بهالكلاس من أساسه.
     */

    /**
     * هل يقدر يشوف قائمة الطلبات (index)؟
     * ملاحظة: الفلترة الفعلية (مين يشوف شنو) تصير بالكنترولر
     * حسب الدور، هذي الميثود بس تتحقق إذا عنده صلاحية دخول الصفحة أصلاً.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['engineer', 'reader', 'accountant']);
    }

    /**
     * هل يقدر يشوف طلب معين بذاته (show)؟
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        // المهندس: بس الطلبات المسندة له
        if ($user->hasRole('engineer')) {
            return $serviceRequest->assigned_engineer_id === $user->id;
        }

        // القارئ: بس الطلبات اللي أنشأها هو
        if ($user->hasRole('reader')) {
            return $serviceRequest->created_by === $user->id;
        }

        // المحاسب: يشوف كل الطلبات
        if ($user->hasRole('accountant')) {
            return true;
        }

        return false;
    }

    /**
     * هل يقدر يعدّل الطلب (update / change-status)؟
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        // المهندس: يغيّر حالة الطلب الخاص فيه بس
        if ($user->hasRole('engineer')) {
            return $serviceRequest->assigned_engineer_id === $user->id;
        }

        // القارئ: يعدّل طلبه هو بس (قبل ما يوافق عليه الأدمن أو يتغيّر)
        if ($user->hasRole('reader')) {
            return $serviceRequest->created_by === $user->id;
        }

        // المحاسب: يعدّل بس الطلبات اللي أنشأها هو بنفسه
        if ($user->hasRole('accountant')) {
            return $serviceRequest->created_by === $user->id;
        }

        return false;
    }

    /**
     * هل يقدر يحذف الطلب (destroy)؟
     * نفس منطق update: المحاسب ما يحذف إلا اللي أنشأه هو.
     * المهندس والقارئ أصلاً ما عندهم صلاحية service-requests.delete
     * بـ PermissionSeeder، فهذا احتياط إضافي بس.
     */
    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->hasRole('accountant')) {
            return $serviceRequest->created_by === $user->id;
        }

        return false;
    }
}
