<?php

/**
 *  ICEPAY Basicmode API 2
 *  PayPal library
 *
 *  @version 1.0.1
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */

class Icepay_Paymentmethod_Paypal extends Icepay_Paymentmethod {
    public      $_version       = "1.0.1";
    public      $_method        = "PAYPAL";
    public      $_readable_name = "PayPal";
    public      $_issuer        = array('DEFAULT');
    public      $_country       = array('00');
    public      $_language      = array('00');
    public      $_currency      = array('EUR', 'USD', 'GBP');
    public      $_amount        = array(
                                    'minimum'   => 30,
                                    'maximum'   => 1000000
                                    );
}


?>
