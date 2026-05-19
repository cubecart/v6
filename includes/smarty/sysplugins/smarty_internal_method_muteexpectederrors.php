<?php
/**
 * Smarty Method MuteExpectedErrors — compatibility shim.
 *
 * Removed in Smarty 4 (Smarty_Internal_ErrorHandler covers it now). Old
 * CubeCart plugins (e.g. ebay_sales) still call $smarty->muteExpectedErrors(),
 * which would otherwise throw "undefined extension class".
 */
class Smarty_Internal_Method_MuteExpectedErrors
{
    public $objMap = 7;  // Smarty + Smarty_Internal_Data + Smarty_Internal_Template

    public function muteExpectedErrors($obj)
    {
        return $obj;
    }
}
