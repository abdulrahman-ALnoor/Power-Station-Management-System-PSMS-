<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Invoice::with([
            'customer',
            'consumptionCharge',
        ])
        ->latest()
        ->get()
        ->map(function ($invoice) {
            return [
                $invoice->invoice_number,
                $invoice->customer?->full_name,
                $invoice->consumptionCharge?->total_amount,
                $invoice->outstanding_before_payment,
                $invoice->paid_amount,
                $invoice->remaining_balance,
                $invoice->status,
                $invoice->payment_notes,
                $invoice->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer',
            'Total Amount',
            'Outstanding Before Payment',
            'Paid Amount',
            'Remaining Balance',
            'Status',
            'Payment Notes',
            'Created At',
        ];
    }
}