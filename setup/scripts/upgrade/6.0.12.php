<?php
$addresses = $db->select('CubeCart_addressbook', array('state', 'address_id'));
if ($addresses) {
    foreach ($addresses as $address) {
        if (strlen($address['state']) == 2 && !is_numeric($address['state'])) {
            $state = strtoupper($address['state']);
            $match = $db->select('CubeCart_geo_zone', array('id'), array('abbrev' => $state));
            if ($match && $match[0]['id'] > 0) {
                $db->update('CubeCart_addressbook', array('state' => $match[0]['id']), array('address_id' => $address['address_id']));
            }
        } elseif (!is_numeric($address['state'])) {
            $state = $address['state'];
            $match = $db->select('CubeCart_geo_zone', array('id'), array('name' => $state));
            if ($match && $match[0]['id'] > 0) {
                $db->update('CubeCart_addressbook', array('state' => $match[0]['id']), array('address_id' => $address['address_id']));
            }
        }
    }
}
