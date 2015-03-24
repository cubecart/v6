UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'example-orbit', 'bxslider'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'data-orbit=""', ''); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'orbit-caption', 'hide'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'slide1.jpg"', ' title="Promotional Message One"'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'slide3.jpg"', ' title="Promotional Message Two"'); #EOQ
ALTER TABLE  `CubeCart_order_summary` ADD UNIQUE (`cart_order_id`); #EOQ