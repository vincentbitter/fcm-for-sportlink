<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Match_Result
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
    public $uitslag;
    public $uitslag_regulier;
    public $isAway;

    public function date()
    {
        return substr($this->wedstrijddatum, 0, 10);
    }

    public function team()
    {
        return $this->isAway ? $this->uitteamid : $this->thuisteamid;
    }

    public function opponent()
    {
        return $this->isAway ? $this->thuisteam : $this->uitteam;
    }

    public function matchGoalsFor()
    {
        if (preg_match('/^(\d+) - (\d+)$/', $this->uitslag_regulier, $matches)) {
            return intval($matches[$this->isAway ? 2 : 1]);
        }
        return null;
    }

    public function matchGoalsAgainst()
    {
        if (preg_match('/^(\d+) - (\d+)$/', $this->uitslag_regulier, $matches)) {
            return intval($matches[$this->isAway ? 1 : 2]);
        }
        return null;
    }

    public function matchGoalsForFinal()
    {
        if (preg_match('/^(\d+) - (\d+)$/', $this->uitslag, $matches)) {
            return intval($matches[$this->isAway ? 2 : 1]);
        }
        return null;
    }

    public function matchGoalsAgainstFinal()
    {
        if (preg_match('/^(\d+) - (\d+)$/', $this->uitslag, $matches)) {
            return intval($matches[$this->isAway ? 1 : 2]);
        }
        return null;
    }
}
