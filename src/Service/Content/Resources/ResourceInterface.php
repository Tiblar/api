<?php
declare(strict_types=1);

namespace App\Service\Content\Resources;

interface ResourceInterface
{
    /**
     * @return string
     */
    public function getContents(): string;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @return string
     */
    public function getHashName(): string;

    /**
     * @return string
     */
    public function getFileSize(): string;

    /**
     * @return string
     */
    public function getPostType(): ?string;

    /**
     * @return string
     */
    public function getOriginalName(): string;

    /**
     * @return string
     */
    public function getExtension(): string;

    /**
     * @return string
     */
    public function upload(): string;

    /**
     * @return bool
     */
    public function delete(): bool;
}
