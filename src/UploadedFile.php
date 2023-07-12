<?php

declare(strict_types=1);

namespace JPI\HTTP;

class UploadedFile {

    public function __construct(
        protected string $filename,
        protected int $size,
        protected string $mediaType,
        protected int $errorCode,
        protected string $tempName
    ) {
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
