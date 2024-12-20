<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Activity_History extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($activity_history) {
            if (empty($activity_history->id)) { // Sử dụng id làm ULID
                $activity_history->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;

    protected $table = 'activities_history';

    protected $fillable = [
        'id',
        'name_activity',
        'discription_activity',
        'status_activity',
        'user_id'
    ];

    //USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
