<?php

namespace AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames;

use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frame;
use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\IFrame;
use AntonioKadid\WAPPKitCore\IO\Exceptions\IOException;

/**
 * Class TextFrame
 *
 * @package AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames
 */
class TextFrame extends Frame implements IFrame
{
    /***
     * @return array
     *
     * @throws IOException
     */
    public function extract(): array
    {
        $encoding = bin2hex($this->read(1));
        $text = $this->readFromContent($encoding);

        return [
            'id' => $this->_id,
            'name' => $this->_name,
            'encoding' => $encoding,
            'text' => $text
        ];
    }
}