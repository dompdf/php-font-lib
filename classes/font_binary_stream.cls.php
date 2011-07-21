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
  const char      = 13;
  
  public function load($filename) {
    $this->f = fopen($filename, "rb");
    return $this->f != false;
  }
  
  public function seek($offset) {
    return fseek($this->f, $offset, SEEK_SET) == 0;
  }
  
  public function pos() {
    return ftell($this->f);
  }
  
  public function skip($n) {
    fseek($this->f, $n, SEEK_CUR);
  }
  
  public function read($n) {
    if ($n < 1) return "";
    return fread($this->f, $n);
  }
  
  public function write($data) {
    if ($data === null || $data === "") return;
    return fwrite($this->f, $data);
  }

  public function readUInt8() {
    return ord($this->read(1));
  }

  public function writeUInt8($data) {
    return $this->write(chr($data));
  }

  public function readInt8() {
    $v = $this->readUInt8();
    
    if ($v >= 0x80) {
      $v -= 256;
    }
      
    return $v;
  }

  public function writeInt8($data) {
    // todo
  }

  public function readUInt16() {
    $a = @unpack('nn', $this->read(2));
    return $a['n'];
  }

  public function writeUInt16($data) {
    return $this->write(pack("n", $data));
  }

  public function readInt16() {
    $v = $this->readUInt16();
    
    if ($v >= 0x8000) {
      $v -= 0x10000;
    }
      
    return $v;
  }

  public function writeInt16($data) {
    if ($data < 0) {
      $data += 0x10000;
    }
    
    return $this->writeUInt16($data);
  }

  public function readUInt32() {
    $a = unpack('NN', $this->read(4));
    return $a['N'];
  }

  public function writeUInt32($data) {
    return $this->write(pack("N", $data));
  }

  public function readFixed() {
    $d = $this->readInt16();
    $d2 = $this->readUInt16();
    return round($d + $d2 / 0x10000, 4);
  }

  public function writeFixed($data) {
    $left = floor($data);
    $right = ($data - $left) * 0x10000;
    return $this->writeInt16($left) + $this->writeUInt16($left);
  }
  
  public function readLongDateTime() {
    $this->readUInt32(); // ignored 
    $date = $this->readUInt32() - 2082844800;
    
    return strftime("%Y-%m-%d %H:%M:%S", $date);
  }
  
  public function writeLongDateTime($data) {
    $date = strtotime($data);
    $date += 2082844800;
    
    return $this->writeUInt32(0) + $this->writeUInt32($date);
  }
  
  public function unpack($def) {
    $d = array();
    foreach($def as $name => $type) {
      $d[$name] = $this->r($type);
    }
    return $d;
  }
  
  public function pack($def, $data) {
    $bytes = 0;
    foreach($def as $name => $type) {
      $bytes += $this->w($type, $data[$name]);
    }
    return $bytes;
  }
  
  public function r($type) {
    switch($type) {
      case self::uint8:     return $this->readUInt8();
      case self::int8:      return $this->readInt8();
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
      case self::char:      return $this->read(1);
      default: 
        if ( is_array($type) ) {
          if ($type[0] == self::char) {
            return $this->read($type[1]);
          }
          
          $ret = array();
          for($i = 0; $i < $type[1]; $i++) {
            $ret[] = $this->r($type[0]);
          }
          return $ret;
        }
    }
  }
  
  public function w($type, $data) {
    switch($type) {
      case self::uint8:     return $this->writeUInt8($data);
      case self::int8:      return $this->writeInt8($data);
      case self::uint16:    return $this->writeUInt16($data);
      case self::int16:     return $this->writeInt16($data);
      case self::uint32:    return $this->writeUInt32($data);
      case self::int32:     return $this->writeUInt32($data); 
      case self::shortFrac: return $this->writeFixed($data);
      case self::Fixed:     return $this->writeFixed($data);
      case self::FWord:     return $this->writeInt16($data);
      case self::uFWord:    return $this->writeUInt16($data);
      case self::F2Dot14:   return $this->writeInt16($data);
      case self::longDateTime: return $this->writeLongDateTime($data);
      case self::char:      return $this->write($data);
      default: 
        if ( is_array($type) ) {
          if ($type[0] == self::char) {
            return $this->write($type[1]);
          }
          
          $ret = 0;
          for($i = 0; $i < $type[1]; $i++) {
            $ret += $this->w($type[0], $data[$i]);
          }
          return 0;
        }
    }
  }
  
  public function convertUInt32ToStr($uint32) {
    return chr(($uint32 >> 24) & 0xFF).chr(($uint32 >> 16) & 0xFF).chr(($uint32 >> 8) & 0xFF).chr($uint32 & 0xFF);
  }
}
