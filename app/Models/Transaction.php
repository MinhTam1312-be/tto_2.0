<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($transaction) {
            if (empty($transaction->id)) { // Sử dụng id làm ULID
                $transaction->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'transactions';
    protected $fillable = [
        'amount',
        'payment_method',
        'status',
        'payment_discription',
        'enrollment_id',
        'del_flag'
    ];
    
    //ENROLLMENT
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
