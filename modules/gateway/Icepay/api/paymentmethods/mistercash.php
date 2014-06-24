<?php

/**
 *  ICEPAY Basicmode API 2
 *  MisterCash library
 *
 *  @version 1.0.0
 *  @author Sander van Tilburg
 *  @copyright Copyright (c) 2012, ICEPAY
 *
 */

class Icepay_Paymentmethod_Mistercash extends Icepay_Paymentmethod {
    public      $_version       = "1.0.0";
    public      $_method        = "MISTERCASH";
    public      $_readable_name = "MisterCash";
    public      $_issuer        = array('MISTERCASH');
    public      $_country       = array('BE','NL');
    public      $_language      = array('DE','EN','ES','FR','IT','JA','NL');
    public      $_currency      = array('EUR');
    public      $_amount        = array(
                                    'minimum'   => 200,
                                    'maximum'   => 200000
                                    );
}


?>
