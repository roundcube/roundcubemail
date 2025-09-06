<?php

class rcube_plugin
{
    public function gettext($p)
    {
        global $labels;
    
        if (!isset($labels)) {
            include __DIR__ . "/../localization/en_US.inc";
        }

        return $labels[$p];
    }
}
