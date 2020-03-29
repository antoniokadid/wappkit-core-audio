<?php

namespace AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23;

use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames\CommentFrame;
use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames\PictureFrame;
use AntonioKadid\WAPPKitCore\Audio\MP3\ID3v23\Frames\TextFrame;
use AntonioKadid\WAPPKitCore\IO\Exceptions\IOException;
use AntonioKadid\WAPPKitCore\IO\Streams\FileStream;
use AntonioKadid\WAPPKitCore\IO\Streams\TempStream;

/**
 * Class TagReader
 *
 * Compatible only with ID3v2.3.
 *
 * @package AntonioKadid\WAPPKitCore\Audio
 */
class TagReader
{
    /**
     * @param string $filename
     *
     * @return array
     *
     * @throws IOException
     */
    public function getTags(string $filename): array
    {
        if (!file_exists($filename))
            throw new IOException(sprintf('File "%s" does not exist.', $filename));

        $fileStream = new FileStream($filename, 'rb');

        // Load ID3 Header
        $id3 = $fileStream->read(3);
        if ($id3 !== 'ID3')
            throw new IOException(sprintf('File "%s" does not have valid ID3 tag.', $filename));

        $id3MajorVersion = current(unpack('H*', $fileStream->read(1)));
        if (strpos($id3MajorVersion, '03') !== 0)
            throw new IOException(sprintf('File "%s" is using an unsupported ID3 version.', $filename));

        $id3Revision = current(unpack('H*', $fileStream->read(1)));

        $id3Flags = $fileStream->read(1);

        $id3Size = unpack('N*', $fileStream->read(4))[1];

        $id3Data = $fileStream->read($id3Size);

        $fileStream->close();

        // Process the data
        return $this->processID3Data($id3Data);
    }

    /**
     * @param string $data
     *
     * @return array
     * @throws IOException
     */
    private function processID3Data(string $data): array
    {
        $result = [];

        $stream = TempStream::fromBytes($data);

        while (!$stream->atEnd()) {
            $frameId = $stream->read(4);

            switch ($frameId) {
                case Frame::FRAME_TITLE:
                case Frame::FRAME_ARTIST_ALBUM;
                case Frame::FRAME_ARTIST:
                case Frame::FRAME_ALBUM:
                case Frame::FRAME_YEAR:
                case Frame::FRAME_TRACK_NUMBER:
                    list($frameSize, $frameFlags, $frameContents) = $this->readFrameInfo($stream);

                    $result[] = new TextFrame($frameId, $frameSize, $frameFlags, $frameContents);
                    break;
                case Frame::FRAME_PICTURE:
                    list($frameSize, $frameFlags, $frameContents) = $this->readFrameInfo($stream);

                    $result[] = new PictureFrame($frameId, $frameSize, $frameFlags, $frameContents);
                    break;
                case Frame::FRAME_COMMENTS:
                    list($frameSize, $frameFlags, $frameContents) = $this->readFrameInfo($stream);

                    $result[] = new CommentFrame($frameId, $frameSize, $frameFlags, $frameContents);
                    break;
            }
        }

        return $result;
    }

    /**
     * @param TempStream $stream
     *
     * @return array
     *
     * @throws IOException
     */
    private function readFrameInfo(TempStream $stream)
    {
        $frameSize = (int)current(unpack('N*', $stream->read(4)));
        $frameFlags = $stream->read(2);
        $frameContents = $frameSize === 0 ? '' : $stream->read($frameSize);

        return [$frameSize, $frameFlags, $frameContents];
    }
}