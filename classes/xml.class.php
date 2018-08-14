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
 * XML controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class XML extends XMLWriter
{

    ##############################################

    public function __construct($xml_header = true, $indent_string = ' ')
    {
        $this->openMemory();
        $this->setIndent(true);
        $this->setIndentString($indent_string);
        if ($xml_header) {
            $this->startDocument('1.0', 'UTF-8');
        }
    }

    //=====[ Public ]=======================================

    /**
     * Add an array to the element
     *
     * @param array $array
     * @return bool
     */
    public function addArray($array)
    {
        if (is_array($array)) {
            foreach ($array as $index => $data) {
                if (is_array($data)) {
                    if (!isset($data['value'])) {
                        $this->startElement($index);
                        $this->addArray($data);
                        $this->endElement();
                    } else {
                        $this->setElement($index, $data['value'], $data['attributes'], $data['cdata']);
                    }
                } else {
                    $this->setElement($index, false, $data);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * End element
     * @param bool $full_end
     */
    public function endElement($full_end = true)
    {
        if ($full_end) {
            parent::fullEndElement();
        } else {
            parent::endElement();
        }
    }

    /**
     * Get current document
     *
     * @param bool $flush
     * @return string
     */
    public function getDocument($flush = true)
    {
        $this->endDocument();
        return $this->outputMemory($flush);
    }

    /**
     * Display XML
     * @return XML
     */
    public function output()
    {
        Debug::getInstance()->supress();
        header('Content-Type: text/xml');
        echo $this->getDocument();
    }

    /**
     * Set an XML element
     *
     * @param string $name
     * @param mixed $value
     * @param string $attributes
     * @param mixed $cdata
     */
    public function setElement($name, $value = null, $attributes = false, $cdata = true)
    {
        $this->startElement($name, $attributes);
        if ($cdata) {
            $this->writeCData($value);
        } else {
            $this->text($value);
        }
        $this->endElement(true);
    }

    /**
     * Start a new element
     *
     * @param string $name
     * @param string $attributes
     */
    public function startElement($name, $attributes = false)
    {
        parent::startElement($name);
        if (is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                parent::writeAttribute($attribute, $value);
            }
        }
    }
}
