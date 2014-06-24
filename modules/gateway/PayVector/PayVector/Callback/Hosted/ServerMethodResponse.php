<?php

function StoreServerMethodResponse($_POST) {

    if (!$GLOBALS['db']->misc(PayVectorSQL::TableExists(PayVectorSQL::tblHPF_SERVER_Results))) {
        $GLOBALS['db']->misc(PayVectorSQL::createHPF_RESULTS());
    } else {
        $GLOBALS['db']->misc(PayVectorSQL::deleteHPF_HistoricResults());
    }

    $results = $GLOBALS['db']->misc(PayVectorSQL::insertHPF_SERVER_Results($_POST));

    return $results;
}

function RetrieveServerMethodResponse($CrossReference) {

    $results = $GLOBALS['db']->misc(PayVectorSQL::selectHPF_SERVER_Results($CrossReference));
    
    // the CubeCart initialises the return array via "array()", which create zero index array entry which is then filled with the transaction results. This just shuffles them from the zero index item to the root of the array
     
    $results = $results[0];
    
    return $results;

}

?>
