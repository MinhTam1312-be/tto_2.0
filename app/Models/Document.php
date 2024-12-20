<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Document extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Tạo ULID trước khi tạo bản ghi mới
        static::creating(function ($document) {
            if (empty($document->id)) { // Sử dụng id làm ULID
                $document->id = (string) Str::ulid();
            }
        });
    }
    use HasFactory;
    protected $table = 'documents';
    protected $fillable = [
        'name_document',
        'discription_document',
        'serial_document',
        'file',
        'url_video',
        'type_document',
        'del_flag',
        'chapter_id',

    ];

    //COMMENT_DOCUMENT
    public function comments_document()
    {
        return $this->hasMany(Comment_Document::class);
    }

    //STATUS_DOC
    public function status_docs()
    {
        return $this->hasOne(Status_Doc::class, 'document_id', 'id');
    }

    //NOTE
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    //CHAPTER
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    //QUESTION
    public function question()
    {
        return $this->hasOne(Question::class, 'id', 'id');
    }

    //CODE
    public function code()
    {
        return $this->hasOne(Question::class, 'id', 'id');
    }

}
