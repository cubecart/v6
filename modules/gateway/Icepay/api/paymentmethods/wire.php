<?php

/**
 *  ICEPAY Basicmode API 2
 *  WireTransfer library
 *
 *  @version 1.0.1
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */

class Icepay_Paymentmethod_Wire extends Icepay_Paymentmethod {
    public      $_version       = "1.0.1";
    public      $_method        = "WIRE";
    public      $_readable_name = "Wire Transfer";
    public      $_issuer        = array('DEFAULT');
    public      $_country       = array('00');
    public      $_language      = array('00');
    public      $_currency      = array('00');
    public      $_amount        = array(
                                    'minimum'   => 30,
                                    'maximum'   => 1000000
                                    );
}


?>
