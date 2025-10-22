<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_budget',
        'seoer',
        'spent_amount',
        'remaining_amount',
        'description',
        'period_start',
        'period_end',
    ];

    protected function casts(): array
    {
        return [
            'total_budget' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    /**
     * Calculate remaining amount automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($budget) {
            // Tính toán spent_amount từ tổng các service proposals đã hoàn thành
            $completedProposalsAmount = $budget->serviceProposals()
                ->where('status', 'completed')
                ->sum('amount');
            
            $budget->spent_amount = $completedProposalsAmount;
            $budget->remaining_amount = $budget->total_budget - $budget->spent_amount;
        });
    }

    /**
     * Get formatted total budget
     */
    public function getFormattedTotalBudgetAttribute(): string
    {
        return number_format($this->total_budget, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get formatted spent amount
     */
    public function getFormattedSpentAmountAttribute(): string
    {
        return number_format($this->spent_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get formatted remaining amount
     */
    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get spending percentage
     */
    public function getSpendingPercentageAttribute(): float
    {
        if ($this->total_budget == 0) return 0;
        return ($this->spent_amount / $this->total_budget) * 100;
    }

    /**
     * Get service proposals for this budget
     */
    public function serviceProposals()
    {
        return $this->hasMany(ServiceProposal::class);
    }

    /**
     * Recalculate spent amount from completed service proposals
     */
    public function recalculateSpentAmount(): void
    {
        $completedAmount = $this->serviceProposals()
            ->where('status', 'completed')
            ->sum('amount');
        
        $this->spent_amount = $completedAmount;
        $this->remaining_amount = $this->total_budget - $this->spent_amount;
        $this->save();
    }
}
