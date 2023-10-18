<?php
/**
 * @package php-font-lib
 * @link    https://github.com/dompdf/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace FontLib\Table\Type;
use FontLib\Table\Table;

/**
 * `kern` font table.
 *
 * @package php-font-lib
 */
class kern extends Table {
  protected function _parse() {
    $font = $this->getFont();

    $data = $font->unpack(array(
      "version"         => self::uint16,
      "nTables"         => self::uint16,

      // only the first subtable will be parsed
      "subtableVersion" => self::uint16,
      "length"          => self::uint16,
      "coverage"        => self::uint16,
    ));

    $data["format"] = ($data["coverage"] >> 8);

    $subtable = array();

    switch ($data["format"]) {
      case 0:
        $subtable = $font->unpack(array(
          "nPairs"        => self::uint16,
          "searchRange"   => self::uint16,
          "entrySelector" => self::uint16,
          "rangeShift"    => self::uint16,
        ));

        $pairs = array();
        $tree  = array();

        $values = $font->readUInt16Many($subtable["nPairs"] * 3);
        for ($i = 0, $idx = 0; $i < $subtable["nPairs"]; $i++) {
          $left  = $values[$idx++];
          $right = $values[$idx++];
          $value = $values[$idx++];

          if ($value >= 0x8000) {
            $value -= 0x10000;
          }

          $pairs[] = array(
            "left"  => $left,
            "right" => $right,
            "value" => $value,
          );

          $tree[$left][$right] = $value;
        }

        //$subtable["pairs"] = $pairs;
        $subtable["tree"] = $tree;
        break;

      case 1:
      case 2:
      case 3:
        break;
    }

    $data["subtable"] = $subtable;

    $this->data = $data;
  }
}