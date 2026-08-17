<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\ConsumptionCharge;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Http\Resources\InvoiceResource;
use App\Exports\InvoicesExport;
use Maatwebsite\Excel\Facades\Excel;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class InvoiceController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    // this function is used to get a list of invoices with optional search and filtering
    // it supports searching by invoice number or customer name, filtering by status, and filtering by year and month
    // it returns a paginated response with the invoices and their related customer, accountant, and consumption charge data
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query();

        // 1. البحث النصي (رقم الفاتورة أو اسم العميل)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($qCustomer) use ($search) {
                    $qCustomer->where('full_name', 'like', "%{$search}%");
                });
            });
        }

        // 2. الفلترة بحسب الحالة
        if ($request->filled('status')) {
            $query->whereHas('consumptionCharge', function ($qCharge) use ($request) {
                $qCharge->where('status', $request->status);
            });
        }

        // 3.  الفلترة بحسب الفترة (الشهر والسنة)
        $query
            ->when($request->year,function($q)use($request){

                $q->whereYear('created_at',$request->year);

            })
            ->when($request->month,function($q)use($request){

                $q->whereMonth('created_at',$request->month);

            });

        // جلب البيانات بالصفحات
        $invoices = $query->with([
            'customer',
            'accountant',
            'consumptionCharge'
        ])
        ->latest('created_at')
        ->paginate($request->get('per_page', 10));

        return $this->success(
            'تم جلب الفواتير بنجاح.',
            InvoiceResource::collection($invoices)
            );
    }

    // this function is used to get statistics about invoices
    // it calculates total revenue, total invoices, paid invoices count, partially paid invoices count, overdue amount, and this month's collections
    // it returns a response with the calculated statistics
    public function stats()
    {
    // 1. إجمالي الإيرادات المحصلة بالفعل
    $totalRevenue = Invoice::sum('paid_amount');

    // 2. إجمالي عدد الفواتير
    $totalInvoices = Invoice::count();

    // 3. عدد الفواتير المدفوعة بالكامل
    $paidInvoicesCount = Invoice::where('status', 'paid')->count();

    // 4. عدد الفواتير غير المدفوعة أو المدفوعة جزئياً
    $partiallyPaidInvoicesCount = Invoice::where('status', 'partially_paid')->count();

    // 5. المبالغ المتبقية غير المحصلة (المتأخرات/المتبقي)
    $overdueAmount = Invoice::sum('remaining_balance');

    // 6. تحصيلات هذا الشهر الفعلية
    $thisMonthCollect = Invoice::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('paid_amount');

    return $this->success(
            'تم جلب إحصائيات الفواتير بنجاح.'   ,
    [
        'total_revenue'          => $totalRevenue,
        'total_invoices'         => $totalInvoices,
        'paid_invoices_count'    => $paidInvoicesCount,
        'partially_paid_count'   => $partiallyPaidInvoicesCount,
        'overdue_amount'         => $overdueAmount,
        'this_month_collect'     => $thisMonthCollect,
    ] );

}

    /**
     * Store a newly created resource in storage.
     */

    // this function is used to create a new invoice and update the corresponding consumption charge
    // it first checks if the paid amount does not exceed the remaining amount of the consumption charge
    // it then calculates the new remaining balance and paid amount, and determines the status of the invoice and consumption charge
    // it uses a transaction to ensure that both the invoice creation and consumption charge update are successful
    // it returns a response with the created invoice data
    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        // 1. جلب الدين الحقيقي من قاعدة البيانات
        $charge = ConsumptionCharge::findOrFail(
            $request->consumption_charge_id
        );
        // 2. التحقق من أن المبلغ المدفوع لا يتجاوز المتبقي
        if ($request->paid_amount > $charge->remaining_amount) {
            return $this->error(
                'المبلغ المدفوع أكبر من المبلغ المتبقي.',
                422
            );
        }


        // 3. حساب القيم الجديدة
        $newRemainingBalance = $charge->remaining_amount - $request->paid_amount;
        $newPaidAmount = $charge->paid_amount + $request->paid_amount;
        // 4. تحديد حالة الدين تلقائياً
        $status = $newRemainingBalance == 0 ? 'paid': 'partially_paid';

        // 5. تنفيذ إنشاء الفاتورة وتحديث الدين داخل Transaction
        try {

            $invoice = DB::transaction(function () use (
                $charge,
                $request,
                $newRemainingBalance,
                $newPaidAmount,
                $status
            ) {


                // إنشاء الفاتورة
                $invoice = Invoice::create([
                    'consumption_charge_id' => $charge->id,
                    'customer_id' => $charge->customer_id,
                    'accountant_id' => Auth::id(),
                    'outstanding_before_payment' => $charge->remaining_amount,
                    'paid_amount' => $request->paid_amount,
                    'remaining_balance' => $newRemainingBalance,
                    'status' => $status,
                    'payment_notes' => $request->payment_notes,
                ]);
                // تحديث بيانات الدين
                $charge->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingBalance,
                    'status' => $status,
                ]);
                return $invoice;
            });



            // 6. إعادة البيانات للـ Frontend
            return $this->success(
                'تم إنشاء الفاتورة بنجاح.',
                new InvoiceResource(
                    $invoice->load([
                        'customer',
                        'accountant',
                        'consumptionCharge'
                    ])
                ),

                201
            );
        } catch (\Exception $e) {
            return $this->error(
                'حدث خطأ أثناء إنشاء الفاتورة.',
                500
            );
        }
    }



    /**
     * Display the specified resource.
     */

    // this function is used to get the details of a specific invoice
    // it loads the related customer, accountant, and consumption charge data
    // it returns a response with the invoice data
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'customer',
            'accountant',
            'consumptionCharge',
        ]);

        return $this->success(
            'تم جلب الفاتورة بنجاح.',
            new InvoiceResource($invoice)
        );
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update($request->validated());

        $invoice->load([
            'customer',
            'accountant',
            'consumptionCharge',
        ]);

        return $this->success(
            'تم تحديث الفاتورة بنجاح.',
            new InvoiceResource($invoice->fresh())
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return $this->success(
            'تم حذف الفاتورة بنجاح.',
            null
        );
    }
    public function exportPdf(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'accountant',
            'consumptionCharge.meter',
            'consumptionCharge.meterReading',
        ]);

        $pdf = Pdf::loadView(
            'invoices.pdf',
            compact('invoice')
        );

        $fileName = $invoice->invoice_number . '.pdf';

        $path = 'invoices/' . $fileName;

        Storage::disk('public')->put(
            $path,
            $pdf->output()
        );

        $invoice->update([
            'pdf_path' => $path,
        ]);

        return $this->success(
            'تم تصدير الفاتورة إلى PDF بنجاح.',
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'pdf_path' => $path,
                'pdf_url' => Storage::disk('public')->url($path),
            ]
            
        );
    }
    
    public function customerInvoices($customerId)
        {
            $invoices = Invoice::with([
                'customer',
                'accountant',
                'consumptionCharge',
            ])
            ->where('customer_id', $customerId)
            ->latest('created_at')
            ->paginate(10);

            return $this->success(
                'تم جلب فواتير العميل بنجاح.',
                InvoiceResource::collection($invoices)
            );
        }
    public function monthlyRevenue()
    {
        $revenue = Invoice::query()
            ->selectRaw('MONTH(created_at) as month')
            ->selectRaw('SUM(paid_amount) as total_revenue')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();

        return $this->success(
            'تم جلب الإيرادات الشهرية بنجاح.',
            $revenue
        );
    }

    public function latestPayments()
    {
        $payments = Invoice::with([
            'customer:id,full_name',
            'accountant:id,name',
        ])
        ->latest('created_at')
        ->take(10)
        ->get();

        return $this->success(
            'تم جلب آخر التحصيلات بنجاح.',
            InvoiceResource::collection($payments)
        );
    }

    public function exportExcel()
    {
        return Excel::download(
            new InvoicesExport,
            'invoices.xlsx'
        );
    }
}


