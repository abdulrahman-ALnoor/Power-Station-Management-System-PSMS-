<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission; // موديل الصلاحيات الجاهز من مكتبة Spatie
use Spatie\Permission\Models\Role;       // موديل الأدوار الجاهز من مكتبة Spatie

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // القسم 1: توليد صلاحيات CRUD تلقائياً لكل مورد
        // ==========================================
        // بدل ما نكتب 36 سطر يدوي (9 موارد × 4 عمليات)،
        // نحط أسماء الموارد بمصفوفة، والكود يولّد الأسماء تلقائياً
        $resources = [
            'customers', 'meters', 'meter-readings', 'invoices',
            'consumption-charges', 'service-requests', 'equipment',
            'users', 'company-profiles', 'notifications',
        ];
        $actions = ['view', 'create', 'update', 'delete']; // عرض / إضافة / تعديل / حذف

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                // firstOrCreate: لو الصلاحية موجودة أصلاً بقاعدة البيانات، يتجاهلها
                // ولو مو موجودة، ينشئها. هذا يخلي تشغيل السيدر أكثر من مرة آمن 100%
                // النتيجة: customers.view, customers.create, customers.update, customers.delete
                //          meters.view, meters.create ... وهكذا لكل الموارد
                Permission::firstOrCreate(['name' => "{$resource}.{$action}"]);
            }
        }

        // ==========================================
        // القسم 2: صلاحيات خاصة (أفعال مش نمط CRUD عادي)
        // ==========================================
        // هذي أفعال مو "إضافة/تعديل/حذف/عرض" عادية، فما تدخل باللوب فوق
        // كل وحدة استخرجناها من متطلبات مستند الصلاحيات اللي رفعته
        $customPermissions = [
            'notifications.send',              // إرسال إشعار فعلي للمستخدم (مختلف عن مجرد إنشاء سجل إشعار)
            'reports.customer-financial',       // توليد تقرير مالي PDF لعميل معيّن
            'reports.customer-readings',        // توليد تقرير قراءات PDF لعميل معيّن
            'service-requests.assign',          // توجيه طلب صيانة لمهندس معيّن (خاص بالأدمن)
            'service-requests.change-status',   // تغيير حالة الطلب (قيد المعالجة/منجز/مرفوض...)
            'equipment.describe',               // تحديث محدود: إضافة وصف بس، مو كل بيانات المعدة
        ];
        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==========================================
        // القسم 3: دور Admin — يأخذ كل الصلاحيات بدون استثناء
        // ==========================================
        // firstOrCreate: يجيب الدور لو موجود، أو ينشئه لو مو موجود
        $admin = Role::firstOrCreate(['name' => 'admin']);
        // syncPermissions: يمسح أي صلاحيات قديمة مربوطة بهذا الدور، ويحط بدالها القائمة الجديدة بالضبط
        // هنا نعطيه Permission::all() يعني حرفياً كل صلاحية موجودة بالجدول، من غير ما نعدد وحدة وحدة
        $admin->syncPermissions(Permission::all());

        // ==========================================
        // القسم 4: دور Engineer (مهندس الكهرباء)
        // ==========================================
        // حسب المستند: يشوف/يضيف طلبات صيانة، يغيّر حالة الطلب الخاص فيه، يوصف المعدات
        // ملاحظة: "الخاص فيه بس" هذا قيد على مستوى السجل، مو صلاحية عامة —
        // هذا الجزء بيتحقق لاحقاً بـ Policy منفصلة، مو من هالسيدر
        $engineer = Role::firstOrCreate(['name' => 'engineer']);
        $engineer->syncPermissions([
            'service-requests.view',
            'service-requests.create',
            'service-requests.change-status',
            'equipment.view',
            'equipment.describe',
        ]);

        // ==========================================
        // القسم 5: دور Reader (قارئ العدادات)
        // ==========================================
        // حسب المستند: القراءات كاملة CRUD، طلبات صيانة (CRUD كامل على طلباته هو بس،
        // بدون توجيه لمهندس ولا تعرّض لموافقة الأدمن — القيد على مستوى السجل يتحقق بـ ServiceRequestPolicy)
        $reader = Role::firstOrCreate(['name' => 'reader']);
        $reader->syncPermissions([
            'meter-readings.view',
            'meter-readings.create',
            'meter-readings.update',
            'meter-readings.delete',
            'service-requests.view',
            'service-requests.create',
            'service-requests.update',
            'service-requests.delete',
            'equipment.view',
            'equipment.describe',
        ]);

        // ==========================================
        // القسم 6: دور Accountant (المحاسب)
        // ==========================================
        // حسب المستند: الفواتير والتحصيل كاملة، التقارير المالية،
        // طلبات صيانة (CRUD كامل، بس لا يقدر يعدّل/يحذف إلا الطلب اللي أنشأه هو —
        // القيد على مستوى السجل يتحقق بـ ServiceRequestPolicy)
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions([
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'reports.customer-financial',
            'reports.customer-readings',
            'service-requests.view',
            'service-requests.create',
            'service-requests.update',
            'service-requests.delete',
        ]);
    }
}
