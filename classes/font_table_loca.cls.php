<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `loca` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_loca extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $indexToLocFormat = $font->getData("head", "indexToLocFormat");
    $numGlyphs = $font->getData("maxp", "numGlyphs");
    
    $data = array();
    
    // 2 bytes
    if ($indexToLocFormat == 0) {
      $d = $font->read(($numGlyphs + 1) * 2);
      $loc = unpack("n*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1] * 2;
      }
    }
    
    // 4 bytes
    else if ($indexToLocFormat == 1) {
      $d = $font->read(($numGlyphs + 1) * 4);
      $loc = unpack("N*", $d);
      
      for ($i = 0; $i <= $numGlyphs; $i++) {
        $data[] = $loc[$i+1];
      }
    }
    
    $this->data = $data;
  }
}