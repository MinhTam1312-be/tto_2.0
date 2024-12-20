<?php

use App\Http\Controllers\Admin\ActivitiesLogController;
use App\Http\Controllers\Admin\AdminActivitiesHistory;
use App\Http\Controllers\Admin\AdminAnswer_CodeApiController;
use App\Http\Controllers\Admin\AdminAnswer_QuestionApiController;
use App\Http\Controllers\Admin\AdminChapterApiController;
use App\Http\Controllers\Admin\AdminCodeApiController;   
use App\Http\Controllers\Admin\AdminComment_DocumentApiController;
use App\Http\Controllers\Admin\AdminComment_PostApiController;
use App\Http\Controllers\Admin\AdminCourseApiController;
use App\Http\Controllers\Admin\AdminDocumentApiController;
use App\Http\Controllers\Admin\AdminEnrollmentApiController;
use App\Http\Controllers\Admin\AdminFaq_CourseApiController;
use App\Http\Controllers\Admin\AdminFavorite_CourseApiController;
use App\Http\Controllers\Admin\AdminModuleApiController;
use App\Http\Controllers\Admin\AdminNoteApiController;
use App\Http\Controllers\Admin\AdminPayment_LinkApiController;
use App\Http\Controllers\Admin\AdminPost_CategoryApiController;
use App\Http\Controllers\Admin\AdminPostApiController;
use App\Http\Controllers\Admin\AdminQuestionApiController;
use App\Http\Controllers\Admin\AdminReminderApiController;
use App\Http\Controllers\Admin\AdminRouteApiController;
use App\Http\Controllers\Admin\AdminStatus_DocApiController;
use App\Http\Controllers\Admin\AdminStatus_VideoApiController;
use App\Http\Controllers\Admin\AdminTransactionApiController;
use App\Http\Controllers\Admin\AdminUrl_QualityApiController;
use App\Http\Controllers\Admin\AdminUrl_Sub_TitleApiController;
use App\Http\Controllers\Admin\AdminUserApiController;
use App\Http\Controllers\Admin\StatisticsAccountantController;
use App\Http\Controllers\Admin\StatisticsAdminController;
use App\Http\Controllers\API\ChapterApiController;
use App\Http\Controllers\API\Comment_PostApiController;
use App\Http\Controllers\API\CourseApiController;
use App\Http\Controllers\API\FaqApiController;
use App\Http\Controllers\API\ImageApiController;
use App\Http\Controllers\API\NoteApiController;
use App\Http\Controllers\API\Post_CategoryController;
use App\Http\Controllers\API\RouteApiController;
use App\Http\Controllers\API\UserApiController;

