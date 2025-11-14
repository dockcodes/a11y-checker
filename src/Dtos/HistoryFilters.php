<?php

namespace Dock\A11yChecker\Dtos;

class HistoryFilters extends BaseDto
{
    public string | \DateTime | null $date_from = null;
    public string | \DateTime | null $date_to = null;

    protected function validate(): void
    {
        foreach (['date_from', 'date_to'] as $prop) {
            $value = $this->{$prop};
            if ($value === null) {
                continue;
            }
            if (!($value instanceof \DateTime) && !$this->isValidDate($value)) {
                throw new \InvalidArgumentException(
                    "Invalid date format for {$prop}: {$value}"
                );
            }
        }
    }

    private function isValidDate(string $date): bool
    {
        $formats = [
            "Y-m-d\TH:i:s.u\Z",
            "Y-m-d H:i:s.u\Z",
            "Y-m-d\TH:i:s\Z",
            "Y-m-d H:i:s\Z",
            "Y-m-d\TH:i",
            "Y-m-d H:i",
            "Y-m-d",
            "d.m.Y",
            "d.m.Y\TH:i",
            "d.m.Y H:i",
            "d-m-Y",
            "d-m-Y\TH:i",
            "d-m-Y H:i",
            "d/m/Y",
            "d/m/Y\TH:i",
            "d/m/Y H:i",
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $date);
            if ($dt !== false) {
                return true;
            }
        }

        return false;
    }
}