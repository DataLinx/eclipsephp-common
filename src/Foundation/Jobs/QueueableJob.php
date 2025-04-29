<?php

namespace Eclipse\Common\Foundation\Jobs;

use Eclipse\Common\Enums\JobStatus;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

abstract class QueueableJob implements ShouldQueue
{
    use Queueable;

    /**
     * User that created the job. If it was created in the console or by the scheduler, no dispatcher is set.
     */
    public ?Authenticatable $dispatcher;

    /**
     * Job status
     */
    public JobStatus $status = JobStatus::QUEUED;

    /**
     * Exception that was thrown during job execution
     */
    protected ?Throwable $exception;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        if (auth()->check()) {
            $this->dispatcher = auth()->user();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->execute();

        $this->status = JobStatus::COMPLETED;

        if (isset($this->dispatcher)) {
            $this->notify();
        }
    }

    /**
     * Execute the job.
     */
    abstract protected function execute(): void;

    /**
     * Notify the dispatcher if one is set.
     */
    public function notify(): void
    {
        if (isset($this->dispatcher)) {
            // Send user notification to database
            $this->makeNotification()
                ->sendToDatabase($this->dispatcher);
        } else {
            throw new RuntimeException('Cannot send notification — no dispatcher set for job '.static::class);
        }
    }

    /**
     * Create the notification instance.
     */
    protected function makeNotification(): Notification
    {
        return Notification::make()
            ->title($this->getNotificationTitle())
            ->icon($this->getNotificationIcon())
            ->iconColor($this->getNotificationIconColor())
            ->body($this->getNotificationBody());
    }

    /**
     * Get the job name.
     *
     * Defaults to unqualified class name.
     */
    protected function getJobName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get the notification title based on the job status.
     */
    protected function getNotificationTitle(): string
    {
        return match ($this->status) {
            JobStatus::QUEUED => $this->getJobName().' queued.',
            JobStatus::COMPLETED => $this->getJobName().' successfully completed!',
            JobStatus::FAILED => $this->getJobName().' failed!',
        };
    }

    /**
     * Get the notification icon based on the job status.
     */
    protected function getNotificationIcon(): string
    {
        return match ($this->status) {
            JobStatus::QUEUED => 'heroicon-o-clock',
            JobStatus::COMPLETED => 'heroicon-o-check-circle',
            JobStatus::FAILED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Get the notification icon color based on the job status.
     */
    protected function getNotificationIconColor(): string
    {
        return match ($this->status) {
            JobStatus::QUEUED => 'info',
            JobStatus::COMPLETED => 'success',
            JobStatus::FAILED => 'danger',
        };
    }

    /**
     * Get the notification body based on the job status.
     */
    protected function getNotificationBody(): ?string
    {
        return match ($this->status) {
            JobStatus::FAILED => $this->exception->getMessage(),
            default => null,
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        // Set job status and then output the correct notification title/body
        $this->status = JobStatus::FAILED;

        $this->exception = $exception;

        Log::error('Error encountered while executing job {job}: {message}', ['job' => static::class, 'message' => $exception->getMessage()]);

        $this->notify();
    }
}
