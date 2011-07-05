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

class Font_Binary_Stream {
  protected $f;
  
  const uint8     = 1;
  const  int8     = 2;
  const uint16    = 3;
  const  int16    = 4;
  const uint32    = 5;
  const  int32    = 6;
  const shortFrac = 7;
  const Fixed     = 8;
  const  FWord    = 9;
  const uFWord    = 10;
  const F2Dot14   = 11;
  const longDateTime = 12;
  
  public function load($filename) {
    $this->f = fopen($filename, "rb");
    return $this->f != false;
  }
  
  public function seek($offset) {
    return fseek($this->f, $offset, SEEK_SET) == 0;
  }
  
  public function skip($n) {
    fseek($this->f, $n, SEEK_CUR);
  }
  
  public function r($type) {
    switch($type) {
      case self::uint8:     return $this->read(1);
      case self::int8:      return $this->read(1);
      case self::uint16:    return $this->readUInt16();
      case self::int16:     return $this->readInt16();
      case self::uint32:    return $this->readUInt32();
      case self::int32:     return $this->readUInt32(); 
      case self::shortFrac: return $this->readFixed();
      case self::Fixed:     return $this->readFixed();
      case self::FWord:     return $this->readInt16();
      case self::uFWord:    return $this->readUInt16();
      case self::F2Dot14:   return $this->readInt16();
      case self::longDateTime: return $this->readLongDateTime();
    }
  }
  
  public function unpack($def) {
    $d = array();
    foreach($def as $name => $type) {
      $d[$name] = $this->r($type);
    }
    return $d;
  }

  public function read($n) {
    if ($n < 1) return "";
    return fread($this->f, $n);
  }

  public function readUInt16() {
    $a = unpack('nn', $this->read(2));
    return $a['n'];
  }

  public function readUInt32() {
    $a = unpack('NN', $this->read(4));
    return $a['N'];
  }

  public function readInt16() {
    $v = $this->readUInt16();
    
    if ($v >= 0x8000) {
      $v -= 65536;
    }
      
    return $v;
  }

  public function readFixed() {
    $d = $this->readInt16();
    $d2 = $this->readUInt16();
    return round($d + $d2 / 65536, 4);
  }
  
  public function readLongDateTime() {
    $dt = array($this->readUInt32(), $this->readUInt32());
    $date = $dt[1] - 2082844800;
    return strftime("%Y-%m-%d %H:%M:%S", $date);
  }
}
