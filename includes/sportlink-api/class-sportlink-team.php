<?php

if (! defined('ABSPATH')) {
    exit;
}

class FCMSL_Team
{
    public $teamcode;
    public $teamnaam;
    public $speeldag;
    public $leeftijdscategorie;
    public $geslacht;
    public $competitiesoort;

    public function full_name()
    {
        $name = $this->teamnaam;
        if ($this->speeldag != 'Zaterdag')
            $name .= ' (' . strtolower($this->speeldag) . ')';
        return $name;
    }

    public function age_category()
    {
        if (strpos($this->leeftijdscategorie, 'Senioren') === 0)
            return FCManager_AgeCategory::SENIORS;

        if (strpos($this->leeftijdscategorie, 'Onder ') === 0)
            return FCManager_AgeCategory::YOUTH;

        return null;
    }

    public function gender()
    {
        switch ($this->geslacht) {
            case 'man':
                return FCManager_TeamGender::MALE;
            case 'vrouw':
                return FCManager_TeamGender::FEMALE;
            case 'gemengd':
                return FCManager_TeamGender::MIXED;
            default:
                return null;
        }
    }
}
