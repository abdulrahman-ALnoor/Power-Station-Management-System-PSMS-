<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    /**
     * ملاحظة: لا داعي للتحقق من دور "admin" هنا،
     * لأن Gate::before بـ AppServiceProvider يعطيه صلاحية كل شي تلقائياً.
     */

    /**
     * هل يقدر يشوف قائمة المعدات (index)؟
     * حسب المستند: بس المهندس والقارئ يتعاملون مع المعدات (كل وحد يشوف معداته هو).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['engineer', 'reader']);
    }

    /**
     * هل يقدر يشوف معدة معينة بذاتها (show)؟
     * المهندس والقارئ: بس المعدة المرتبطة فيه (user_id).
     */
    public function view(User $user, Equipment $equipment): bool
    {
        if ($user->hasAnyRole(['engineer', 'reader'])) {
            return $equipment->user_id === $user->id;
        }

        return false;
    }

    /**
     * هل يقدر يضيف وصف/ملاحظة للمعدة (describe)؟
     * حسب المستند: (RU) تحديث محدود بس — إضافة وصف، مو كل بيانات المعدة.
     * بس على المعدة الخاصة فيه.
     */
    public function describe(User $user, Equipment $equipment): bool
    {
        if ($user->hasAnyRole(['engineer', 'reader'])) {
            return $equipment->user_id === $user->id;
        }

        return false;
    }

    /**
     * التحديث الكامل لكل بيانات المعدة: أدمن بس (Gate::before)،
     * ماكو دور ثاني عنده equipment.update بالسيدر.
     */
    public function update(User $user, Equipment $equipment): bool
    {
        return false;
    }

    /**
     * الحذف: أدمن بس (Gate::before).
     */
    public function delete(User $user, Equipment $equipment): bool
    {
        return false;
    }
}
