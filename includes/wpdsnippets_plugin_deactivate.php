<?php
/**
 * @package WPDSnippets
 */

class WpdSnippetsPluginDeactivate
{


    public static function deactivate () {
        flush_rewrite_rules();
    }


}