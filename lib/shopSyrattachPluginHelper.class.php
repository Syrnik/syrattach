<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2021
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * Class shopSyrattachPluginHelper
 */
class shopSyrattachPluginHelper
{
    /**
     * Determines maximum upload size in bytes
     *
     * @see http://stackoverflow.com/a/2840875/2558549
     *
     * @return int
     */
    public function determineMaxUploadSize(): int
    {
        $max_upload = $this->convertPHPSizeToBytes(ini_get('upload_max_filesize'));
        $max_post = $this->convertPHPSizeToBytes(ini_get('post_max_size'));
        $memory_limit = $this->convertPHPSizeToBytes(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);

        return (int)($upload_mb * 0.8);
    }

    /**
     * Convert php.ini sizes like 64M to the numbers
     *
     * @see http://stackoverflow.com/a/22500394/2558549
     *
     * @param int|string $size
     * @return int
     */
    public function convertPHPSizeToBytes($size)
    {
        if (is_numeric($size)) {
            return $size;
        }
        $sSuffix = substr($size, -1);
        $iValue = substr($size, 0, -1);
        switch (strtoupper($sSuffix)) {
            case 'P':
                $iValue *= 1024;
            case 'T':
                $iValue *= 1024;
            case 'G':
                $iValue *= 1024;
            case 'M':
                $iValue *= 1024;
            case 'K':
                $iValue *= 1024;
                break;
        }

        return $iValue;
    }
}
