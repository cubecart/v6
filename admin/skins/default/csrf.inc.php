<?php
/* Map of GET variable that require CSRF check */
$csrf_maps = array(
    /* START DELETE */
    array('_g'=>'settings','node'=>'index','action'=>'delete','admin_id'=>false), // Delete admin
    array('_g'=>'filemanager','delete'=>false), // Delete files & folders
    array('_g'=>'settings','node'=>'geo','delete'=>'country','id'=>false), // Delete country
    array('_g'=>'settings','node'=>'geo','delete'=>'zone','id'=>false), // Delete zones
    array('_g'=>'customers','action'=>'delete','customer_id'=>false), // Delete customer
    array('_g'=>'customers','action'=>'edit','customer_id'=>false, 'delete_addr'=>false), // Delete address
    array('_g'=>'orders','delete'=>false), // Delete order
    array('_g'=>'orders','action'=>'edit','order_id'=>false, 'delete-note'=>false), // Delete order note
    array('_g'=>'customers','node'=>'email','action'=>'delete','newsletter_id'=>false), // Delete newsletter
    array('_g'=>'customers','node'=>'subscribers','delete'=>false), // Delete subscriber
    array('_g'=>'categories','delete'=>false), // Delete category
    array('_g'=>'products','node'=>'index','delete'=>false), // Delete product
    array('_g'=>'products','action'=>'edit','product_id'=>false,'delete_review'=>false), // Delete product review
    array('_g'=>'products','node'=>'reviews','delete'=>false), // Delete review
    array('_g'=>'products','node'=>'options','delete'=>'group','id'=>false), // Delete option group
    array('_g'=>'products','node'=>'options','delete'=>'attribute','id'=>false), // Delete option attribute
    array('_g'=>'products','node'=>'options','delete'=>'set','id'=>false), // Delete option set
    array('_g'=>'products','node'=>'coupons','delete'=>false), // Delete promo code
    array('_g'=>'products','node'=>'manufacturers','delete'=>false), // Delete manufacturer
    array('_g'=>'documents','delete'=>false), // Delete document
    array('_g'=>'documents','node'=>'email','action'=>'delete','type'=>'template','template_id'=>false), // Delete email template
    array('_g'=>'settings','node'=>'hooks','delete_snippet'=>false), // Delete code snippet
    array('_g'=>'settings','node'=>'currency','delete'=>false), // Delete currency
    array('_g'=>'settings','node'=>'tax','delete_class'=>false), // Delete tax class
    array('_g'=>'settings','node'=>'tax','delete_detail'=>false), // Delete tax detail
    array('_g'=>'settings','node'=>'tax','delete_rule'=>false), // Delete tax rule
    array('_g'=>'settings','node'=>'language','delete'=>false), // Delete language
    array('_g'=>'plugins','type'=>false,'module'=>false,'delete'=>'1'), // Delete extension
    /* END DELETE */
    array('_g'=>'customers','node'=>'email','action'=>'send','newsletter_id'=>false),
    array('_g'=>'logout')
);
