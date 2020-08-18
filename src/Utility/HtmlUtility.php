<?php declare(strict_types=1);

/*
 * This file is part of the ESO Raidplanner project.
 * @copyright ESO Raidplanner.
 *
 * For the full license, see the license file distributed with this code.
 */

namespace App\Utility;

use DOMDocument;

class HtmlUtility
{
    /**
     * @param string $html
     * @return string
     */
    public static function removeScriptTags(string $html): string
    {
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $scriptTags = $doc->getElementsByTagName('script');
        $length = $scriptTags->length;
        for ($i = 0; $i < $length; $i++) {
            $scriptTags->item($i)->parentNode->removeChild($scriptTags->item($i));
        }

        return $doc->saveHTML();
    }
}
