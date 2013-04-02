<?php

/**
 * Prepares an URL for URL Rewriting
 *
 * @param string $type
 * @param int $id
 * @param string $name
 * @return string

 */

function makeurl($type, $id, $name)
{
    $url = strtr($name,'ְֱֲֳִֵאבגדהוׂ׃װױײ״עףפץצרָֹÊֻטיךכַחּֽ־ֿלםמןÙÚÛÜשתûüÿׁס/','AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn-');
    $url=preg_replace('/ /', '-', $url);
    $url=trim(preg_replace('/[^a-z|A-Z|0-9|-]/', '', strtolower($url)), '-');
    $url=preg_replace('/\-+/', '-', $url);
    $url = urlencode($type . $id .'-'. $url .'.html');
    return $url;
}
?>