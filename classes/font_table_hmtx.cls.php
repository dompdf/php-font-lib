<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `hmtx` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_hmtx extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $data = array();
    
    $numOfLongHorMetrics = $font->getData("hhea", "numOfLongHorMetrics");
    for($i = 0; $i < $numOfLongHorMetrics; $i++) {
      $advanceWidth = $font->readUInt16();
      $leftSideBearing = $font->readUInt16();
      $data[$i] = $advanceWidth;
    }
    
    $numGlyphs = $font->getData("maxp", "numGlyphs");
    if($numOfLongHorMetrics < $numGlyphs){
      $lastWidth = end($data);
      $data = array_pad($data, $numGlyphs, $lastWidth);
    }
    
    $this->data = $data;
  }
  
  protected function _encode() {
    return $this->getFont()->w(array(self::uint16, count($this->data)), $this->data);
  }
}