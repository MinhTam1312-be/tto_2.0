<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reminder extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($reminder) {
            if (empty($reminder->id)) { // Sử dụng id làm ULID
                $reminder->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'reminders';
    protected $fillable = [
        'day_of_week',
        'time',
        'status',
        'del_flag',
        'enrollment_id',
    ];

    //ENROLLMENT
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id', 'id');
    }
}
