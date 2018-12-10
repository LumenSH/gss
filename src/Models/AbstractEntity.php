<?php

namespace GSS\Models;

/**
 * Abstract entity
 */
abstract class AbstractEntity implements \JsonSerializable
{
    /**
     * Sets all properties from array.
     *
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!\property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = $value;
        }
    }

    /**
     * Returns all properties as array.
     */
    public function toArray(): array
    {
        return \get_object_vars($this);
    }

    /**
     * Returns all properties as array.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
