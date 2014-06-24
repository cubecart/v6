<?php

/**
 *  ICEPAY Basicmode API 2
 *  DirectDebit library
 *
 *  @version 1.0.1
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */

class Icepay_Paymentmethod_Ddebit extends Icepay_Paymentmethod {
    public      $_version       = "1.0.1";
    public      $_method        = "DDEBIT";
    public      $_readable_name = "Direct Debit";
    public      $_issuer        = array('INCASSO');
    public      $_country       = array('NL');
    public      $_language      = array('NL', 'EN');
    public      $_currency      = array('EUR');
    public      $_amount        = array(
                                    'minimum'   => 1,
                                    'maximum'   => 200000
                                    );
}


?>
