<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Favorite_Course extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($favorite_course) {
            if (empty($favorite_course->id)) { // Sử dụng id làm ULID
                $favorite_course->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'favorite_courses';
    protected $fillable = [
        'user_id',
        'course_id',
        'del_flag'
    ];

    //USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //COURSE
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
