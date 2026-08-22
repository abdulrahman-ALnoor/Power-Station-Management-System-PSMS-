<?php

namespace App\Http\Controllers\Api;
use Mpdf\Mpdf;
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
use App\Models\Customer;
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
    $query->where('status', $request->status);
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
    // مهم: نجمع من consumption_charges.remaining_amount (الرصيد الحالي الفعلي لكل دين)،
    // وليس من invoices.remaining_balance (التي تمثل لقطة تاريخية وقت كل دفعة بالذات).
    // جمع remaining_balance من الفواتير يضاعف المتأخرات وهمياً لأي عميل دفع على دفعات.
    $overdueAmount = ConsumptionCharge::sum('remaining_amount');

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
    $this->authorize('view', $invoice);

    $invoice->load([
        'customer',
        'accountant',
        'consumptionCharge.meter',
        'consumptionCharge.meterReading',
    ]);

    $html = view(
        'invoices.pdf',
        compact('invoice')
    )->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'default_font' => 'dejavusans',
        'default_font_size' => 12,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_left' => 15,
        'margin_right' => 15,
    ]);

    $mpdf->SetDirectionality('rtl');

    $mpdf->WriteHTML($html);

    $fileName = 'invoice-' . $invoice->invoice_number . '.pdf';

    return response(
        $mpdf->Output($fileName, 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
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
    $data = Invoice::query()
        ->selectRaw('MONTH(created_at) as month')
        ->selectRaw('SUM(outstanding_before_payment) as invoices_amount')
        ->selectRaw('SUM(paid_amount) as collections_amount')
        ->whereYear('created_at', now()->year)
        ->groupByRaw('MONTH(created_at)')
        ->orderByRaw('MONTH(created_at)')
        ->get();

    return $this->success(
        'تم جلب بيانات الفواتير والتحصيلات الشهرية بنجاح.',
        $data
    );
}

public function statusDistribution()
{
    $data = Invoice::query()
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->get()
        ->map(function ($item) {
            return [
                'name' => $item->status,
                'value' => (int) $item->total,
            ];
        });

    return $this->success(
        'تم جلب توزيع حالات الفواتير بنجاح.',
        $data
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

    /**
     * عرض الفواتير اللي أصدرها القارئ/المحاسب الحالي بنفسه (لصفحة "فواتيري" بواجهة القارئ)
     */
    public function readerIndex(Request $request)
    {
        $invoices = Invoice::with([
            'customer',
            'accountant',
            'consumptionCharge',
        ])
            ->where('accountant_id', $request->user()->id)
            ->latest('created_at')
            ->paginate($request->get('per_page', 10));

        return $this->success(
            'تم جلب الفواتير الصادرة منك بنجاح.',
            InvoiceResource::collection($invoices)
        );
    }


    public function revenueReport()
{
    $invoices = Invoice::query();

    $totalInvoices = (clone $invoices)->sum(
        'outstanding_before_payment'
    );

    $totalCollected = (clone $invoices)->sum(
        'paid_amount'
    );

    $totalRemaining = (clone $invoices)->sum(
        'remaining_balance'
    );

    $totalInvoicesCount = (clone $invoices)->count();

    return response()->json([
        'success' => true,
        'message' => 'تم جلب تقرير الإيرادات بنجاح',
        'data' => [
            'total_invoices' => (float) $totalInvoices,
            'total_collected' => (float) $totalCollected,
            'total_remaining' => (float) $totalRemaining,
            'total_invoices_count' => $totalInvoicesCount,
        ],
    ]);
}public function overdueInvoices(Request $request)
{
    $search = $request->input('search');

    $invoices = Invoice::with([
        'customer',
        'consumptionCharge',
    ])
        ->where('remaining_balance', '>', 0)

        ->when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {

                // البحث برقم الفاتورة
                $query->where(
                    'invoice_number',
                    'like',
                    "%{$search}%"
                )

                // أو البحث باسم العميل
                ->orWhereHas(
                    'customer',
                    function ($customerQuery) use ($search) {
                        $customerQuery->where(
                            'full_name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            });
        })

        ->latest()

        ->paginate(
            $request->input('per_page', 10)
        );

    return response()->json([
        'success' => true,
        'data' => $invoices,
    ]);
}


public function collectionsReport(Request $request)
{
   $query = Invoice::with([
    'customer:id,full_name',
])
        ->where('paid_amount', '>', 0);

    /*
    |--------------------------------------------------------------------------
    | البحث
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($query) use ($search) {
            $query->where(
                'invoice_number',
                'like',
                "%{$search}%"
            )
            ->orWhereHas(
                'customer',
                function ($customerQuery) use ($search) {
                    $customerQuery->where(
    'full_name',
    'like',
    "%{$search}%"
);
                }
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ترتيب التحصيلات من الأحدث إلى الأقدم
    |--------------------------------------------------------------------------
    */
    $collections = $query
        ->latest()
        ->paginate(
            $request->input(
                'per_page',
                20
            )
        );

    /*
    |--------------------------------------------------------------------------
    | الإحصائيات
    |--------------------------------------------------------------------------
    */
    $totalCollected = Invoice::where(
        'paid_amount',
        '>',
        0
    )->sum('paid_amount');

    $collectionsCount = Invoice::where(
        'paid_amount',
        '>',
        0
    )->count();

    $fullyPaidCount = Invoice::where(
        'status',
        'paid'
    )->count();

    $partiallyPaidCount = Invoice::where(
        'status',
        'partially_paid'
    )->count();

    return response()->json([
        'success' => true,

        'data' => [
            'collections' => $collections,

            'stats' => [
                'total_collected' => $totalCollected,

                'collections_count' => $collectionsCount,

                'fully_paid_count' => $fullyPaidCount,

                'partially_paid_count' => $partiallyPaidCount,
            ],
        ],
    ]);
}
public function accountStatement(Request $request)
{
    $request->validate([
        'customer_id' => [
            'required',
            'exists:customers,id',
        ],
    ]);

    $customerId = $request->input('customer_id');

    // العميل
    $customer = Customer::findOrFail($customerId);

    // فواتير العميل
    $invoicesQuery = Invoice::with([
        'customer:id,full_name',
        'consumptionCharge:id,total_amount',
    ])
        ->where('customer_id', $customerId)
        ->latest();

    $invoices = $invoicesQuery->paginate(
        $request->input('per_page', 20),
    );

    // إجمالي قيمة الفواتير
    $totalInvoices = Invoice::where(
        'customer_id',
        $customerId,
    )
        ->with('consumptionCharge')
        ->get()
        ->sum(function ($invoice) {
            return (float) (
                $invoice->consumptionCharge
                    ?->total_amount ?? 0
            );
        });

    // إجمالي المدفوع
    $totalPaid = Invoice::where(
        'customer_id',
        $customerId,
    )->sum('paid_amount');

    // إجمالي المتبقي
    $totalRemaining = Invoice::where(
        'customer_id',
        $customerId,
    )->sum('remaining_balance');

    return response()->json([
        'success' => true,

        'data' => [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
            ],

            'invoices' => $invoices,

            'summary' => [
                'total_invoices' => $totalInvoices,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,
            ],
        ],
    ]);
}
}
