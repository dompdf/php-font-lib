<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `glyf` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_glyf extends Font_Table {
  protected function getGlyph($gid){
    return;
    
    $indexToLocFormat = $this->getData("head", "indexToLocFormat");
    $numGlyphs = $this->getData("maxp", "numGlyphs");
    
    $tableOffset = $this->pos();
    
    $data = $this->unpack(array(
      "numberOfContours" => self::int16,
      "xMin" => self::FWord,
      "yMin" => self::FWord,
      "xMax" => self::FWord,
      "yMax" => self::FWord,
    ));
    
    $this->seek($tableOffset);
    
    $this->data = $data;
  }
  
  protected function _parse(){
    return $this->_parseRaw();
  }
  
  protected function _encode(){
    return $this->_encodeRaw();
  }
}