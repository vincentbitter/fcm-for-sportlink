<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Match
{
    public $wedstrijddatum;
    public $wedstrijdcode;
    public $teamnaam;
    public $thuisteamid;
    public $thuisteam;
    public $uitteamid;
    public $uitteam;
    public $aanvangstijd;
    public $wedstrijd;

    public function date()
    {
        return substr($this->wedstrijddatum, 0, 10);
    }

    public function isAway()
    {
        return $this->teamnaam != $this->thuisteam ? 1 : 0;
    }

    public function team()
    {
        return $this->isAway() ? $this->uitteamid : $this->thuisteamid;
    }

    public function opponent()
    {
        return $this->isAway() ? $this->thuisteam : $this->uitteam;
    }
}
