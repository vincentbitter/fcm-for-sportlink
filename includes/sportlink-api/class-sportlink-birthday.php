<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Birthday
{
    public $volledigenaam;
    public $verjaardag;

    public function date_of_birth()
    {
        if ($this->verjaardag) {
            return DateTimeImmutable::createFromFormat('j M Y', $this->verjaardag . ' 1900')->format('Y-m-d');
        }
        return null;
    }

    public function full_name()
    {
        return $this->volledigenaam;
    }
}
