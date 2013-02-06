<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require_once dirname(__FILE__)."/font_header.cls.php";

/**
 * TrueType font file header.
 * 
 * @package php-font-lib
 */
class Font_EOT_Header extends Font_Header {
  protected $def = array(
    "format"        => self::uint32,
    "numTables"     => self::uint16,
    "searchRange"   => self::uint16,
    "entrySelector" => self::uint16,
    "rangeShift"    => self::uint16,
  );
  
  public function parse(){
    $font = $this->font;

    $this->data = $font->unpack(array(
      "EOTSize"        => self::uint32,
      "FontDataSize"   => self::uint32,
      "Version"        => self::uint32,
      "Flags"          => self::uint32,
    ));

    $this->data["FontPANOSE"] = $font->read(10);

    $this->data += $font->unpack(array(
      "Charset"        => self::uint8,
      "Italic"         => self::uint8,
      "Weight"         => self::uint32,
      "fsType"         => self::uint16,
      "MagicNumber"    => self::uint16,
      "UnicodeRange1"  => self::uint32,
      "UnicodeRange2"  => self::uint32,
      "UnicodeRange3"  => self::uint32,
      "UnicodeRange4"  => self::uint32,
      "CodePageRange1" => self::uint32,
      "CodePageRange2" => self::uint32,
      "CheckSumAdjustment" => self::uint32,
      "Reserved1"      => self::uint32,
      "Reserved2"      => self::uint32,
      "Reserved3"      => self::uint32,
      "Reserved4"      => self::uint32,
      "Padding1"       => self::uint16,
    ));

    $this->readString("FamilyName");
    $this->data["Padding2"] = $font->readUInt16();
    $this->readString("StyleName");
    $this->data["Padding3"] = $font->readUInt16();
    $this->readString("VersionName");
    $this->data["Padding4"] = $font->readUInt16();
    $this->readString("FullName");
  }

  private function readString($name) {
    $font = $this->font;
    $size = $font->readUInt16();

    $this->data["{$name}Size"] = $size;
    $this->data[$name] = $font->read($size);
  }

  public function encode(){
    //return $this->font->pack($this->def, $this->data);
  }
}