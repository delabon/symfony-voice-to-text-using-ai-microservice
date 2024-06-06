<?php

namespace App\Tests\Trait;

use RuntimeException;

trait FileDuplicator
{
    public static function duplicate(string $filepath): string
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $newFilepath = '/tmp/' . uniqid() . '.' . $ext;

        $result = copy($filepath, $newFilepath);

        if (!$result) {
            throw new RuntimeException("The file $filepath cannot be duplicated for some reason.");
        }

        return $newFilepath;
    }
}