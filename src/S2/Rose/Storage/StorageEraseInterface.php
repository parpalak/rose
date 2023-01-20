<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

interface StorageEraseInterface
{
    /**
     * Drops and creates index storage.
     */
    public function erase(): void;
}
