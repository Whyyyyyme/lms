<?php

namespace App\Notifications\Concerns;

trait BuildsStudentNotificationUrls
{
    protected function studentMaterialUrl(int|string $materialId): string
    {
        return url('/mahasiswa/materi/' . $materialId);
    }

    protected function studentAssignmentUrl(int|string $assignmentId): string
    {
        return url('/mahasiswa/tugas/' . $assignmentId);
    }

    protected function studentAttendanceUrl(): string
    {
        return url('/mahasiswa/absensi');
    }

    protected function studentGradeUrl(): string
    {
        return url('/mahasiswa/nilai');
    }

    protected function studentDashboardUrl(): string
    {
        return url('/mahasiswa/dashboard');
    }
}
