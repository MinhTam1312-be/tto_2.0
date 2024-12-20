<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Route extends Model
{
    // Thiết lập kiểu của khóa chính là chuỗi (string)
    protected $keyType = 'string';

    // Vô hiệu hóa tự động tăng (auto-incrementing)
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        // Xử lý khi tạo mới
        static::creating(function ($route) {
            // Tạo ULID nếu chưa có
            if (empty($route->id)) {
                $route->id = (string) Str::ulid();
            }

            // Tạo slug khi tạo route mới
            if (empty($route->slug_route) && !empty($route->name_route)) {
                $route->slug_route = self::generateUniqueSlug($route->name_route);
            }
        });

        // Xử lý khi cập nhật
        static::updating(function ($route) {
            if ($route->isDirty('name_route')) {
                $route->slug_route = self::generateUniqueSlug($route->name_route, $route->id);
            }
        });
    }

    /**
     * Hàm tạo slug duy nhất cho route
     *
     * @param string $name_route
     * @param string|null $ignoreId
     * @return string
     */
    public static function generateUniqueSlug($name_route, $ignoreId = null)
    {
        $slug = Str::slug($name_route, '-');
        $originalSlug = $slug;
        $count = 1;

        // Kiểm tra trùng lặp slug
        while (
            self::where('slug_route', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }


    use HasFactory;
    protected $table = 'routes';
    protected $fillable = [
        'name_route',
        'slug_route',
        'img_route',
        'discription_route',
        'del_flag'
    ];

    //MODULE
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    //COURSE -RECOMMEND
    public function coursesRecommend()
    {
        return $this->belongsToMany(Course::class, 'course_route'); // Chỉ định bảng trung gian
    }


    //ENROLLMENT
    public function enrollments()
    {
        return $this->hasManyThrough(Module::class, Enrollment::class);
    }
}
