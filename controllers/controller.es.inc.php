<?php
$o = array();
if(!empty($_GET['q'])) {
    require_once(CC_ROOT_DIR.'/classes/elasticsearchhandler.class.php');
    $es = new ElasticsearchHandler;
    $es->body(array('keywords' => $_GET['q']));

    if($result = $es->search(1, 20)) {
        foreach($result["hits"]["hits"] as $p) {
            $o[] = array(
                'product_id' => $p['_id'],
                'name' => $p["_source"]["name"],
                'thumbnail' => ''
            );
        }
    }
}
die(json_encode($o));