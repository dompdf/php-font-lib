<?php

namespace FontLib\Tests;

use FontLib\Font;
use PHPUnit\Framework\TestCase;

class FontTest extends TestCase
{
    public function testLoadFileNotFound()
    {
        // @todo when PHP 5.4 support is dropped, uncomment line below and drop
        //       the try...catch block.
        // $this->expectException('\Fontlib\Exception\FontNotFoundException');
        try {
            Font::load('non-existing/font.ttf');
            $this->fail('Load should have failed.');
        }
        catch (\Fontlib\Exception\FontNotFoundException $e) {
            // Avoid throwing a risky test error.
            $this->assertTrue(true);
        }
    }

    public function testLoadTTFFontSuccessfully()
    {
        $trueTypeFont = Font::load('sample-fonts/IntelClear-Light.ttf');

        $this->assertInstanceOf('FontLib\TrueType\File', $trueTypeFont);
    }

    public function test12CmapFormat()
    {
        $trueTypeFont = Font::load('sample-fonts/NotoSansShavian-Regular.ttf');

        $trueTypeFont->parse();

        $cmapTable = $trueTypeFont->getData("cmap", "subtables");

        $cmapFormat4Table = $cmapTable[0];

        $this->assertEquals(4, $cmapFormat4Table['format']);
        $this->assertEquals(6, $cmapFormat4Table['segCount']);
        $this->assertEquals($cmapFormat4Table['segCount'], count($cmapFormat4Table['startCode']));
        $this->assertEquals($cmapFormat4Table['segCount'], count($cmapFormat4Table['endCode']));

        $cmapFormat12Table = $cmapTable[1];

        $this->assertEquals(12, $cmapFormat12Table['format']);
        $this->assertEquals(6, $cmapFormat12Table['ngroups']);
        $this->assertEquals(6, count($cmapFormat12Table['startCode']));
        $this->assertEquals(6, count($cmapFormat12Table['endCode']));
        $this->assertEquals(53, count($cmapFormat12Table['glyphIndexArray']));
    }

}
