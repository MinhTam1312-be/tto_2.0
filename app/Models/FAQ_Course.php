<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class FAQ_Course extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($faq_course) {
            if (empty($faq_course->id)) { // Sử dụng id làm ULID
                $faq_course->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'faqs_course';
    protected $fillable = [
        'question_faq',
        'answer_faq',
        'del_flag',
        'course_id',
    ];

    //COURSE
    public function course()
{
    return $this->belongsTo(Course::class, 'course_id', 'id');
}
}
