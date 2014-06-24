<?php

function StoreServerMethodResponse($_POST) {

    if (!$GLOBALS['db']->misc(IridiumSQL::TableExists(IridiumSQL::tblHPF_SERVER_Results))) {
        $GLOBALS['db']->misc(IridiumSQL::createHPF_RESULTS());
    } else {
        $GLOBALS['db']->misc(IridiumSQL::deleteHPF_HistoricResults());
    }

    $results = $GLOBALS['db']->misc(IridiumSQL::insertHPF_SERVER_Results($_POST));

    return $results;
}

function RetrieveServerMethodResponse($CrossReference) {

    $results = $GLOBALS['db']->misc(IridiumSQL::selectHPF_SERVER_Results($CrossReference));
    
    // the CubeCart initialises the return array via "array()", which create zero index array entry which is then filled with the transaction results. This just shuffles them from the zero index item to the root of the array
     
    $results = $results[0];
    
    return $results;

}

?>
