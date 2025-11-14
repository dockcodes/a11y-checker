<?php

namespace Dock\A11yChecker\Dtos;

abstract class BaseDto
{
    public static function from(...$params): static
    {
        $instance = new static();

        if (count($params) === 1 && is_array($params[0])) {
            $params = $params[0];
        } else {
            $params = $params;
        }

        foreach ($params as $key => $value) {
            if (!property_exists($instance, $key)) {
                throw new \InvalidArgumentException(
                    "Property '{$key}' does not exist in " . static::class
                );
            }
            $instance->{$key} = $value;
        }

        if (method_exists($instance, 'validate')) {
            $instance->validate();
        }

        return $instance;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}