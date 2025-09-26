<?php

namespace Eclipse\Common\Filament\Forms\Components\Concerns;

use Closure;

trait HasMediaUploadOptions
{
    protected array|Closure $acceptedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    protected bool|Closure $allowUrlUploads = false;

    protected bool|Closure $allowFileUploads = false;

    protected int|Closure|null $maxFiles = null;

    protected int|Closure|null $maxFileSize = null;

    protected bool|Closure $isMultiple = true;

    protected bool|Closure $allowBulkDelete = true;

    public function acceptedFileTypes(array|Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function allowUrlUploads(): static
    {
        $this->allowUrlUploads = true;

        return $this;
    }

    public function allowFileUploads(): static
    {
        $this->allowFileUploads = true;

        return $this;
    }

    public function allowUploads(): static
    {
        $this->allowFileUploads = true;
        $this->allowUrlUploads = true;

        return $this;
    }

    public function maxFiles(int|Closure|null $limit): static
    {
        $this->maxFiles = $limit;

        return $this;
    }

    public function maxFileSize(int|Closure|null $size): static
    {
        $this->maxFileSize = $size;

        return $this;
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function single(): static
    {
        $this->multiple(false);

        return $this;
    }

    public function getAcceptedFileTypes(): array
    {
        return $this->evaluate($this->acceptedFileTypes);
    }

    public function getAllowUrlUploads(): bool
    {
        return $this->evaluate($this->allowUrlUploads);
    }

    public function getAllowFileUploads(): bool
    {
        return $this->evaluate($this->allowFileUploads);
    }

    public function getMaxFiles(): ?int
    {
        return $this->evaluate($this->maxFiles);
    }

    public function getMaxFileSize(): ?int
    {
        return $this->evaluate($this->maxFileSize);
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->isMultiple);
    }

    public function bulkDelete(bool|Closure $condition = true): static
    {
        $this->allowBulkDelete = $condition;

        return $this;
    }

    public function disableBulkDelete(): static
    {
        $this->allowBulkDelete = false;

        return $this;
    }

    public function getAllowBulkDelete(): bool
    {
        return $this->evaluate($this->allowBulkDelete);
    }
}
