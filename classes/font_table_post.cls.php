<?php
/**
 * @package php-font-lib
 * @link    http://php-font-lib.googlecode.com/
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * `post` font table.
 * 
 * @package php-font-lib
 */
class Font_Table_post extends Font_Table {
  protected function _parse(){
    $font = $this->getFont();
    
    $data = $font->unpack(array(
      "format"             => self::Fixed,
      "italicAngle"        => self::Fixed,
      "underlinePosition"  => self::FWord,
      "underlineThickness" => self::FWord,
      "isFixedPitch"       => self::uint32,
      "minMemType42"       => self::uint32,
      "maxMemType42"       => self::uint32,
      "minMemType1"        => self::uint32,
      "maxMemType1"        => self::uint32,
    ));
    
    $names = array();
    
    switch($data["format"]) {
      case 1:
        $names = Font_TrueType::$macCharNames;
      break;
      
      case 2:
        $data["numberOfGlyphs"] = $font->readUInt16();
        
        $glyphNameIndex = array();
        for($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $glyphNameIndex[] = $font->readUInt16();
        }
        
        $namesPascal = array();
        for($i = 0; $i < $data["numberOfGlyphs"]; $i++) {
          $len = $font->readUInt8();
          $namesPascal[] = $font->read($len);
        }
        
        foreach($glyphNameIndex as $g => $index) {
          if ($index < 258) {
            $names[$g] = Font_TrueType::$macCharNames[$index];
          }
          else {
            $names[$g] = $namesPascal[$index - 258];
          }
        }
        
        $data["glyphNameIndex"] = $glyphNameIndex;
        
      break;
      
      case 2.5:
        // TODO
      break;
      
      case 3:
        // nothing
      break;
      
      case 4:
        // TODO
      break;
    }
    
    $data["names"] = $names;
    
    $this->data = $data;
  }
}