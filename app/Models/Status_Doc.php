<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Status_Doc extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($status_doc) {
            if (empty($status_doc->id)) { // Sử dụng id làm ULID
                $status_doc->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'status_docs';
    protected $fillable = [
        'status_doc',
        'cache_time_video',
        'document_id',
        'enrollment_id',
        'del_flag'
    ];

    //DOCUMENT
    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    //ENROLLMENT
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
