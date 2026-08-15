<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;

class MeterReadingPolicy
{
    /**
     * ملاحظة: لا داعي للتحقق من دور "admin" هنا،
     * لأن Gate::before بـ AppServiceProvider يعطيه صلاحية كل شي تلقائياً.
     */

    /**
     * هل يقدر يشوف قائمة القراءات (index)؟
     * حسب المستند: القارئ عنده CRUD كامل على القراءات (لكن قراءاته هو بس).
     * المحاسب ما له صلاحية meter-readings أصلاً بالسيدر، فما توصله الميدلوير أصلاً.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('reader');
    }

    /**
     * هل يقدر يشوف قراءة معينة بذاتها (show)؟
     * القارئ: بس القراءة اللي أنشأها هو.
     */
    public function view(User $user, MeterReading $meterReading): bool
    {
        if ($user->hasRole('reader')) {
            return $meterReading->created_by === $user->id;
        }

        return false;
    }

    /**
     * هل يقدر يعدّل القراءة (update)؟
     * القارئ: بس القراءة اللي أنشأها هو.
     */
    public function update(User $user, MeterReading $meterReading): bool
    {
        if ($user->hasRole('reader')) {
            return $meterReading->created_by === $user->id;
        }

        return false;
    }

    /**
     * هل يقدر يحذف القراءة (destroy)؟
     * القارئ: بس القراءة اللي أنشأها هو.
     */
    public function delete(User $user, MeterReading $meterReading): bool
    {
        if ($user->hasRole('reader')) {
            return $meterReading->created_by === $user->id;
        }

        return false;
    }
}



