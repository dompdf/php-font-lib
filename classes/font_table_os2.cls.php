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

class Font_Table_os2 extends Font_Table {
  protected $def = array(
    "version"             => self::uint16,
    "xAvgCharWidth"       => self::int16,
    "usWeightClass"       => self::uint16,
    "usWidthClass"        => self::uint16,
    "fsType"              => self::int16,
    "ySubscriptXSize"     => self::int16,
    "ySubscriptYSize"     => self::int16,
    "ySubscriptXOffset"   => self::int16,
    "ySubscriptYOffset"   => self::int16,
    "ySuperscriptXSize"   => self::int16,
    "ySuperscriptYSize"   => self::int16,
    "ySuperscriptXOffset" => self::int16,
    "ySuperscriptYOffset" => self::int16,
    "yStrikeoutSize"      => self::int16,
    "yStrikeoutPosition"  => self::int16,
    "sFamilyClass"        => self::int16,
    "panose"              => array(self::uint8, 10),
    "ulCharRange"         => array(self::uint32, 4),
    "achVendID"           => array(self::char,   4),
    "fsSelection"         => self::uint16,
    "fsFirstCharIndex"    => self::uint16,
    "fsLastCharIndex"     => self::uint16,
    "typoAscender"        => self::int16,
    "typoDescender"       => self::int16,
    "typoLineGap"         => self::int16,
    "winAscent"           => self::int16,
    "winDescent"          => self::int16,
  );
}