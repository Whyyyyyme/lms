<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudySemesterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Assistant\AnnouncementController as AssistantAnnouncementController;
use App\Http\Controllers\Assistant\AssignmentController as AssistantAssignmentController;
use App\Http\Controllers\Assistant\AssistantDashboardController;
use App\Http\Controllers\Assistant\CourseWorkspaceController as AssistantCourseWorkspaceController;
use App\Http\Controllers\Assistant\AttendanceController as AssistantAttendanceController;
use App\Http\Controllers\Assistant\ExportController as AssistantExportController;
use App\Http\Controllers\Assistant\MaterialController as AssistantMaterialController;
use App\Http\Controllers\Assistant\SubmissionController as AssistantSubmissionController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Mahasiswa\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Mahasiswa\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Mahasiswa\CalendarController as StudentCalendarController;
use App\Http\Controllers\Mahasiswa\ChatbotController as StudentChatbotController;
use App\Http\Controllers\Mahasiswa\CourseWorkspaceController as StudentCourseWorkspaceController;
use App\Http\Controllers\Mahasiswa\GradeController as StudentGradeController;
use App\Http\Controllers\Mahasiswa\MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\MaterialController as StudentMaterialController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::prefix('notifikasi')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');

        Route::get('/{notification}/buka', [NotificationController::class, 'open'])->name('open');

        Route::patch('/dibaca-semua', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::patch('/{notification}/dibaca', [NotificationController::class, 'markAsRead'])->name('read');

        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');

            Route::resource('users', UserController::class);

            Route::resource('semester', StudySemesterController::class)->parameters([
                'semester' => 'studySemester',
            ]);

            Route::resource('tahun-akademik', AcademicYearController::class)->parameters([
                'tahun-akademik' => 'academicYear',
            ]);

            Route::resource('matakuliah', CourseController::class)->parameters([
                'matakuliah' => 'course',
            ]);

            Route::resource('kelas', AdminClassController::class)->parameters([
                'kelas' => 'praktikumClass',
            ]);

            Route::prefix('laporan')->name('reports.')->group(function () {
                Route::get('/nilai', [ReportController::class, 'scores'])->name('scores');
                Route::get('/absensi', [ReportController::class, 'attendances'])->name('attendances');
                Route::get('/aktivitas', [ReportController::class, 'activities'])->name('activities');
                Route::get('/nilai/export', [ReportController::class, 'exportScores'])->name('scores.export');
                Route::get('/absensi/export', [ReportController::class, 'exportAttendances'])->name('attendances.export');
            });

            Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
        });

    Route::prefix('asisten')
        ->name('assistant.')
        ->middleware('role:asisten')
        ->group(function () {
            Route::get('/dashboard', [AssistantCourseWorkspaceController::class, 'index'])->name('dashboard');
            Route::get('/mata-kuliah', [AssistantCourseWorkspaceController::class, 'index'])->name('courses.index');
            Route::get('/mata-kuliah/{praktikumClass}', [AssistantCourseWorkspaceController::class, 'show'])->name('courses.show');

            Route::resource('materi', AssistantMaterialController::class)->parameters([
                'materi' => 'material',
            ]);

            Route::resource('tugas', AssistantAssignmentController::class)->parameters([
                'tugas' => 'assignment',
            ]);

            Route::prefix('submissions')->name('submissions.')->group(function () {
                Route::get('/', [AssistantSubmissionController::class, 'index'])->name('index');
                Route::get('/{submission}', [AssistantSubmissionController::class, 'show'])->name('show');
                Route::patch('/{submission}/nilai', [AssistantSubmissionController::class, 'grade'])->name('grade');
            });

            Route::prefix('absensi')->name('attendances.')->group(function () {
                Route::get('/', [AssistantAttendanceController::class, 'index'])->name('index');
                Route::get('/buat', [AssistantAttendanceController::class, 'create'])->name('create');
                Route::post('/', [AssistantAttendanceController::class, 'store'])->name('store');
                Route::get('/{attendance}', [AssistantAttendanceController::class, 'show'])->name('show');
                Route::patch('/{attendance}/buka', [AssistantAttendanceController::class, 'open'])->name('open');
                Route::patch('/{attendance}/tutup', [AssistantAttendanceController::class, 'close'])->name('close');
                Route::patch('/{attendance}/records/{record}', [AssistantAttendanceController::class, 'updateRecord'])->name('records.update');
                Route::delete('/{attendance}', [AssistantAttendanceController::class, 'destroy'])->name('destroy');
            });

            Route::resource('pengumuman', AssistantAnnouncementController::class)->parameters([
                'pengumuman' => 'announcement',
            ]);

            Route::prefix('export')->name('exports.')->group(function () {
                Route::get('/nilai/excel', [AssistantExportController::class, 'scoresExcel'])->name('scores.excel');
                Route::get('/nilai/pdf', [AssistantExportController::class, 'scoresPdf'])->name('scores.pdf');
                Route::get('/absensi/excel', [AssistantExportController::class, 'attendancesExcel'])->name('attendances.excel');
                Route::get('/absensi/pdf', [AssistantExportController::class, 'attendancesPdf'])->name('attendances.pdf');
            });
        });

    Route::prefix('mahasiswa')
        ->name('student.')
        ->middleware('role:mahasiswa')
        ->group(function () {
            Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');

            Route::get('/mata-kuliah', [StudentCourseWorkspaceController::class, 'index'])->name('courses.index');
            Route::get('/mata-kuliah/riwayat', [StudentCourseWorkspaceController::class, 'history'])->name('courses.history');
            Route::get('/mata-kuliah/{praktikumClass}', [StudentCourseWorkspaceController::class, 'show'])->name('courses.show');

            Route::get('/materi', [StudentMaterialController::class, 'index'])->name('materials.index');
            Route::get('/materi/riwayat', [StudentMaterialController::class, 'history'])->name('materials.history');
            Route::get('/materi/riwayat/mata-kuliah/{course}', [StudentMaterialController::class, 'historyCourse'])->name('materials.history-course');
            Route::get('/materi/mata-kuliah/{course}', [StudentMaterialController::class, 'course'])->name('materials.course');

            Route::get('/materi/{material}', [StudentMaterialController::class, 'show'])->name('materials.show');
            Route::get('/materi/{material}/preview', [StudentMaterialController::class, 'preview'])->name('materials.preview');
            Route::get('/materi/{material}/download', [StudentMaterialController::class, 'download'])->name('materials.download');

            Route::get('/tugas', [StudentAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('/tugas/riwayat', [StudentAssignmentController::class, 'history'])->name('assignments.history');
            Route::get('/tugas/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
            Route::post('/tugas/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
            Route::put('/submissions/{submission}', [StudentAssignmentController::class, 'updateSubmission'])->name('submissions.update');

            Route::get('/nilai', [StudentGradeController::class, 'index'])->name('grades.index');
            Route::get('/nilai/riwayat', [StudentGradeController::class, 'history'])->name('grades.history');

            Route::get('/absensi', [StudentAttendanceController::class, 'index'])->name('attendances.index');
            Route::post('/absensi/{attendance}/check-in', [StudentAttendanceController::class, 'checkIn'])->name('attendances.check-in');

            Route::get('/jadwal', [StudentCalendarController::class, 'schedule'])->name('schedule.index');
            Route::get('/kalender', [StudentCalendarController::class, 'index'])->name('calendar.index');

            Route::get('/chatbot', [StudentChatbotController::class, 'index'])->name('chatbot.index');
            Route::post('/chatbot/kirim', [StudentChatbotController::class, 'send'])->name('chatbot.send');
        });
});

if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}