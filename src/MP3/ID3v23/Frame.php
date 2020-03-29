<?php

namespace AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23;

use AntonioKadid\WAPPKitCore\IO\Exceptions\IOException;
use AntonioKadid\WAPPKitCore\IO\Streams\TempStream;

/**
 * Class Frame
 *
 * @package AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23
 */
class Frame extends TempStream
{
    const FRAME_TITLE = 'TIT2';
    const FRAME_ARTIST = 'TPE1';
    const FRAME_ARTIST_ALBUM = 'TPE2';
    const FRAME_COMMENTS = 'COMM';
    const FRAME_PICTURE = 'APIC';
    const FRAME_ALBUM = 'TALB';
    const FRAME_YEAR = 'TYER';
    const FRAME_TRACK_NUMBER = 'TRCK';

    const ENCODING_ISO = '00';
    const ENCODING_UNICODE = '01';

    protected $_id;
    protected $_name;
    protected $_size;
    protected $_flags;

    /**
     * Frame constructor.
     *
     * @param string $frameId
     * @param int    $size
     * @param string $flags
     * @param string $content
     *
     * @throws IOException
     */
    public function __construct(string $frameId, int $size, string $flags, string $content)
    {
        parent::__construct($size);

        $this->_id = $frameId;
        $this->_name = $this->getName($frameId);
        $this->_size = $size;
        $this->_flags = $flags;

        $this->write($content);
        $this->seekFromBeginning(0);
    }

    /**
     * @param string $frameId
     *
     * @return string
     */
    private static function getName(string $frameId): string
    {
        switch ($frameId) {
            case self::FRAME_TITLE:
                return 'Title/songname/content description';
            case self::FRAME_ARTIST:
                return 'Lead performer(s)/Soloist(s)';
            case self::FRAME_ARTIST_ALBUM:
                return 'Band/orchestra/accompaniment';
            case self::FRAME_COMMENTS:
                return 'Comments';
            case self::FRAME_PICTURE:
                return 'Attached picture';
            case self::FRAME_ALBUM:
                return 'Album/Movie/Show title';
            case self::FRAME_YEAR:
                return 'Year';
            case self::FRAME_TRACK_NUMBER:
                return 'Track number/Position in set';
            default:
                return 'Unknown';
        }
    }

    /**
     * @param string $encoding
     * @param string $hexDelimiter
     *
     * @return string
     *
     * @throws IOException
     */
    protected function readFromContent(string $encoding = self::ENCODING_ISO, string $hexDelimiter = ''): string
    {
        switch ($encoding) {
            case self::ENCODING_ISO:
                $data = '';
                while (!$this->atEnd()) {
                    $chr = $this->read(1);
                    if ($chr === '')
                        continue;

                    $hexValue = bin2hex($chr);
                    if ($hexValue === $hexDelimiter)
                        return html_entity_decode($data);

                    $data .= sprintf('&#x%s;', $hexValue);
                }

                return html_entity_decode($data);
            case self::ENCODING_UNICODE:
                $data = '';

                $endianFlag = $this->read(2);
                $isLittleEndian = bin2hex($endianFlag) === 'fffe';

                while (!$this->atEnd()) {
                    $byte1 = $this->read(1);
                    $byte2 = $this->read(1);

                    if ($byte1 === '' || $byte2 === '')
                        continue;

                    $hexValue = bin2hex("{$byte1}{$byte2}");
                    if ($hexValue === $hexDelimiter)
                        return html_entity_decode($data);

                    $chr = bin2hex($isLittleEndian ? $byte2 . $byte1 : $byte1 . $byte2);
                    $data .= sprintf('&#x%s;', $chr);
                }

                return html_entity_decode($data);
            default:
                return '';
        }
    }
}