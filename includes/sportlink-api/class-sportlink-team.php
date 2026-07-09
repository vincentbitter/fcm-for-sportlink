<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Team
{
    public $teamcode;
    public $teamnaam;
    public $speeldag;

    public function full_name()
    {
        $name = $this->teamnaam;
        if ($this->speeldag != 'Zaterdag')
            $name .= ' (' . strtolower($this->speeldag) . ')';
        return $name;
    }
}
