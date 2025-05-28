<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuoteItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quote_id',
        'item',
        'quantity',
        'price',
        'approved',
        'comment',
        'reason',
        'reason_details'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'approved' => 'boolean'
    ];

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Get the quote that the item belongs to
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function getQuoteHistoryAttribute()
    {
        return self::query()
            ->select([
                'quote_items.quantity',
                'quote_items.price',
                'quotes.created_at',
                'quotes.id',
                'quotes.reference',
                'quotes.status',
                DB::raw('CAST(quote_items.quantity * quote_items.price AS DECIMAL(10,2)) as amount'),
                DB::raw('CASE 
                    WHEN quote_items.approved = 1 THEN "success"
                    WHEN quote_items.approved = 0 AND quotes.status = "pending" THEN "warning"
                    ELSE "danger"
                END as status_color'),
                DB::raw('CASE 
                    WHEN quote_items.approved = 1 THEN "Approved"
                    WHEN quote_items.approved = 0 AND quotes.status = "pending" THEN "Pending"
                    ELSE "Rejected"
                END as status')
            ])
            ->join('quotes', 'quotes.id', '=', 'quote_items.quote_id')
            ->where('quote_items.item', $this->item)
            ->whereNotNull('quotes.created_at')
            ->orderBy('quotes.created_at', 'desc')
            ->get();
    }
}
