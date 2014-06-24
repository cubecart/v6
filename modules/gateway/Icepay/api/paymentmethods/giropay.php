<?php

/**
 *  ICEPAY Basicmode API 2
 *  Giropay library
 *
 *  @version 1.0.1
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */

class Icepay_Paymentmethod_Giropay extends Icepay_Paymentmethod {
    public      $_version       = "1.0.1";
    public      $_method        = "GIROPAY";
    public      $_readable_name = "Giropay";
    public      $_issuer        = array('DEFAULT');
    public      $_country       = array('DE');
    public      $_language      = array('DE');
    public      $_currency      = array('EUR');
    public      $_amount        = array(
                                    'minimum'   => 30,
                                    'maximum'   => 1000000
                                    );
}


?>
