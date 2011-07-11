<?php
/**
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library in the file LICENSE.LGPL; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307 USA
 *
 * Alternatively, you may distribute this software under the terms of the
 * PHP License, version 3.0 or later.  A copy of this license should have
 * been distributed with this file in the file LICENSE.PHP .  If this is not
 * the case, you can obtain a copy at http://www.php.net/license/3_0.txt.
 *
 * @link http://php-font-lib.googlecode.com/
 * @author Fabien Ménager
 */

/* $Id$ */

require_once dirname(__FILE__)."/font_truetype.cls.php";
//require_once dirname(__FILE__)."/font_woff_table_directory_entry.cls.php";

class Font_EOT extends Font_TrueType {
  private $origF;
  private $fileOffset = 0;
  
  public $header;
  
  function parseHeader(){
    $this->header = $this->unpack(array(
      "EOTSize"        => self::uint32,
      "FontDataSize"   => self::uint32,
      "Version"        => self::uint32,
      "Flags"          => self::uint32,
    ));
    
    $this->header["FontPANOSE"] = $this->read(10);
    
    $this->header += $this->unpack(array(
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
      "FamilyNameSize" => self::uint16,
    ));
  }
  
  function parse() {
    exit("EOT not supported yet");
  }
  
  public function readUInt16() {
    $a = unpack('vv', $this->read(2));
    return $a['v'];
  }

  public function readUInt32() {
    $a = unpack('VV', $this->read(4));
    return $a['V'];
  }
}
