<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>
        فاتورة {{ $invoice->invoice_number }}
    </title>

    <style>
        @page {
            margin: 25px;
        }

        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            direction: rtl;
            text-align: right;
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.8;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
        }

        .invoice-title {
            text-align: left;
            font-size: 20px;
            font-weight: bold;
        }

        .invoice-number {
            text-align: left;
            margin-top: 5px;
            font-size: 13px;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            background: #eeeeee;
            padding: 8px;
            margin-top: 18px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 7px;
            border: 1px solid #ddd;
        }

        .label {
            font-weight: bold;
            width: 30%;
            background: #f7f7f7;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #ccc;
            padding: 9px;
            text-align: center;
        }

        .details-table th {
            background: #eeeeee;
            font-weight: bold;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .payment-table td {
            border: 1px solid #ddd;
            padding: 9px;
        }

        .payment-label {
            font-weight: bold;
            background: #f7f7f7;
            width: 60%;
        }

        .amount {
            direction: ltr;
            text-align: left;
            font-weight: bold;
        }

        .status {
            font-weight: bold;
            text-align: center;
        }

        .notes {
            border: 1px solid #ddd;
            padding: 12px;
            margin-top: 10px;
            min-height: 35px;
            background: #fafafa;
            white-space: normal;
        }

        .signature-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

.header {
    width: 100%;
    border-bottom: 2px solid #222;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table td {
    vertical-align: middle;
}

.company-cell {
    width: 40%;
    text-align: right;
}

.logo-cell {
    width: 20%;
    text-align: center;
}

.invoice-cell {
    width: 40%;
    text-align: left;
}

.station-logo {
    display: block;
    width: 75px;
    height: auto;
    margin: 0 auto;
}

.company-name {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
}

.company-subtitle {
    font-size: 13px;
    margin-bottom: 3px;
}

.company-info {
    font-size: 11px;
}

.invoice-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 6px;
}

.invoice-number {
    font-size: 11px;
    margin-top: 4px;
}
    </style>
</head>

<body>


{{-- Header --}}
<div class="header">

    <table class="header-table">

        <tr>

            {{-- معلومات المحطة --}}
            <td class="company-cell">

                <div class="company-name">
                    نظام إدارة محطة الكهرباء
                </div>

                <div class="company-subtitle">
                    فاتورة خدمات الكهرباء
                </div>

                <div class="company-info">
                    صنعاء - الجمهورية اليمنية
                </div>

            </td>


            {{-- شعار المحطة في الوسط --}}
            <td class="logo-cell">

                <img
                    src="{{ public_path('images/station-logo.png') }}"
                    class="station-logo"
                    alt="شعار المحطة"
                >

            </td>


            {{-- معلومات الفاتورة --}}
            <td class="invoice-cell">

                <div class="invoice-title">
                    فاتورة
                </div>

                <div class="invoice-number">
                    رقم الفاتورة:
                    {{ $invoice->invoice_number }}
                </div>

                <div class="invoice-number">
                    التاريخ:
                    {{ $invoice->created_at->format('Y-m-d') }}
                </div>

            </td>

        </tr>

    </table>

</div>


    {{-- بيانات العميل --}}
    <div class="section-title">
        بيانات العميل
    </div>

    <table class="info-table">

        <tr>
            <td class="label">
                اسم العميل
            </td>

            <td>
                {{ $invoice->customer->full_name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">
                رقم العميل
            </td>

            <td>
                {{ $invoice->customer->customer_number ?? '-' }}
            </td>
        </tr>

        @if($invoice->customer->phone ?? false)
            <tr>
                <td class="label">
                    رقم الهاتف
                </td>

                <td>
                    {{ $invoice->customer->phone }}
                </td>
            </tr>
        @endif

        @if($invoice->customer->address_description ?? false)
            <tr>
                <td class="label">
                    العنوان
                </td>

                <td>
                    {{ $invoice->customer->address_description }}
                </td>
            </tr>
        @endif

    </table>


    {{-- بيانات العداد والاستهلاك --}}
    <div class="section-title">
        بيانات العداد والاستهلاك
    </div>

    <table class="details-table">

        <thead>
            <tr>
                <th>رقم العداد</th>
                <th>الاستهلاك</th>
                <th>قيمة الاستهلاك</th>
            </tr>
        </thead>

        <tbody>
            <tr>

                <td>
                    {{ $invoice->consumptionCharge->meter->meter_number ?? '-' }}
                </td>

                <td>
                    {{ $invoice->consumptionCharge->meterReading->consumption ?? 0 }}
                    KWh
                </td>

                <td>
                    {{ number_format($invoice->consumptionCharge->total_amount ?? 0, 2) }}
                    ريال
                </td>

            </tr>
        </tbody>

    </table>


    {{-- تفاصيل الدفع --}}
    <div class="section-title">
        تفاصيل الدفع
    </div>

    <table class="payment-table">

        <tr>
            <td class="payment-label">
                المبلغ المستحق قبل الدفع
            </td>

            <td class="amount">
                {{ number_format($invoice->outstanding_before_payment ?? 0, 2) }}
                ريال
            </td>
        </tr>

        <tr>
            <td class="payment-label">
                المبلغ المدفوع
            </td>

            <td class="amount">
                {{ number_format($invoice->paid_amount ?? 0, 2) }}
                ريال
            </td>
        </tr>

        <tr>
            <td class="payment-label">
                المبلغ المتبقي
            </td>

            <td class="amount">
                {{ number_format($invoice->remaining_balance ?? 0, 2) }}
                ريال
            </td>
        </tr>

        <tr>
            <td class="payment-label">
                حالة الفاتورة
            </td>

            <td class="status">

                @if($invoice->status === 'paid')
                    مدفوعة بالكامل
                @elseif($invoice->status === 'partially_paid')
                    مدفوعة جزئياً
                @else
                    غير محددة
                @endif

            </td>
        </tr>

    </table>


    {{-- ملاحظات / إشعار الدفع --}}
    <div class="section-title">
        ملاحظات وإشعار الدفع
    </div>

    <div class="notes">
        @if(!empty($invoice->payment_notes))
            {{ $invoice->payment_notes }}
        @else
            لا توجد ملاحظات أو إشعار إضافي لهذه الفاتورة.
        @endif
    </div>


    {{-- التوقيعات --}}
    <table class="signature-table">

        <tr>

            <td>
                المحاسب
                <br>
                <br>

                {{ $invoice->accountant->name ?? '-' }}
            </td>

            <td>
                توقيع العميل
                <br>
                <br>

                ____________________
            </td>

        </tr>

    </table>


    {{-- Footer --}}
    <div class="footer">

        شكراً لتعاملكم معنا

        <br>

        هذه الفاتورة صادرة إلكترونياً من نظام إدارة محطة الكهرباء.

    </div>

</body>

</html>
