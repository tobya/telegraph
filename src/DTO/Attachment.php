<?php

namespace DefStudio\Telegraph\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, string>
 */
class Attachment implements Arrayable
{
    public function __construct(
        private string $path,
        private string|null $filename = null,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function local(): bool
    {
        return Str::of($this->path)->startsWith('/');
    }

    public function contents(): string
    {
        return File::get($this->path);
    }

    public function filename(): string
    {
        return $this->filename ?? File::basename($this->path);
    }

    public function toArray(): array
    {
        return [
            'contents' => $this->contents(),
            'filename' => $this->filename(),
        ];
    }
}
