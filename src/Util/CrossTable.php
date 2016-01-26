<?php

/*
 * This file is part of the Crosstabs Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\CrosstabsBundle\Util;

class CrossTable
{
    /**
     * @param \DataContainer $dc
     * @param array $arrFields
     */
    public function filter(\DataContainer $dc, array $arrFields)
    {
        $do = \Input::get('do');
        $intId = \Input::get('id');

        if (is_array($arrFields) && isset($arrFields[$do]) && is_numeric($intId) && $intId > 0) {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['filter'][] = array($arrFields[$do] . '=?', $intId);
        }
    }

    /**
     * @param $intId
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return string
     */
    public function icon($intId, $href, $label, $title, $icon)
    {
        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $intId . '&amp;popup=1') . '" title="' . specialchars($title) . '" onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . specialchars(str_replace("'",
            "\\'", sprintf($title, $intId))) . '\',\'url\':this.href});return false">' . \Image::getHtml($icon,
            $label) . '</a> ';
    }
}
