<?php
/**
 * @copyright 2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

interface StorageEraseInterface
{
    /**
     * Drops and creates index storage.
     */
    public function erase();
}
