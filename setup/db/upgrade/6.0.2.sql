UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'example-orbit', 'bxslider'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'data-orbit=""', ''); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'orbit-caption', 'hide'); #EOQ