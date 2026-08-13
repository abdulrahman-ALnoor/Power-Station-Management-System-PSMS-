<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>
        فاتورة {{ $invoice->invoice_number }}
    </title>

    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 13px;
            color: #333;
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
            margin-top: 20px;
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
            text-align: left;
            font-weight: bold;
        }

        .status {
            font-weight: bold;
            text-align: center;
        }

        .notes {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

        .signature-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
        }

        .signature-table td {
            width: 50%;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">

        <table class="header-table">

            <tr>

                <td>
                    <div class="company-name">
                        نظام إدارة محطة الكهرباء
                    </div>

                    <div>
                        فاتورة خدمات الكهرباء
                    </div>
                </td>

                <td>
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
                {{ $invoice->customer->full_name }}
            </td>
        </tr>

        <tr>
            <td class="label">
                رقم العميل
            </td>

            <td>
                {{ $invoice->customer->customer_number }}
            </td>
        </tr>

        @if($invoice->customer->phone)

        <tr>
            <td class="label">
                رقم الهاتف
            </td>

            <td>
                {{ $invoice->customer->phone }}
            </td>
        </tr>

        @endif

        @if($invoice->customer->address_description)

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


    {{-- بيانات العداد --}}
    <div class="section-title">
        بيانات العداد والاستهلاك
    </div>

    <table class="details-table">

        <thead>

            <tr>
                <th>
                    رقم العداد
                </th>

                <th>
                    الاستهلاك
                </th>

                <th>
                    قيمة الاستهلاك
                </th>
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
                    {{ number_format($invoice->consumptionCharge->total_amount, 2) }}
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
                {{ number_format($invoice->outstanding_before_payment, 2) }}
                ريال
            </td>

        </tr>

        <tr>

            <td class="payment-label">
                المبلغ المدفوع
            </td>

            <td class="amount">
                {{ number_format($invoice->paid_amount, 2) }}
                ريال
            </td>

        </tr>

        <tr>

            <td class="payment-label">
                المبلغ المتبقي
            </td>

            <td class="amount">
                {{ number_format($invoice->remaining_balance, 2) }}
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
                @else
                    مدفوعة جزئياً
                @endif

            </td>

        </tr>

    </table>


    {{-- ملاحظات الدفع --}}
    @if($invoice->payment_notes)

        <div class="section-title">
            ملاحظات الدفع
        </div>

        <div class="notes">
            {{ $invoice->payment_notes }}
        </div>

    @endif


    {{-- المحاسب --}}
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