use App\Http\Controllers\API\EnrollmentApiController;
use App\Http\Controllers\API\PostApiController;
use App\Http\Controllers\API\VNPayApiController;
use App\Http\Controllers\API\Comment_DocApiController;
use App\Http\Controllers\API\FavoriteCoursesApiController;
use App\Http\Controllers\API\GoogleSheetApiController;
use App\Http\Controllers\API\QuestionApiController;
use App\Http\Controllers\API\ReminderApiController;
use App\Http\Controllers\API\SocialApiController;
use App\Http\Controllers\API\StatisticsCourseController;
use App\Http\Controllers\API\StatisticsPostController;
use App\Http\Controllers\CertificateController;
use App\Models\Enrollment;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//INSTRUCTOR
Route::prefix('instructor')->group(function () {
    Route::middleware(['auth.api'])->group(function () {
        // Gộp 4 cái tổng khóa học, tổng doanh thu, đánh giá giảng viên, tổng lượt xem
        Route::get('statistical-course', [StatisticsCourseController::class, 'getStatisticalCourse']);
        // THỐNG KÊ
        // Thống kê tổng số khóa học của giảng viên, doanh thu tổng của giảng viên dựa trên người mua khóa học sau thuế.
        Route::get('statistical-course-management', [StatisticsCourseController::class, 'getStatistical']);
        Route::get('statistical-course-column-chart', [StatisticsCourseController::class, 'statisticalColumnChart']);
        // Tổng học viên chưa hoàn thành, và đã hoàn thành, tổng khóa học, tổng người đăng ký của giảng viên 
        Route::get('statistical-complete-course', [StatisticsCourseController::class, 'statisticalProgressClient']);
        Route::get('statistical-highest-rating-course', [StatisticsCourseController::class, 'statisticalHighestRatingCourse']);
        Route::get('statistical-course-column-chart', [StatisticsCourseController::class, 'statisticalColumnChart']);



        // Chức năng quản lý khóa học của instructor

        // QUẢN LÝ KHÓA HỌC
        // Chức năng gọi, thêm, sửa khóa học
        Route::resource('courses', AdminCourseApiController::class);
        // Ẩn, hiện khóa học
        Route::get('status-course/{course_id}', [AdminCourseApiController::class, 'statusCourse']);
        // Lấy ra các bài học từ course
        Route::get('doc-course/{course_id}', [AdminCourseApiController::class, 'docForUser']);
        // Tìm kiếm khóa học
        Route::post('search-course', [AdminCourseApiController::class, 'searchNameCourse']);
        // Lấy ra các lộ trình
        Route::resource('route', RouteApiController::class);


        // QUẢN LÝ CHƯƠNG
        // Chức năng gọi, thêm, sửa chương
        Route::resource('chapters', AdminChapterApiController::class);
        // Gọi các chương thuộc khóa học
        Route::get('chapters-by-course/{course_id}', [AdminChapterApiController::class, 'getChaptersByCourse']);
        // Ẩn, hiện bình luận bài học
        Route::get('status-chapter/{chapter_id}', [AdminChapterApiController::class, 'statusChapter']);
        // Lấy id khóa học đếm chapter đếm chi tiết chapter trong doc
        Route::get('getCountChapterAndDoc/{course_id}', [AdminChapterApiController::class, 'getCountChapterAndDoc']);


        // QUẢN LÝ DOCUMENTS
        // Chức năng gọi bài học
        Route::resource('documents', AdminDocumentApiController::class);
        // Gọi các bài học thuộc chương
        Route::get('documents-by-chapter/{chapter_id}', [AdminDocumentApiController::class, 'getDocumentsByChapter']);
        // Gọi các bài học thuộc chương
        Route::get('documents-by-course-chapter/{course_id}/{chapter_id}', [AdminDocumentApiController::class, 'getDocumentsByCourseChapter']);
        // Chức năng thêm document theo dạng video
        Route::post('store-video-document', [AdminDocumentApiController::class, 'storeVideoDocument']);
        // Chức năng thêm document theo dạng quiz
        Route::post('store-quiz-document', [AdminDocumentApiController::class, 'storeQuizDocument']);
        // Chức năng thêm document theo dạng code
        Route::post('store-code-document', [AdminDocumentApiController::class, 'storeCodeDocument']);
        // Chức năng sửa document theo dạng video
        Route::put('update-video-document/{doc_id}', [AdminDocumentApiController::class, 'updateVideoDocument']);
        // Chức năng sửa document theo dạng quiz
        Route::put('update-quiz-document/{doc_id}', [AdminDocumentApiController::class, 'updateQuizDocument']);
        // Chức năng sửa document theo dạng code
        Route::put('update-code-document/{doc_id}', [AdminDocumentApiController::class, 'updateCodeDocument']);
        // Chức năng ẩn, hiện bài học
        Route::get('status-document/{doc_id}', [AdminDocumentApiController::class, 'statusDocument']);


        // QUẢN LÝ BÌNH LUẬN KHÓA HỌC
        // Lấy ra tất cả bình luận
        Route::resource('get-all-comment-doc', AdminComment_DocumentApiController::class);
        // Lấy ra các bình luận của document
        Route::get('get-comment-doc/{doc_id}', [AdminComment_DocumentApiController::class, 'getCommentDoc']);
        // Chức năng bình luận
        Route::post('comment-doc/{doc_id}/{comment_id?}', [AdminComment_DocumentApiController::class, 'commentDoc']);
        // Sửa bình luận của mình.
        Route::match(['put', 'patch'], 'comment-update/{doc_id}/{comment_id}', [AdminComment_DocumentApiController::class, 'updateCommentDoc']);
        // Xóa bình luận của mình.
        Route::delete('comment-delete/{doc_id}/{comment_id}', [AdminComment_DocumentApiController::class, 'deleteCommentDoc']);
        // Ẩn, hiện bình luận bài học
        Route::get('status-comment-doc/{doc_id}/{comment_id}', [AdminComment_DocumentApiController::class, 'statusCommentDoc']);
    });
});

//ACCOUNTANT
Route::prefix('accountant')->group(function () {
    Route::middleware(['auth.api'])->group(function () {

        // THỐNG KÊ
        // Thống kê tổng người dùng
        Route::get('statistical-user', [StatisticsAccountantController::class, 'statisticalUser']);
        // Thống kê tổng đơn hàng
        Route::get('statistical-enrollment', [StatisticsAccountantController::class, 'statisticalEnrollment']);
        // Thống kê tổng lợi nhuận (sau thuế)
        Route::get('statistical-profits', [StatisticsAccountantController::class, 'statisticalProfits']);
        // Thống kê tổng đơn hàng hôm nay
        Route::get('statistical-enrollment-today', [StatisticsAccountantController::class, 'statisticalEnrollmentToday']);
        // Lấy ra các thống kê statistical-user, statistical-enrollment, statistical-profits, statistical-enrollment-today
        Route::get('get-all-statistical-UEPET', [StatisticsAccountantController::class, 'getStatistics']);
        // Thống kê các tháng theo năm
        Route::get('statistical-profits-by-months/{year?}', [StatisticsAccountantController::class, 'statisticalProfitsByMonth']);
        // Lấy thống kê truyền vào tuần trả về thứ 2 tới chủ nhật
        Route::get('weekly-statistics/{year}/{week}', [StatisticsAccountantController::class, 'getWeeklyStatistics']);
        // Lấy thống kê theo yêu cầu
        Route::get('transtion-statistics-request/{filterBy}/{status}/{order}', [StatisticsAccountantController::class, 'getTranstionStatisticsRequest']);
        // Lấy chi tiết transtion ra các khóa học và thông tin user
        Route::get('get-detail-transtion/{transtion_id}', [StatisticsAccountantController::class, 'getDetailTranstion']);
        // Lấy chi tiết khóa học ra các trastion
        Route::get('/courses-by-transactions/{slug_course}/{filterBy}/{status}/{order}', [StatisticsAccountantController::class, 'getTransactionsByCourse']);
        // Lấy ra người dùng có bao nhiêu thanh đơn thanh toán
        Route::get('user-by-transtion/{phone_or_email}', [StatisticsAccountantController::class, 'userByTranstion']);

        // Lấy ra tất cả khóa học theo kèm doanh thu bán được của khóa học đó
        Route::get('course-enrollment-revenue', [StatisticsAccountantController::class, 'courseEnrollmentRevenue']);
        // Lấy ra tất cả khóa học theo kèm doanh thu bán được của khóa học đó
        Route::get('course-enrollment-revenue/{slug_course}', [StatisticsAccountantController::class, 'courseEnrollmentRevenueBySlug']);
        // Lấy ra khóa học yêu thích nhất
        Route::get('most-favorite-course/{limit?}', [StatisticsAccountantController::class, 'mostFavoritesByCourse']);
        // lấy ra khóa học có đánh giá 5 sao yêu thích nhất 
        Route::get('most-rated-five-star-course/{limit?}', [StatisticsAccountantController::class, 'mostRaterFiveStarCourse']);
        // Khóa học có doanh thu cao nhất
        Route::get('highest-revenue-course', [StatisticsAccountantController::class, 'HighRevenueCourse']);
        // Khóa học có doanh thu thấp nhất
        Route::get('lowest-revenue-course', [StatisticsAccountantController::class, 'LowRevenueCourse']);
        // gộp 4 api most-favorite-course, most-rated-five-star-course, highest-revenue-course, lowest-revenue-course
        Route::get('getaccountantStatistics', [StatisticsAccountantController::class, 'getAllStatistics']);
        // Thống kê doanh thu
        Route::post('total-revenue-by-date', [StatisticsAccountantController::class, 'totalRevenueByDate']);
    });
});

