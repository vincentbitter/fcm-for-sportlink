<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Referee
{
    public $first_name;
    public $last_name;

    public function __construct($scheidsrechter, $scheidsrechters)
    {
        if ($scheidsrechter) {
            $this->parse_name($scheidsrechter);
        } else if ($scheidsrechters) {
            if (preg_match('/([^,]+)\s*\(Spelbegeleider\)/', $scheidsrechters, $matches)) {
                $this->parse_name($matches[1]);
            }
        }
    }

    private function parse_name($scheidsrechter)
    {
        $scheidsrechter = trim($scheidsrechter);
        if (preg_match('/\(([^)]+)\)\s+(.+)$/', $scheidsrechter, $matches)) {
            $this->first_name = $matches[1];
            $this->last_name = $matches[2];
        } else {
            $this->first_name = '';
            $this->last_name = $scheidsrechter;
        }
    }

    public function name()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
