<?php

namespace App\Helpers;

use App\Models\User;
use App\Notifications\SupervisorTaskNotification;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Kirim notifikasi ke seluruh Supervisor dan Accounting aktif.
     *
     * @param array{
     *     task_type: string,
     *     task_id: int|string,
     *     nomor_referensi: string,
     *     nama_teller: string,
     *     title: string,
     *     message: string,
     *     nominal?: int|null,
     *     status: string,
     *     action_url: string
     * } $data
     */
    public static function notifySupervisors(array $data): void
    {
        try {
            $supervisors = User::whereIn('role', ['supervisor', 'accounting'])->get();
            foreach ($supervisors as $supervisor) {
                $supervisor->notify(new SupervisorTaskNotification($data));
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi supervisor: '.$e->getMessage());
        }
    }

    /**
     * Kirim notifikasi keputusan ke Teller pemohon.
     *
     * @param array{
     *     task_type: string,
     *     task_id: int|string,
     *     nomor_referensi: string,
     *     nama_teller: string,
     *     title: string,
     *     message: string,
     *     nominal?: int|null,
     *     status: string,
     *     action_url: string
     * } $data
     */
    public static function notifyUser(int|string $userId, array $data): void
    {
        try {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new SupervisorTaskNotification($data));
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi user: '.$e->getMessage());
        }
    }

    /**
     * Tandai notifikasi pekerjaan sebagai terbaca jika user membukanya.
     */
    public static function markTaskAsRead(?User $user, string $taskType, int|string $taskId): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->unreadNotifications
                ->filter(function ($n) use ($taskType, $taskId) {
                    return isset($n->data['task_type'], $n->data['task_id']) &&
                        $n->data['task_type'] === $taskType &&
                        (string) $n->data['task_id'] === (string) $taskId;
                })
                ->markAsRead();
        } catch (\Throwable $e) {
            Log::error('Gagal menandai notifikasi terbaca: '.$e->getMessage());
        }
    }
}
