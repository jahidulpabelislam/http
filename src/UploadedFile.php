<?php

declare(strict_types=1);

namespace JPI\HTTP;

class UploadedFile {

    protected $filename;

    protected $size;
    protected $mediaType;

    protected $errorCode;

    protected $tempName;

    public function __construct(string $filename, int $size, string $mediaType, int $errorCode, string $tempName) {
        $this->filename = $filename;
        $this->size = $size;
        $this->mediaType = $mediaType;
        $this->errorCode = $errorCode;
        $this->tempName = $tempName;
    }

    public function saveTo(string $targetPath): bool {
        return move_uploaded_file($this->tempName, $targetPath);
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getErrorCode(): int {
        return $this->errorCode;
    }

    public function getFilename(): string {
        return $this->filename;
    }

    public function getMediaType(): string {
        return $this->mediaType;
    }

    public function getTempName(): string {
        return $this->tempName;
    }
}
