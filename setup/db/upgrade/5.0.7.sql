ALTER TABLE `CubeCart_inventory` CHANGE COLUMN `updated` `updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ

ALTER TABLE `CubeCart_filemanager` CHANGE `filepath` `filepath` VARCHAR(255) default NULL; #EOQ

UPDATE `CubeCart_filemanager` SET `filepath` = NULL WHERE `filepath` = ''; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'de-DE' WHERE `doc_lang` = 'de'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'es-ES' WHERE `doc_lang` = 'es'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = '{%DEFAULT_EN-XX%}' WHERE `doc_lang` = 'en'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'fr-FR' WHERE `doc_lang` = 'fr'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'nl-NL' WHERE `doc_lang` = 'nl'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'cn-CN' WHERE `doc_lang` = 'cn'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'dk-DK' WHERE `doc_lang` = 'dk'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'fi-FI' WHERE `doc_lang` = 'fi'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'it-IT' WHERE `doc_lang` = 'it'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'no-NO' WHERE `doc_lang` = 'no'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'pl-PL' WHERE `doc_lang` = 'pl'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'pt-PT' WHERE `doc_lang` = 'pt'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'se-SE' WHERE `doc_lang` = 'se'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'sk-SK' WHERE `doc_lang` = 'sk'; #EOQ

UPDATE `CubeCart_documents` SET `doc_lang` = 'en-US' WHERE `doc_lang` = ''; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'de-DE' WHERE `language` = 'de'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'es-ES' WHERE `language` = 'es'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = '{%DEFAULT_EN-XX%}' WHERE `language` = 'en'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'fr-FR' WHERE `language` = 'fr'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'nl-NL' WHERE `language` = 'nl'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'cn-CN' WHERE `language` = 'cn'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'dk-DK' WHERE `language` = 'dk'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'fi-FI' WHERE `language` = 'fi'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'it-IT' WHERE `language` = 'it'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'no-NO' WHERE `language` = 'no'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'pl-PL' WHERE `language` = 'pl'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'pt-PT' WHERE `language` = 'pt'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'se-SE' WHERE `language` = 'se'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'sk-SK' WHERE `language` = 'sk'; #EOQ

UPDATE `CubeCart_inventory_language` SET `language` = 'en-US' WHERE `language` = ''; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'de-DE' WHERE `language` = 'de'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'es-ES' WHERE `language` = 'es'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = '{%DEFAULT_EN-XX%}' WHERE `language` = 'en'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'fr-FR' WHERE `language` = 'fr'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'nl-NL' WHERE `language` = 'nl'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'cn-CN' WHERE `language` = 'cn'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'dk-DK' WHERE `language` = 'dk'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'fi-FI' WHERE `language` = 'fi'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'it-IT' WHERE `language` = 'it'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'no-NO' WHERE `language` = 'no'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'pl-PL' WHERE `language` = 'pl'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'pt-PT' WHERE `language` = 'pt'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'se-SE' WHERE `language` = 'se'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'sk-SK' WHERE `language` = 'sk'; #EOQ

UPDATE `CubeCart_category_language` SET `language` = 'en-US' WHERE `language` = ''; #EOQ