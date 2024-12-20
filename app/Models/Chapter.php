<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Chapter extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($chapter) {
            if (empty($chapter->id)) { // Sử dụng id làm ULID
                $chapter->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'chapters';
    protected $fillable = [
        'name_chapter',
        'serial_chapter',
        'del_flag',
        'course_id',
    ];

    //DOCUMENT
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Trong model Chapter
    public function documentsProgress()
    {
        return $this->hasMany(Document::class, 'chapter_id', 'id');
    }

    //COURSE
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
