<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Enrollment extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($enrollment) {
            if (empty($enrollment->id)) { // Sử dụng id làm ULID
                $enrollment->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'enrollments';
    protected $fillable = [
        'rating_course',
        'feedback_text',
        'status_course',
        'certificate_course',
        'enroll',
        'del_flag',
        'course_id',
        'user_id',
    ];

    protected $attributes = [
        'status_course' => 'in_progress',
    ];
    //TRANSACTION
    public function transactions()
    {
        return $this->hasOne(Transaction::class, 'enrollment_id', 'id');
    }

    //STATUS_VIDEO
    public function status_docs()
    {
        return $this->hasMany(Status_Doc::class, 'enrollment_id', 'id');
    }

    //COURSE
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }


    //USER
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function courses() // course đăng kí
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