//MARKETING
Route::prefix('marketing')->group(function () {
    Route::middleware(['auth.api'])->group(function () {
        // API GỘP THEO TRANG
        // Route configuration
        Route::get('statistics-posts/{limit?}', [AdminPostApiController::class, 'statisticsPosts']);
        // lấy tổng bài viết danh mục bình luận, lượt xem của quyền makerting
        Route::get('total-post-category-comment-view', [AdminPost_CategoryApiController::class, 'totalPostCategoryView']);



        // THỐNG KÊ
        // Thống kê của marketing
        Route::get('statistical-course-management', [StatisticsCourseController::class, 'getTotalCourseApprovedUnapprovedLecturer']);
        // Thống kê bài viết có nhiều bình luận lấy ra bài viết
        Route::get('statistics-post-many-comments-view/{limit?}', [AdminPostApiController::class, 'statisticsPostManyComments']);
        // Thống kê bài viết có nhiều bình luận lấy ra bài viết
        Route::get('statistics-post-little-comments-view/{limit?}', [AdminPostApiController::class, 'statisticsPostLittleComments']);
        // Thống kê bài viết có nhiều bình luận lấy ra bài viết
        Route::get('statistics-post-new/{limit?}', [AdminPostApiController::class, 'statisticsPostNew']);
        // Ẩn, hiện danh mục bài viết
        Route::get('status-category-post/{category_id}', [AdminPost_CategoryApiController::class, 'statusCategoryPost']);
        // Chức năng gọi, thêm, sửa danh mục bài viết
        // Route::resource('post_categories', AdminPost_CategoryApiController::class);

        // Chức năng gọi, thêm, sửa bài viết
        Route::resource('posts', AdminPostApiController::class);
        // Gọi ra các bình luận của bài viết
        Route::get('get-comment-post/{post_id}', [AdminComment_PostApiController::class, 'getCommentPost']);
        // Chức năng bình luận của bài viết
        Route::post('comment-post/{post_id}/{comment_id?}', [AdminComment_PostApiController::class, 'commentPost']);
        // Chức năng sửa bình luận của bài viết
        Route::put('update-comment-post/{post_id}/{comment_id}', [AdminComment_PostApiController::class, 'updatePost']);
        // Chức năng xóa bình luận của bài viết
        Route::delete('delete-comment-post/{post_id}/{comment_id}', [AdminComment_PostApiController::class, 'deleteComment']);
        // Ẩn bình luận bài viết
        Route::get('status-comment-post/{post_id}/{comment_post_id}', [AdminComment_PostApiController::class, 'statusCommentPost']);
    });
});

