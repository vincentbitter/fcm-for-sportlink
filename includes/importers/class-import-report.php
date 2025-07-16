<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Import_Report
{
    public $created;
    public $updated;
    public $deleted;

    public function __construct($created, $updated, $deleted)
    {
        $this->created = $created;
        $this->updated = $updated;
        $this->deleted = $deleted;
    }

    public function to_array()
    {
        return array($this->created, $this->updated, $this->deleted);
    }
}
