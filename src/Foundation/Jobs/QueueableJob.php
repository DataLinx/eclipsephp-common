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
     * Locale that is used for texts, important for user notifications etc.
     */
    public string $locale;

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
     *
     * This is called when the job is getting queued in the main process, so any context fetching (user, locale...) will work.
     */
    public function __construct(string $locale = null)
    {
        $this->locale = $locale ?? app()->getLocale();

        if (auth()->check()) {
            $this->dispatcher = auth()->user();
        }

        $this->queued();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Job {job} started.', ['job' => static::class]);
            $this->execute();
            $this->completed();
        } catch (Throwable $exception) {
            $this->failed($exception);
        }
    }

    /**
     * Execute the job.
     *
     * @throws Throwable
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
                ->seconds(5)
                ->sendToDatabase($this->dispatcher)
                ->broadcast($this->dispatcher);
        } else {
            throw new RuntimeException('Cannot send notification — no dispatcher set for job '.static::class);
        }
    }

    /**
     * Create the notification instance.
     *
     * @return Notification Filament Notification or any descended class
     */
    protected function makeNotification(): object
    {
        return Notification::make()
            ->title($this->getNotificationTitle())
            ->icon($this->getNotificationIcon())
            ->iconColor($this->getNotificationIconColor())
            ->body($this->getNotificationBody());
    }

    /**
     * Get the job name for displaying notifications.
     *
     * Defaults to unqualified class name. It's recommended to overload this method and return a end-user-friendly translatable name.
     */
    protected function getJobName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get the notification title based on the job status.
     *
     *  By default, this returns generic titles. Jobs can overload this function to return a more customized title.
     */
    protected function getNotificationTitle(): string
    {
        return __("eclipse-common::jobs.notifications.{$this->status->value}.title", ['job' => $this->getJobName()], $this->locale);
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
     *
     * By default, this returns generic messages. Jobs can overload this function to return a more customized message.
     */
    protected function getNotificationBody(): string
    {
        return match ($this->status) {
            JobStatus::QUEUED, JobStatus::COMPLETED => __("eclipse-common::jobs.notifications.{$this->status->value}.message", [], $this->locale),
            JobStatus::FAILED => __("eclipse-common::jobs.notifications.{$this->status->value}.message", [
                // Use up to 200 chars of the exception message to prevent "Pusher error: Payload too large.."
                'exception' => substr($this->exception, 0, 200),
            ], $this->locale),
        };
    }

    /**
     * Function that gets executed when the job is queued.
     */
    protected function queued(): void
    {
        Log::info('Job {job} queued.', ['job' => static::class]);

        if (isset($this->dispatcher)) {
            $this->notify();
        }
    }

    /**
     * Function that gets executed when the job is successfully completed.
     */
    protected function completed(): void
    {
        // Set job status and then output the correct notification title/body
        $this->status = JobStatus::COMPLETED;

        Log::info('Job {job} completed successfully.', ['job' => static::class]);

        if (isset($this->dispatcher)) {
            $this->notify();
        }
    }

    /**
     * Handle a job failure.
     */
    protected function failed(?Throwable $exception): void
    {
        // Set job status and then output the correct notification title/body
        $this->status = JobStatus::FAILED;

        $this->exception = $exception;

        Log::error('Job {job} encountered an error: {message}', ['job' => static::class, 'message' => $exception->getMessage()]);

        if (isset($this->dispatcher)) {
            $this->notify();
        }
    }
}
