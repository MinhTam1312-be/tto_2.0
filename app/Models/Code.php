<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Code extends Model
{
    protected $primaryKey = 'id'; // id là khóa chính
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($code) {
            if (empty($code->id)) { // Sử dụng id làm ULID
                $code->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'codes';
    protected $fillable = [
        'id',
        'question_code',
        'answer_code',
        'tutorial_code',
        'del_flag',
    ];

    //DOCUMENT
    public function document()
    {
        return $this->belongsTo(Document::class, 'id', 'id');
    }
}
