<?php

/**
 *  ICEPAY Basicmode API 2
 *  Phone SMS library
 *
 *  @version 1.0.1
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */

class Icepay_Paymentmethod_Sms extends Icepay_Paymentmethod {
    public      $_version       = "1.0.1";
    public      $_method        = "SMS";
    public      $_readable_name = "SMS Text";
    public      $_issuer        = array('DEFAULT');
    public      $_country       = array('00');
    public      $_language      = array('EN','NL');
    public      $_currency      = array('00');
    public      $_amount        = array(
                                    'minimum'   => 30,
                                    'maximum'   => 1000000
                                    );
}


?>
