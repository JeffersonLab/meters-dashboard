<?php

namespace App\Console\Commands;

trait SyncTrait
{
    /**
     * Extract a property value from data structure if it exists.  Null otherwise.
     *
     * @param  object  $item
     * @param  string  $property
     * @return mixed
     */
    protected function propertyFromItem(object $item, string $property)
    {
        if (isset($item->properties) && isset($item->properties->$property)) {
            return $item->properties->$property;
        }

        return null;
    }
}
