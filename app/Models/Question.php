<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question extends Model
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
        static::creating(function ($question) {
            if (empty($question->id)) { // Sử dụng id làm ULID
                $question->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'questions';
    protected $fillable = [
        'content_question',
        'answer_question',
        'type_question',
        'del_flag',
    ];

    //DOCUMENT
    public function document()
    {
        return $this->belongsTo(Document::class, 'id', 'id');
    }
}
