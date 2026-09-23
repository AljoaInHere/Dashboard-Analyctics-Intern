<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $tanggal
 * @property string $operator
 * @property int $total_dikirim
 * @property int $total_sukses
 * @property int $total_gagal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float $delivery_rate
 */
class SmsTraffic extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sms_traffics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal',
        'operator',
        'total_dikirim',
        'total_sukses',
        'total_gagal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_dikirim' => 'integer',
            'total_sukses' => 'integer',
            'total_gagal' => 'integer',
        ];
    }

    /**
     * Accessor for delivery rate percentage.
     */
    protected function deliveryRate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_dikirim > 0
                ? round(($this->total_sukses / $this->total_dikirim) * 100, 2)
                : 0.0
        );
    }

    /**
     * Scope a query to filter by specific operator.
     */
    public function scopeForOperator(Builder $query, ?string $operator): Builder
    {
        if ($operator && $operator !== 'all') {
            return $query->where('operator', $operator);
        }

        return $query;
    }

    /**
     * Scope a query to filter between date range.
     */
    public function scopeBetweenDates(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        if ($startDate) {
            return $query->where('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            return $query->where('tanggal', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query for a given month and year.
     */
    public function scopeForMonth(Builder $query, ?int $month = null, ?int $year = null): Builder
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }

    /**
     * Scope a query for recent days.
     */
    public function scopeRecentDays(Builder $query, int $days = 7): Builder
    {
        return $query->where('tanggal', '>=', Carbon::now()->subDays($days));
    }
}
