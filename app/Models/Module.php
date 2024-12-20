<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Module extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($module) {
            if (empty($module->id)) { // Sử dụng id làm ULID
                $module->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'modules';
    protected $fillable = [
        'route_id',
        'course_id',
        'del_flag'
    ];

    //ROUTE
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    //COURSE
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    //COURSE
    public function courseRecommend()
    {
        return $this->belongsTo(Course::class);
    }

    // Module.php
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'module_id', 'id');
    }
}