//ADMIN
Route::prefix('admin')->group(function () {
    Route::middleware(['auth.api'])->group(function () {

        // THỐNG KÊ
        // Thống kê tiến độ của học viên (Số học viên đang học, số học viên đã hoàn thành)
        Route::get('statistical-progress-client', [StatisticsCourseController::class, 'statisticalProgressClient']);
        // Thống các bài viết
        Route::get('statistical-post', [StatisticsPostController::class, 'index']);
        // Thống kê tổng bài viết, tổng comment, tổng danh mục thuộc bài viết, tổng lượt xem bài viết của người dùng đăng nhập
        Route::get('statistical-post-by-user', [StatisticsPostController::class, 'statisticalPostByUser']);
        // Tổng lượt xem qua tháng của các bài viết đó
        Route::get('get-total-view-mouth', [StatisticsPostController::class, 'getTotalViewMouth']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học
        Route::get('statistical-course-management', [StatisticsCourseController::class, 'getTotalCourseApprovedUnapprovedLecturer']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học
        Route::get('statistical-course-Approved-management-by-mouth', [StatisticsCourseController::class, 'getCourseApproveByMouth']);
        // Thống kê tổng khóa học, khóa học được đăng kí vào hôm nay, tồng giảng viên, doanh thu sau thuế
        Route::get('statistical-admin', [StatisticsAdminController::class, 'getTotalCourseCartNowStaffRevenue']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học 
        Route::get('statistical-revenue-mouth', [StatisticsAdminController::class, 'getTotalCourseRevenue']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học
        Route::get('statistical-admin-by-course/{course_id}', [StatisticsAdminController::class, 'getCourseInProgressCompletedAssessmentView']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học
        Route::get('statistical-admin-revenue-course/{course_id}', [StatisticsAdminController::class, 'getCourseRevenue']);
        // Tổng khóa học đã duyệt, chưa duyệt, tổng giảng viên, tổng khóa học
        Route::get('statistical-accountant', [StatisticsAdminController::class, 'getTotalClinetCartProfitCartNow']);
        // Thống kê tổng số học viên đã đăng ký ở các trạng thái
        Route::get('statistical-user-progress', [StatisticsCourseController::class, 'getTotalUserProgress']);
        // Thống kê tổng số người dùng có quyền client hoặc các quyền admin kháckhác
        Route::get('statistical-user/{client?}', [StatisticsCourseController::class, 'getTotalUser']);
        // Tổng khóa học 




        // QUẢN LÝ NGƯỜI DÙNG
        // Lấy ra các chức năng của người dùng
        Route::get('users/{role}', [AdminUserApiController::class, 'getUserRole']);
        // Ẩn người dùng
        Route::get('status-user/{user_id}', [AdminUserApiController::class, 'statusUser']);
        // Lấy ra các eamil có đuôi @fpt.edu.vn 
        Route::get('get-mail-fpt', [AdminUserApiController::class, 'getMailFpt']);
        // Cập nhật quyền
        Route::patch('update-role-admin', [AdminUserApiController::class, 'updateRoleAdmin']);

        // QUẢN LÝ LỘ TRÌNH
        // Chức năng gọi, thêm, sửa lộ trình
        Route::resource('routes', AdminRouteApiController::class);
        // Route thay đổi ảnh của lộ trình
        Route::post('update-images-route/{route_id}', [AdminRouteApiController::class, 'updateImagesRoute']);
        // Ẩn, hiện lộ trình
        Route::get('status-route/{route_id}', [AdminRouteApiController::class, 'statusRoute']);
        // Thêm khóa học vào lộ trình
        Route::post('add-course-to-route', [AdminModuleApiController::class, 'addCourseToRoute']);

        // QUẢN LÝ KHÓA HỌC
        // Chức năng gọi, thêm, sửa khóa học
        Route::resource('courses', AdminCourseApiController::class);
        // Hàm sửa ảnh của khóa học
        Route::post('update-images-course/{course_id}', [AdminCourseApiController::class, 'updateImagesCourse']);
        // Ẩn, hiện khóa học
        Route::get('status-course/{course_id}', [AdminCourseApiController::class, 'statusCourse']);
        // Gọi các khóa học thuộc lộ trình
        Route::get('courses-by-route/{route_id}', [AdminCourseApiController::class, 'getCoursesByRoute']);
        // Lấy ra các bài học từ course
        Route::get('doc-course/{course_id}', [AdminCourseApiController::class, 'docForUser']);
        // Tìm kiếm khóa học
        Route::post('search-course', [AdminCourseApiController::class, 'searchNameCourse']);
        // Duyệt khóa học của instructor đăng
        Route::patch('censor-course/{course_id}', [AdminCourseApiController::class, 'censorCourse']);
        // Lọc khóa học theo trạng thái, view, giảm giá
        Route::post('filter-course', [AdminCourseApiController::class, 'filterCourse']);
        // Show chi tiết khóa học admin
        Route::get('show-detail-course-admin/{course_id}', [AdminUserApiController::class, 'showCourseAdmin']);
        // Sắp xếp khóa học theo người học nhiều nhất hoặc ít nhất
        Route::get('filter-course-by-enroll', [AdminCourseApiController::class, 'filterCourseByEnroll']);


        // QUẢN LÝ CHƯƠNG
        // Chức năng gọi, thêm, sửa chương
        Route::resource('chapters', AdminChapterApiController::class);
        // Gọi các chương thuộc khóa học
        Route::get('chapters-by-course/{course_id}', [AdminChapterApiController::class, 'getChaptersByCourse']);
        // Ẩn, hiện bình luận bài học
        Route::get('status-chapter/{chapter_id}', [AdminChapterApiController::class, 'statusChapter']);
        // Lấy id khóa học đếm chapter đếm chi tiết chapter trong doc
        Route::get('getCountChapterAndDoc/{course_id}', [AdminChapterApiController::class, 'getCountChapterAndDoc']);


        // QUẢN LÝ DOCUMENTS
        // Chức năng gọi bài học
        Route::resource('documents', AdminDocumentApiController::class);
        // Gọi các bài học thuộc chương
        Route::get('documents-by-chapter/{chapter_id}', [AdminDocumentApiController::class, 'getDocumentsByChapter']);
        // Gọi các bài học thuộc chương
        Route::get('documents-by-course-chapter/{course_id}/{chapter_id}', [AdminDocumentApiController::class, 'getDocumentsByCourseChapter']);
        // Chức năng thêm document theo dạng video
        Route::post('store-video-document', [AdminDocumentApiController::class, 'storeVideoDocument']);
        // Chức năng thêm document theo dạng quiz
        Route::post('store-quiz-document', [AdminDocumentApiController::class, 'storeQuizDocument']);
        // Chức năng thêm document theo dạng code
        Route::post('store-code-document', [AdminDocumentApiController::class, 'storeCodeDocument']);
        // Chức năng sửa document theo dạng video
        Route::put('update-video-document/{doc_id}', [AdminDocumentApiController::class, 'updateVideoDocument']);
        // Chức năng sửa document theo dạng video
        Route::put('update-quiz-document/{doc_id}', [AdminDocumentApiController::class, 'updateQuizDocument']);
        // Chức năng sửa document theo dạng video
        Route::put('update-code-document/{doc_id}', [AdminDocumentApiController::class, 'updateCodeDocument']);


        // QUẢN LÝ CÂU HỎI THƯỜNG GẶP
        // Gọi các câu hỏi thường gặp thuộc khóa học
        Route::get('faq-by-course/{course_id}', [AdminFaq_CourseApiController::class, 'getFaqByCourse']);
        // Chức năng gọi, thêm, sửa câu hỏi thường gặp
        Route::resource('faq_courses', AdminFaq_CourseApiController::class);
        // Chức năng ẩn, hiện câu hỏi thường gặp
        Route::get('status-faq_course/{faq_course_id}', [AdminFaq_CourseApiController::class, 'statusFaqCourse']);


        // QUẢN LÝ BÀI VIẾT
        // lấy tổng bài viết danh mục bình luận, lượt xem của quyền makerting
        Route::get('total-post-category-comment-view-admin', [AdminPost_CategoryApiController::class, 'totalPostCategoryViewAdmin']);
        // Lọc bài viết
        Route::post('filter-post', [AdminPostApiController::class, 'filterPost']);
        // Chức năng gọi, thêm, sửa bài viết
        Route::resource('posts', AdminPostApiController::class);
        // Hàm sửa ảnh của bài viết
        Route::post('update-images-post/{post_id}', [AdminPostApiController::class, 'updateImagesPost']);
        // Ẩn, hiện bài viết
        Route::get('status-post/{post_id}', [AdminPostApiController::class, 'statusPost']);
        // Duyệt khóa học của instructor đăng
        Route::patch('censor-post/{post_id}', [AdminPostApiController::class, 'censorPost']);

        // QUẢN LÝ DANH MỤC BÀI VIẾT
        // Chức năng gọi, thêm, sửa danh mục bài viết
        Route::resource('post_categories', AdminPost_CategoryApiController::class);
        // Ẩn, hiện danh mục bài viết
        Route::get('status-category-post/{category_id}', [AdminPost_CategoryApiController::class, 'statusCategoryPost']);


        // QUẢN LÝ BÌNH LUẬN BÀI VIẾT
        // Lấy ra tất cả bình luận
        Route::get('get-all-comment-post', [AdminComment_PostApiController::class, 'getCommentPostAll']);
        // Gọi ra các bình luận của bài viết
        Route::get('get-comment-post/{post_id}', [AdminComment_PostApiController::class, 'getCommentPost']);
        // Chức năng bình luận của bài viết
        Route::post('comment-post/{post_id}/{comment_id?}', [AdminComment_PostApiController::class, 'commentPost']);
        // Chức năng sửa bình luận của bài viết
        Route::put('update-comment-post/{post_id}/{comment_id}', [AdminComment_PostApiController::class, 'updatePost']);
        // Ẩn bình luận bài viết
        Route::get('status-comment-post/{post_id}/{comment_id}', [AdminComment_PostApiController::class, 'statusCommentPost']);


        // QUẢN LÝ BÌNH LUẬN KHÓA HỌC
        // Lấy ra tất cả bình luận
        Route::resource('get-all-comment-doc', AdminComment_DocumentApiController::class);
        // Lấy ra các bình luận của document
        Route::get('get-comment-doc/{doc_id}', [AdminComment_DocumentApiController::class, 'getCommentDoc']);
        // Chức năng bình luận
        Route::post('comment-doc/{doc_id}/{comment_id?}', [AdminComment_DocumentApiController::class, 'commentDoc']);
        // Sửa bình luận của mình.
        Route::match(['put', 'patch'], 'comment-update/{doc_id}/{comment_id}', [AdminComment_DocumentApiController::class, 'updateCommentDoc']);
        // Ẩn, hiện bình luận bài học
        Route::get('status-comment-doc/{doc_id}/{comment_id}', [AdminComment_DocumentApiController::class, 'statusCommentDoc']);
        // Lấy ra các bài học theo khóa học
        Route::get('doc-by-course-admin/{course_id}', [AdminComment_DocumentApiController::class, 'docByCourseAdmin']);


        // QUẢN LÝ LỊCH SỬ GIAO DỊCH
        // Transtion get vs detail
        Route::resource('transaction', AdminTransactionApiController::class);
        // resource Transtion
        // Transtion lịch sử hoạt động
        Route::get('get-activity/{role?}/{status?}/{orderByDate?}', [AdminTransactionApiController::class, 'getActivity']);
        // Transtion search
        Route::get('get-activity-search/{search}', [AdminTransactionApiController::class, 'getActivitySearch']);
        // Route::get('get-conditional-transaction/{role}/{status}/{orderByDate}', [AdminTransactionApiController::class, 'getConditionalTransaction']);
        // Google sheet
        // Lấy ra google-sheet
        Route::get('google-sheets/read', [GoogleSheetApiController::class, 'readGoogleSheet']);
        // Lấy ra google sheet admin
        Route::post('post-google-sheets', [GoogleSheetApiController::class, 'postdminGoogleSheet']);
        // Sắp xếp theo đã trả lời hay chưa, sắp xếp theo ngày mới nhất cũ nhất
        Route::get('get-sort-data/{status?}/{sortOrder?}', [GoogleSheetApiController::class, 'getSortedData']);


        // QUẢN LÝ LỊCH SỬ HOẠT ĐỘNG
        // Lấy ra lịch hoạt dộng có parse là asc hoặc desc, có limit
        Route::get('activities-log/{parse?}/{limit?}', [ActivitiesLogController::class, 'getActivitiesLog']);
    });
});


Route::prefix('client')->group(function () {
    Route::post('/cancel-email-change', [UserApiController::class, 'cancel']);

    // XÁC NHẬN EMAIL, SỐ ĐIỆN THOẠI
    // check passowrd vào profile trả về true false
    Route::post('check-password', [UserApiController::class, 'checkPassword']);
    // Xác nhận email đăng ký, gửi mã 6 chữ số
    Route::post('check-mail-register', [UserApiController::class, 'checkMailRegister']);
    // Xác nhận mail khi thay đổi
    Route::post('check-mail-change', [UserApiController::class, 'checkMailChange']);
    // Xác nhận mail và token khi thay đổi và gửi mail cho mail cũ xác nhận
    Route::post('check-mail-token-change', [UserApiController::class, 'checkMailTokenChange']);
    // Kiểm tra số điện thoại, xác thực
    Route::post('check-phone', [UserApiController::class, 'checkPhoneUser']);
    // Nhập, xác nhận mã xác thực và thay đổi số điện thoại
    Route::patch('verify-phone-update-phone', [UserApiController::class, 'verifyPhone'])->middleware('auth.api');
    // Gửi mail quên mật khẩu
    Route::post('send-reset-password-mail', [UserApiController::class, 'forgotPassword']);
    // Reset mật khẩu
    Route::post('reset-password', [UserApiController::class, 'resetPassword']);
    // Nhập và xác thực mã xác nhận ở quên mật khẩu
    Route::post('verifyToken', [UserApiController::class, 'verifyToken']);


    // QUẢN LÝ THÔNG TIN CÁ NHÂN CỦA NGƯỜI DÙNG
    // Lấy ra thông tin người dùng
    Route::resource('users', UserApiController::class);
    // Thay đổi mô tả thông tin người dùng
    Route::patch('change-profile-discription-user', [UserApiController::class, 'changeDiscriptionUser'])->middleware('auth.api');
    // Thay đổi tuổi thông tin người dùng
    Route::patch('change-profile-age-user', [UserApiController::class, 'changeAgeUser'])->middleware('auth.api');
    // Thay đổi tên thông tin người dùng
    Route::patch('change-profile-fullname-user', [UserApiController::class, 'changeFullnameUser'])->middleware('auth.api');
    // Thay đổi email thông tin người dùng
    Route::patch('change-profile-email-user', [UserApiController::class, 'changeEmailUser']);
    // Thay đổi mật khẩu người dùng
    Route::patch('change-profile-password-user', [UserApiController::class, 'changePasswordUser'])->middleware('auth.api');
    // Thay đổi ảnh user profile
    Route::post('update-avatar', [UserApiController::class, 'updateAvatar'])->middleware('auth.api');
    // Xóa ảnh user profile   
    Route::delete('delete-avatar', [UserApiController::class, 'deleteAvatar'])->middleware('auth.api');


    // MAIN
    // Lấy ra các lộ trình
    Route::resource('route', RouteApiController::class);
    // Các lộ trình 
    Route::get('route-detail/{route_id}', [RouteApiController::class, 'routeDetail']);
    // Chi tiết lộ trình ra những khóa học
    Route::get('detail-route', [RouteApiController::class, 'detailRoute']);
    // Chi tiết lộ trình ra những khóa học theo route_id  
    Route::get('detail-route/{route_id}', [RouteApiController::class, 'detailByRouteId']);
    // Gọi ra những khóa học thuộc lộ trình
    Route::get('course-by-route/{route_id}', [RouteApiController::class, 'courseByRoute'])->middleware('auth.api');
    // Gọi ra 1 hoặc nhiều khóa học (client)
    Route::resource('courses', CourseApiController::class);
    // Lọc khóa học theo lộ trình
    Route::post('course-filter-condition', [CourseApiController::class, 'filterConditionCourse']);
    // Tìm kiếm khóa học
    Route::post('course-filter-name', [CourseApiController::class, 'filterNameCourse']);
    // Tìm kiếm khóa học, lộ trình, bài viết
    Route::get('search/{search}', [CourseApiController::class, 'searchCourseRoutePost']);
    // Lấy ra feedback theo sao và limit
    Route::get('feedback-limit/{star}/{limit}', [EnrollmentApiController::class, 'feedbackLimit']);
    // Gọi các khóa học thuộc lộ trình
    Route::get('courses-by-route-client/{route_id}', [RouteApiController::class, 'getCoursesByRouteClient']);

    // Lấy tên chapter trong khóa học
    Route::get('name-chapter-by-course/{course_id}', [CourseApiController::class, 'nameChapterByCourseId']);
    // Lấy ra khóa học free và pro
    Route::get('course-price/{price}/{slug_route?}/{limit?}', [CourseApiController::class, 'coursePrice']);
    // Lấy tên feedback của course theo slug_coursecourse
    Route::get('feedback-course/{course_id}/{star}/{limit}', [EnrollmentApiController::class, 'feedbackCourse']);
    // Người dùng đăng ký khóa học    
    Route::get('user-register-course/{course_id}', [EnrollmentApiController::class, 'userRegisterCourse']);
    // Check xem người dùng đã đăng hay chưa
    Route::get('check-enrollment/{courseId}', [EnrollmentApiController::class, 'checkEnrollment']);
    // Lấy ra các câu hỏi thường gặp theo slug_coursecourse
    Route::get('faq-course/{course_id}/{limit}', [FaqApiController::class, 'faqByCourseId']);
    // Lấy ra các doc thuộc course
    Route::get('doc-by-course/{course_id}', [CourseApiController::class, 'docByCourseId'])->middleware('auth.api');
    // Lấy ra trạng thái giữa các bài học, tuần tự
    Route::get('statusDoc-by-document/{document_id}/{course_id}', [CourseApiController::class, 'statusDocByDocument'])->middleware('auth.api');
    // Tạo trạng thái cho bài học khi dùng bấm vào
    Route::get('create-statusDoc/{document_id}/{course_id}', [CourseApiController::class, 'createDocument'])->middleware('auth.api');
    // Cập nhật trạng thái cho bài học
    Route::post('update-statusDoc', [CourseApiController::class, 'updateStatusDocument'])->middleware('auth.api');
    // Lấy ra tất cả trạng thái bài học theo khóa học
    Route::get('all-statusDoc-by-course/{course_id}', [CourseApiController::class, 'getAllStatusDocByCourse']);
    // Lấy ra các bài học từ course
    Route::get('doc-course/{course_id}', [CourseApiController::class, 'docForUser']);
    // Tiến độ khóa học theo từng user
    Route::get('progresss/{orderBy?}', [EnrollmentApiController::class, 'getProgress'])->middleware('auth.api');
    // Cập nhật trạng thái khóa học
    Route::patch('update-status-course/{enrollment_id}', [EnrollmentApiController::class, 'updateStatusCourse'])->middleware('auth.api');
    // Đánh giá sao và feedback cho khóa học
    Route::patch('add-feedback/{course_id}', [EnrollmentApiController::class, 'addFeedback'])->middleware('auth.api');
    // Chuyển trạng thái khóa học thành côngcông
    Route::patch('change-status-course-completed/{course_id}', [EnrollmentApiController::class, 'changeStatusCourseCompleted'])->middleware('auth.api');
    // Cấp chứng chỉ cho người dùng
    Route::post('add-certificate/{course_id}', [CertificateController::class, 'sendMailCertificate'])->middleware('auth.api');
    // Check chứng chỉ
    Route::get('check-certificate/{course_id}', [CertificateController::class, 'checkCertificate'])->middleware('auth.api');
    // Lấy tiến độ theo course
    Route::get('progress/{course_id}', [EnrollmentApiController::class, 'getProgressByCourse'])->middleware('auth.api');

    // Khóa học có tiến độ, người dùng đăng nhập
    Route::get('learning-course', [EnrollmentApiController::class, 'learingCourse'])->middleware('auth.api');
    // Lấy ra các bình luận của document bằng course_id
    Route::get('get-comment-doc-by-course/{course_id}', [Comment_DocApiController::class, 'getCommentDocByCourse'])->middleware('auth.api');
    // Lấy ra các bình luận của document
    Route::get('get-comment-doc/{doc_id}', [Comment_DocApiController::class, 'getCommentDoc'])->middleware('auth.api');
    // Lấy ra bình của luận của document có title id, và đếm replais
    Route::get('get-title-comment-doc/{doc_id}', [Comment_DocApiController::class, 'getTitleCommentDoc'])->middleware('auth.api');
    // Lấy ra name_chapter, lấy kiểu bài học, tên bài học, số lượng bài comment trong doc
    Route::get('get-total-comment-doc/{course_id}', [Comment_DocApiController::class, 'geTotalCommentDoc'])->middleware('auth.api');
    // Lấy ra chi tiết bình của luận của document có title id, và đếm replais
    Route::get('get-detail-comment-doc/{doc_id}/{comment_id}', [Comment_DocApiController::class, 'getDetailCommentDoc'])->middleware('auth.api');
    // Chức năng bình luận
    Route::post('comment-doc/{doc_id}/{comment_id?}', [Comment_DocApiController::class, 'commentDoc']);
    // Sửa bình luận của mình.
    Route::match(['put', 'patch'], 'comment-update/{doc_id}/{comment_id}', [Comment_DocApiController::class, 'updateCommentDoc']);
    // Xóa bình luận của mình.
    Route::delete('comment-delete/{doc_id}/{comment_id}', [Comment_DocApiController::class, 'deleteCommentDoc']);
    // Lấy ra các ghi chú của người dùng theo course
    Route::get('get-note-by-course/{course_id}/{parse?}', [NoteApiController::class, 'getNoteByCourse'])->middleware('auth.api');
    // Lấy ra các ghi chú của người dùng theo course
    Route::get('get-note-by-chapter/{chapter_id}/{parse?}', [NoteApiController::class, 'getNoteByChapter'])->middleware('auth.api');
    // Lấy ra các ghi chú của người dùng
    Route::get('get-note-by-user/{parse?}', [NoteApiController::class, 'getNoteByUser'])->middleware('auth.api');
    // Lấy ra các ghi chú của người dùng theo doc_id
    Route::get('get-note-by-doc/{doc_id}/{parse?}', [NoteApiController::class, 'getNoteByDoc'])->middleware('auth.api');
    // Tạo ghi chú
    Route::post('post-note/{doc_id}', [NoteApiController::class, 'postNote'])->middleware('auth.api');
    // Sửa ghi chú
    Route::match(['put', 'patch'], 'update-note/{doc_id}/{note_id}', [NoteApiController::class, 'updateNote'])->middleware('auth.api');
    // Xóa ghi chú
    Route::delete('delete-note/{note_id}', [NoteApiController::class, 'deleteNote'])->middleware('auth.api');
    // Lấy ra các bình luận của bài viết
    Route::get('get-comment-post/{post_id}', [Comment_PostApiController::class, 'getCommentPost'])->middleware('auth.api');
    // Chức năng bình luận của bài viết
    Route::post('comment-post/{post_id}/{comment_id?}', [Comment_PostApiController::class, 'commentPost'])->middleware('auth.api');
    // Chức năng sửa bình luận của bài viết
    Route::put('update-comment-post/{post_id}/{comment_id}', [Comment_PostApiController::class, 'updatePost'])->middleware('auth.api');
    // Chức năng xóa bình luận của bài viết
    Route::delete('delete-comment-post/{post_id}/{comment_id}', [Comment_PostApiController::class, 'deleteComment']);
    // VNPAY
    Route::post('VNPay/{course_id}/{course_price}', [VNPayApiController::class, 'getVNPay']);
    // VNPAY thanh toán 
    Route::get('vnpay-return', [VNPayApiController::class, 'vnpayReturn']);
    // MOMO
    Route::post('MOMO/{course_id}/{course_price}', [VNPayApiController::class, 'getMomo']);
    // MOMO thanh toán
    Route::get('momo-return', [VNPayApiController::class, 'momoReturn']);
    // Lấy ra nhắc nhở của học viên
    Route::get('get-reminders/{course_id}', [ReminderApiController::class, 'getReminder']);
    // Lấy ra nhắc nhở của học viên với khóa học
    Route::get('get-reminders-course', [ReminderApiController::class, 'getReminderCourse']);
    // Thêm nhắc nhở của học viên
    Route::post('post-reminders', [ReminderApiController::class, 'postReminder']);
    // Thay đổi nhắc nhở của học viên
    Route::put('update-reminders', [ReminderApiController::class, 'updateReminder']);
    // Xóa nhắc nhở của học viên
    Route::resource('reminders', ReminderApiController::class);
    // Check video đã được học chưa
    Route::post('update-status-lesson', [EnrollmentApiController::class, 'checkVideo']);
    // Khóa học yêu thích
    Route::resource('favorite-courses', FavoriteCoursesApiController::class)->middleware('auth.api');
    // Check câu trả lời đúng question 
    Route::post('check-answer/{question_id}', [QuestionApiController::class, 'checkAnswer']);
    // tất cả client
    Route::get('get-all-course-client', [CourseApiController::class, 'allCourseClient'])->middleware('auth.api');
    // Thêm google-sheet
    Route::post('/google-sheets/add', [GoogleSheetApiController::class, 'addData']);
    // Lấy ra những khóa học kếp tiếp
    Route::post('course-next/{orderBy}', [CourseApiController::class, 'courseNext']);
    // thông báo đăng ký khóa học
    Route::get('Notification', [QuestionApiController::class, 'notification']);
    // Lấy ảnh trong thư mục app/public/storage/images
    Route::get('image/{filename}', [ImageApiController::class, 'getImage']);
    // Upload ảnh
    Route::post('upload', [ImageApiController::class, 'uploadImage']);

    // Bài viết
    // Lấy ra tất cả bài viết, chi tiết bài viết
    Route::resource('posts', PostApiController::class);
    // Lấy ra tất cả bài viết, chi tiết bài viết
    Route::get('get-post-to-engarang/{slug_post}', [PostApiController::class, 'getPostTopEngarang']);
    // Lấy ra các danh mục bài viết theo limit
    Route::get('get-categories/{limit}', [Post_CategoryController::class, 'getCategoriesByLimit']);
    // Lấy ra các bài viết có nhiều bình luận nhất
    Route::get('get-posts-highest-comment/{limit}', [PostApiController::class, 'getPostsHighestComment']);
    // Bài viết view cao nhất
    Route::get('post-highest-view/{limit}', [PostApiController::class, 'postHighestView']);
    // Lọc bài viết theo tag
    Route::post('post-filter-tag', [PostApiController::class, 'filterTagPost']);
    // lấy ra 10% khóa học
    Route::get('ten-percent-course/{course_id}', [CourseApiController::class, 'tenPercentCourse']);
    // truyền slug_course lấy ra course_id 
    route::get('slug-by-id/{slug}/{table}', [CourseApiController::class, 'slugByIdCourse']);
    // gộp 4 api 
    route::get('handle-course-request/{course_id}', [CourseApiController::class, 'handleCourseRequest']);
});


Route::post('/check-token', [UserApiController::class, 'checkToken']);
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    // Đăng nhập bằng Google
    Route::post('login-google', [UserApiController::class, 'redirectToGoogle']);
    Route::post('login-facebook', [UserApiController::class, 'redirectToFacebook']);
    // Đăng nhập bằng số điện thoại và email
    Route::post('login', [UserApiController::class, 'login']);
    // Đăng ký tài khoản
    Route::post('register', [UserApiController::class, 'register']);
    // Đăng xuất
    Route::post('logout', [UserApiController::class, 'logout']);
    // Kiểm tra người dùng đăng nhập
    Route::get('profile', [UserApiController::class, 'me']);
    Route::post('respondWithToken', [UserApiController::class, 'respondWithToken']);
});
