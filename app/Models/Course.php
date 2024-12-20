<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Course extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Xử lý khi tạo mới
        static::creating(function ($course) {
            // Tạo ULID nếu chưa có
            if (empty($course->id)) {
                $course->id = (string) Str::ulid();
            }

            // Tạo slug khi tạo khóa học mới
            if (empty($course->slug_course) && !empty($course->name_course)) {
                $course->slug_course = self::generateUniqueSlug($course->name_course);
            }
        });

        // Xử lý khi cập nhật
        static::updating(function ($course) {
            if ($course->isDirty('name_course')) {
                $course->slug_course = self::generateUniqueSlug($course->name_course, $course->id);
            }
        });
    }

    /**
     * Hàm tạo slug duy nhất
     *
     * @param string $name_course
     * @param string|null $ignoreId
     * @return string
     */
    public static function generateUniqueSlug($name_course, $ignoreId = null)
    {
        $slug = Str::slug($name_course, '-');
        $originalSlug = $slug;
        $count = 1;

        // Kiểm tra trùng lặp slug
        while (
            self::where('slug_course', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    use HasFactory;
    protected $table = 'courses';
    protected $fillable = [
        'name_course',
        'slug_course',
        'img_course',
        'price_course',
        'discount_price_course',
        'views_course',
        'rating_course',
        'discription_course',
        'status_course',
        'tax_rate',
        'del_flag',
        'user_id',
    ];



    //MODULE
    public function modules()
    {
        return $this->hasMany(Module::class, 'course_id', 'id');
    }


    public function modulesRecommend()
    {
        return $this->hasMany(Module::class);
    }

    //FAVORITE_COURSE
    public function favorite_courses()
    {
        return $this->hasMany(Favorite_Course::class);
    }

    //FAQ_COURSE
    public function faq_courses()
    {
        return $this->hasMany(FAQ_Course::class);
    }

    //CHAPTER
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id', 'id');
    }

    //DOCUMENT
    public function documents()
    {
        return $this->hasManyThrough(Document::class, Chapter::class, 'course_id', 'chapter_id', 'id', 'id');
    }


    //DOCUMENT
    public function documentsProgress()
    {
        return $this->hasManyThrough(Document::class, Chapter::class, 'course_id', 'chapter_id', 'id', 'id');
    }


    //USER - INSTRUCTORS
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users() // đăng kí khóa học
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'course_id', 'user_id', 'id', 'id');
    }
}
