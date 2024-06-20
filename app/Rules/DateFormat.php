<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateFormat implements Rule
{
    private $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public function passes($attribute, $value)
    {
        $d = \DateTime::createFromFormat($this->format, $value);
        return $d && $d->format($this->format) === $value;
    }

    public function message()
    {
        return 'The :attribute does not match the format ' . $this->format;
    }
}
