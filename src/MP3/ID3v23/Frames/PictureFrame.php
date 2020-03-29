<?php

namespace AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames;

use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frame;
use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\IFrame;
use AntonioKadid\WAPPKitCore\IO\Exceptions\IOException;

/**
 * Class PictureFrame
 *
 * @package AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames
 */
class PictureFrame extends Frame implements IFrame
{
    /**
     * Get picture type name.
     *
     * @param string $type
     *
     * @return string
     */
    private static function getPictureTypeName(string $type): string
    {
        switch ($type) {
            case '00':
                return 'Other';
            case '01':
                return '32x32 pixels \'file icon\' (PNG only)';
            case '02':
                return 'Other file icon';
            case '03':
                return 'Cover (front)';
            case '04':
                return 'Cover (back)';
            case '05':
                return 'Leaflet page';
            case '06':
                return 'Media (e.g. lable side of CD)';
            case '07':
                return 'Lead artist/lead performer/soloist';
            case '08':
                return 'Artist/performer';
            case '09':
                return 'Conductor';
            case '0A':
                return 'Band/Orchestra';
            case '0B':
                return 'Composer';
            case '0C':
                return 'Lyricist/text writer';
            case '0D':
                return 'Recording Location';
            case '0E':
                return 'During recording';
            case '0F':
                return 'During performance';
            case '10':
                return 'Movie/video screen capture';
            case '11':
                return 'A bright coloured fish';
            case '12':
                return 'Illustration';
            case '13':
                return 'Band/artist logotype';
            case '14':
                return 'Publisher/Studio logotype';
            default:
                return 'Unknown';
        }
    }

    /**
     * Extract picture information from frame.
     *
     * http://id3.org/id3v2.3.0#Attached_picture
     *
     * @return array
     *
     * @throws IOException
     */
    public function extract(): array
    {
        $encoding = bin2hex($this->read(1));
        $mimeType = $this->readFromContent(self::ENCODING_ISO, '00');
        $pictureType = bin2hex($this->read(1));
        $description = $this->readFromContent($encoding, $encoding == self::ENCODING_ISO ? '00' : '0000');
        $data = base64_encode($this->getLine($this->_size));

        return [
            'id' => $this->_id,
            'name' => $this->_name,
            'encoding' => $encoding,
            'mimeType' => $mimeType,
            'type' => [
                'id' => $pictureType,
                'name' => self::getPictureTypeName($pictureType)
            ],
            'description' => $description,
            'data' => $data
        ];
    }
}