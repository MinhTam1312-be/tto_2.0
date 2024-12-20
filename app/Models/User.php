<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'id'; // Sử dụng cột id làm khóa chính
    protected $keyType = 'string'; // Đặt kiểu khóa chính là chuỗi

    public $incrementing = false; // Vô hiệu hóa tự động tăng

    protected $fillable = [
        'discription_user',
        'password',
        'fullname',
        'age',
        'email',
        'avatar',
        'phonenumber',
        'provider_id',
        'role',
        'del_flag'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Phương thức boot để tạo ULID và avatar mặc định
    protected static function boot()
    {
        parent::boot();

        // Tạo ULID khi tạo mới
        static::creating(function ($user) {
            if (empty($user->id)) { // Sử dụng id làm ULID
                $user->id = (string) Str::ulid();
            }

            // Đặt avatar mặc định nếu chưa có
            if (empty($user->avatar)) {
                $user->avatar = 'https://be-datn-production-19f3.up.railway.app/api/client/image/userDefault';
            }
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Định nghĩa các quan hệ

    //POST
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    //COMMENT_POST
    public function comments_post()
    {
        return $this->hasMany(Comment_Post::class);
    }

    //COMMENT_DOCUMENT
    public function comments_document()
    {
        return $this->hasMany(Comment_Document::class);
    }

    //COURSE - KHÓA HỌC CỦA GIẢNG VIÊN
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    //ENROLLMENT-COURSE
    public function enrolledCourses()
    {
        return $this->hasManyThrough(Course::class, Enrollment::class, 'user_id', 'course_id', 'id', 'id');
    }

    //ENROLLMENT
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    //FAVORITE_COURSE
    public function favorite_courses()
    {
        return $this->hasMany(Favorite_Course::class);
    }

    //NOTE
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    //ACTIVITY_HISTORY
    public function activities_history()
    {
        return $this->hasMany(Activity_History::class);
    }
}

