<?php

declare(strict_types=1);

namespace JPI\HTTP;

class UploadedFile {

    protected $filename;

    protected $size;
    protected $mediaType;

    protected $error;

    protected $tempName;

    public function __construct(string $filename, int $size, string $mediaType, string $error, string $tempName) {
        $this->filename = $filename;
        $this->size = $size;
        $this->mediaType = $mediaType;
        $this->error = $error;
        $this->tempName = $tempName;
    }

    public function moveTo(string $targetPath): bool {
        return move_uploaded_file($this->tempName, $targetPath);
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getError(): string {
        return $this->error;
    }

    public function getFilename(): string {
        return $this->filename;
    }

    public function getMediaType(): string {
        return $this->mediaType;
    }
}
