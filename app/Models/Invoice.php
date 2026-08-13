<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    //

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'accountant_id',
        'consumption_charge_id',
        'outstanding_before_payment',
        'paid_amount',
        'remaining_balance',
        'status',
        'payment_notes',
        'pdf_path'
        
    ];

    protected function casts(): array
    {
        return [
            'outstanding_before_payment' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
        ];
    }

    /**
     * Boot function to generate invoice_number automatically upon creation.
     */
    protected static function booted(): void
    {
        static::creating(function ($invoice) {
            // يتم التوليد فقط إذا لم يُرسَل رقم فاتورة يدوي
            if (empty($invoice->invoice_number)) {
                $year = date('Y');
                
                // جلب آخر فاتورة مُنشأة لحساب الرقم التسلسلي القادم
                $latestInvoice = static::withTrashed()->latest('id')->first();
                $nextSequence = $latestInvoice ? ($latestInvoice->id + 1) : 1;

                // التنسيق النهائي: INV-2026-0001
                $invoice->invoice_number = 'INV-' . $year . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }


    // the customer() function defines a belongsTo relationship with the Customer model.
    // It allows you to access the customer associated with the invoice.
    // the relationship is one-to-many, where one customer can have many invoices.
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    // the accountant() function defines a belongsTo relationship with the User model.
    // It allows you to access the user who is the accountant for the invoice.
    // the relationship is one-to-many, where one user can be the accountant for many invoices
    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    // the consumptionCharge() function defines a hasOne relationship with the ConsumptionCharge model.
    // It allows you to access the consumption charge associated with the invoice.
    // the relationship is many-to-one, where each invoice is associated with one consumption charge.
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // the consumptionCharge() function defines a belongsTo relationship with the ConsumptionCharge model.
    // It allows you to access the consumption charge associated with the invoice.
    // the relationship is many-to-one, where each invoice is associated with one consumption charge.
    public function consumptionCharge()
    {
        return $this->belongsTo(ConsumptionCharge::class);
    }


}

