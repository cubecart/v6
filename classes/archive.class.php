<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Archive controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Archive
{
    /**
     * Archive handle
     *
     * @var handle
     */
    private $_archive = false;
    /**
     * Archive contents
     *
     * @var string
     */
    private $_contents = null;
    /**
     * Enabled?
     *
     * @var bool
     */
    private $_enabled = false;
    /**
     * Error message
     *
     * @var string
     */
    private $_error  = false;
    /**
     * Zip class class
     *
     * @var class
     */
    private $_zip  = false;

    ##############################################

    public function __construct($archive, $create = false)
    {
        $this->_enabled = (extension_loaded('zip')) ? true : false;
        if ($this->_enabled && !empty($archive)) {
            $this->_zip = new ZipArchive;
            return $this->open($archive, $create);
        }
        return false;
    }

    public function __destruct()
    {
        // Automatically close the zip file, writing all changes (if necessary)
        if ($this->_enabled) {
            $this->_close();
        }
    }

    //=====[ Public ]=======================================

    /**
     * Add file to the archive
     *
     * @param array/string $files
     * @param string $string
     * @return bool
     */
    public function add($files, $string = false)
    {
        if ($this->_enabled) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    $this->_addFile((string)$file);
                }
            } else {
                if (!empty($string) && is_string($string)) {
                    return $this->_zip->addFromString($files, $string);
                } else {
                    return $this->_addFile($files);
                }
            }
        }
        return false;
    }

    /**
     * Get archive contents
     *
     * @return string/false
     */
    public function contents()
    {
        if ($this->_enabled) {
            for ($i = 0; $i< $this->_zip->numFiles; ++$i) {
                $this->_contents[$i] = $this->_zip->statIndex($i);
                $comment = $this->_zip->getCommentIndex($i);
                if ($comment) {
                    $this->_contents[$i]['comment'] = $comment;
                }
            }
            return (!is_null($this->_contents)) ? $this->_contents : false;
        }
        return false;
    }

    /**
     * Delete file from archive
     *
     * @param string $filename
     * @return bool
     */
    public function delete($filename)
    {
        if ($this->_enabled && !empty($filename)) {
            $index = $this->_zip->locateName($filename, ZIPARCHIVE::FL_NODIR);
            if ($index) {
                return $this->deleteIndex((int)$index);
            }
        }

        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Get error
     *
     * @return string
     */
    private function error()
    {
        if ($this->_enabled) {
            switch ($this->_archive) {
            case ZIPARCHIVE::ER_CRC:
                $message = 'ZIP CRC error';
                break;
            case ZIPARCHIVE::ER_EXISTS:
                $message = 'ZIP archive already exists';
                break;
            case ZIPARCHIVE::ER_INCONS:
                $message = 'ZIP archive inconsistency';
                break;
            case ZIPARCHIVE::ER_INVAL:
                $message = 'Invalid arguments';
                break;
            case ZIPARCHIVE::ER_MEMORY:
                $message = 'Memory allocation failure';
                break;
            case ZIPARCHIVE::ER_MULTIDISK:
                $message = 'Multi-disk archives are not supported';
                break;
            case ZIPARCHIVE::ER_NOENT:
                $message = 'File does not exist';
                break;
            case ZIPARCHIVE::ER_NOZIP:
                $message = 'Not a valid ZIP archive';
                break;
            case ZIPARCHIVE::ER_OPEN:
                $message = 'Unable to open archive';
                break;
            case ZIPARCHIVE::ER_READ:
                $message = 'ZIP file read error';
                break;
            case ZIPARCHIVE::ER_SEEK:
                $message = 'ZIP file seek error';
                break;
            default:
                $message = 'Unknown error: '.$this->_archive;
            }
        } else {
            $message = 'ZIP library was not detected in your PHP installation';
        }
        trigger_error($message, E_USER_WARNING);
        $this->_enabled = false;

        return $message;
    }

    /**
     * Extract archive
     *
     * @param string $target Directory
     * @return bool
     */
    public function extract($target)
    {
        if ($this->_enabled && !empty($target)) {
            return $this->_zip->extractTo($target);
        }
        return false;
    }

    /**
     * Open archive
     *
     * @param string $archive
     * @param bool $create
     * @return bool
     */
    private function open($archive, $create = false)
    {
        if ($this->_enabled) {
            $archive = str_replace('/', '/', $archive);
            if ($create) {
                $this->_archive = $this->_zip->open($archive, ZIPARCHIVE::CREATE);
            } elseif (file_exists($archive)) {
                $this->_archive = $this->_zip->open($archive);
            }

            if ($this->_archive === true) {
                $this->contents();
                return true;
            } else {
                $this->error();
                return false;
            }
        }

        return false;
    }

    /**
     * Read archive
     *
     * @param string $filename
     * @return bool
     */
    public function read($filename = false)
    {
        if ($this->_enabled && !empty($filename)) {
            $index = $this->_zip->locateName($filename, ZIPARCHIVE::FL_NODIR);
            if ($index) {
                return $this->_zip->getFromIndex((int)$index);
            }
        }
        return false;
    }

    /**
     * Rename archive
     *
     * @param string $filename
     * @param string $new_name
     * @return bool
     */
    public function rename($filename, $new_name)
    {
        if ($this->_enabled) {
            ## not done yet
        }
        return false;
    }

    /**
     * Revert archive
     *
     * @return bool
     */
    public function revert()
    {
        return ($this->_enabled) ? $this->_zip->unchangeAll() : false;
    }

    /**
     * Adds file to archive.
     *
     * @param string $filename
     *
     * @return bool
     */
    private function _addFile($filename)
    {
        if ($this->_enabled) {
            if (file_exists($filename)) {
                if (is_dir($filename)) {
                    return $this->_zip->addEmptyDir($filename);
                } else {
                    return $this->_zip->addFile($filename);
                }
            }
        }
        return false;
    }

    /**
     * Close archive
     *
     * @return bool
     */
    private function _close()
    {
        return ($this->_enabled) ? $this->_zip->close() : false;
    }
}
