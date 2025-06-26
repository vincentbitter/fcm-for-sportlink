<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCM_Sportlink_Player
{
    public $teamcode;
    public $relatiecode;
    public $voornaam;
    public $achternaam;
    public $tussenvoegsel;
    public $rol;
    public $functie;
    public $foto;

    public function last_name()
    {
        if ($this->tussenvoegsel) {
            return $this->tussenvoegsel . ' ' . $this->achternaam;
        }
        return $this->achternaam;
    }

    public function full_name()
    {
        return trim($this->voornaam . ' ' . $this->last_name());
    }
}
