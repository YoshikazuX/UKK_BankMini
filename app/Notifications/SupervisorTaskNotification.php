<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupervisorTaskNotification extends Notification
{
    use Queueable;

    /**
     * @param array{
     *     task_type: string,
     *     task_id: int|string,
     *     nomor_referensi: string,
     *     nama_teller: string,
     *     title: string,
     *     message: string,
     *     nominal?: int|null,
     *     status: string,
     *     action_url: string,
     *     waktu?: string|null
     * } $data
     */
    public function __construct(public array $data) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge([
            'waktu' => now()->translatedFormat('d F Y H:i'),
        ], $this->data);
    }
}
