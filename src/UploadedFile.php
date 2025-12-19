<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * Represents an uploaded file.
 *
 * Provides information about an uploaded file and a method to save it
 * to a target location using move_uploaded_file().
 */
class UploadedFile {

    public function __construct(
        protected string $filename,
        protected int $size,
        protected string $mediaType,
        protected int $errorCode,
        protected string $tempName
    ) {
    }

    /**
     * Move the uploaded file to the specified target path.
     *
     * @return bool True on success, false on failure
     */
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
