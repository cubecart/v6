INSERT INTO `CubeCart_tax_class` (`id`, `tax_name`) VALUES (1, 'Standard Rate'), (2, 'Reduced Rate'), (3, 'Zero Rate'); #EOQ
INSERT INTO `CubeCart_tax_details` (`id`, `name`, `display`, `status`) VALUES (1, 'VAT', 'VAT', 0); #EOQ
INSERT INTO `CubeCart_tax_rates` (`type_id`, `details_id`, `country_id`, `tax_percent`, `goods`, `shipping`, `active`) VALUES (1, 1, 826, '20', 1, 1, 0), (2, 1, 826, '5.0', 1, 1, 0), (3, 1, 826, '0.0', 1, 1, 0); #EOQ

INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(1, 'United States dollar', 'USD', 840, '$', '', 1.16180, 2, 1421884800, 1, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(2, 'Japanese yen', 'JPY', 392, '¥', '', 136.70000, 0, 1421884800, 1, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(3, 'Bulgarian lev', 'BGN', 100, '', 'BGN', 1.95580, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(4, 'Czech koruna', 'CZK', 203, '', 'CZK', 27.90000, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(5, 'Danish krone', 'DKK', 208, 'kr', '', 7.44540, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(6, 'Estonian kroon', 'EEK', 233, 'kr', '', 0.00000, 2, 0, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(7, 'Pound Sterling', 'GBP', 826, '£', '', 0.76440, 2, 1421884800, 1, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(8, 'Hungarian forint', 'HUF', 348, 'Ft', '', 315.56000, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(9, 'Lithuanian litas', 'LTL', 440, 'Lt', '', 0.00000, 2, 0, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(10, 'Latvian lat', 'LVL', 428, 'Ls', '', 0.00000, 2, 0, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(11, 'Polish zloty', 'PLN', 985, 'zl', '', 4.29970, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(12, 'Romanian leu', 'RON', 642, 'lei', '', 4.50230, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(13, 'Swedish krona', 'SEK', 752, 'kr', '', 9.44050, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(14, 'Swiss franc', 'CHF', 756, 'CHF', '', 0.99430, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(15, 'Norwegian krone', 'NOK', 578, 'kr', '', 8.83100, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(16, 'Croatian kuna', 'HRK', 191, 'kn', '', 7.69850, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(17, 'Russian ruble', 'RUB', 643, '', 'RUB', 74.78000, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(18, 'Turkish lira', 'TRY', 792, 'YTL', '', 2.71280, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(19, 'Brazilian real', 'BRL', 986, 'R$', '', 2.99550, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(20, 'Canadian Dollar', 'CAD', 124, '$', '', 1.43530, 2, 1421884800, 1, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(21, 'Chinese yuan', 'CNY', 156, '元', '', 7.21440, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(22, 'Hong Kong dollar', 'HKD', 344, '圓', '', 9.00740, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(23, 'Mexican peso', 'MXN', 484, '$', '', 17.06390, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(24, 'Malaysian ringgit', 'MYR', 458, 'RM', '', 4.16170, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(25, 'New Zealand dollar', 'NZD', 554, '$', '', 1.53440, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(26, 'Philippine peso', 'PHP', 608, 'Php', '', 51.37300, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(27, 'Singapore dollar', 'SGD', 702, '$', '', 1.54790, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(28, 'Thai baht', 'THB', 764, '฿', '', 37.85100, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(29, 'South African rand', 'ZAR', 710, 'R', '', 13.33670, 2, 1421884800, 0, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(30, 'Euro', 'EUR', 978, '€', '', 1.00000, 2, 1421884800, 1, '.', ','); #EOQ
INSERT INTO `CubeCart_currency` (`currency_id`, `name`, `code`, `iso`, `symbol_left`, `symbol_right`, `value`, `decimal_places`, `updated`, `active`, `symbol_decimal`, `symbol_thousand`) VALUES(31, 'Australian Dollar', 'AUD', 036, '$', '', 1.42990, 2, 1421884800, 1, '.', ','); #EOQ

INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(1, 226, 'AL', 'Alabama'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(2, 226, 'AK', 'Alaska'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(3, 226, 'AS', 'American Samoa'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(4, 226, 'AZ', 'Arizona'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(5, 226, 'AR', 'Arkansas'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(6, 226, 'AF', 'Armed Forces Africa'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(7, 226, 'AA', 'Armed Forces Americas'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(8, 226, 'AC', 'Armed Forces Canada'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(9, 226, 'AE', 'Armed Forces Europe'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(10, 226, 'AM', 'Armed Forces Middle East'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(11, 226, 'AP', 'Armed Forces Pacific'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(12, 226, 'CA', 'California'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(13, 226, 'CO', 'Colorado'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(14, 226, 'CT', 'Connecticut'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(15, 226, 'DE', 'Delaware'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(16, 226, 'DC', 'District of Columbia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(17, 226, 'FM', 'Federated States Of Micronesia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(18, 226, 'FL', 'Florida'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(19, 226, 'GA', 'Georgia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(20, 226, 'GU', 'Guam'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(21, 226, 'HI', 'Hawaii'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(22, 226, 'ID', 'Idaho'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(23, 226, 'IL', 'Illinois'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(24, 226, 'IN', 'Indiana'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(25, 226, 'IA', 'Iowa'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(26, 226, 'KS', 'Kansas'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(27, 226, 'KY', 'Kentucky'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(28, 226, 'LA', 'Louisiana'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(29, 226, 'ME', 'Maine'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(30, 226, 'MH', 'Marshall Islands'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(31, 226, 'MD', 'Maryland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(32, 226, 'MA', 'Massachusetts'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(33, 226, 'MI', 'Michigan'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(34, 226, 'MN', 'Minnesota'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(35, 226, 'MS', 'Mississippi'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(36, 226, 'MO', 'Missouri'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(37, 226, 'MT', 'Montana'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(38, 226, 'NE', 'Nebraska'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(39, 226, 'NV', 'Nevada'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(40, 226, 'NH', 'New Hampshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(41, 226, 'NJ', 'New Jersey'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(42, 226, 'NM', 'New Mexico'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(43, 226, 'NY', 'New York'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(44, 226, 'NC', 'North Carolina'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(45, 226, 'ND', 'North Dakota'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(46, 226, 'MP', 'Northern Mariana Islands'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(47, 226, 'OH', 'Ohio'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(48, 226, 'OK', 'Oklahoma'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(49, 226, 'OR', 'Oregon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(50, 226, 'PW', 'Palau'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(51, 226, 'PA', 'Pennsylvania'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(52, 226, 'PR', 'Puerto Rico'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(53, 226, 'RI', 'Rhode Island'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(54, 226, 'SC', 'South Carolina'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(55, 226, 'SD', 'South Dakota'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(56, 226, 'TN', 'Tennessee'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(57, 226, 'TX', 'Texas'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(58, 226, 'UT', 'Utah'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(59, 226, 'VT', 'Vermont'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(60, 226, 'VI', 'Virgin Islands'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(61, 226, 'VA', 'Virginia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(62, 226, 'WA', 'Washington'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(63, 226, 'WV', 'West Virginia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(64, 226, 'WI', 'Wisconsin'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(65, 226, 'WY', 'Wyoming'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(66, 38, 'AB', 'Alberta'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(67, 38, 'BC', 'British Columbia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(68, 38, 'MB', 'Manitoba'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(69, 38, 'NF', 'Newfoundland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(70, 38, 'NB', 'New Brunswick'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(71, 38, 'NS', 'Nova Scotia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(72, 38, 'NT', 'Northwest Territories'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(73, 38, 'NU', 'Nunavut'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(74, 38, 'ON', 'Ontario'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(75, 38, 'PE', 'Prince Edward Island'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(76, 38, 'QC', 'Quebec'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(77, 38, 'SK', 'Saskatchewan'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(78, 38, 'YT', 'Yukon Territory'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(79, 80, 'NDS', 'Niedersachsen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(80, 80, 'BAW', 'Baden-Württemberg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(81, 80, 'BAY', 'Bayern'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(82, 80, 'BER', 'Berlin'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(83, 80, 'BRG', 'Brandenburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(84, 80, 'BRE', 'Bremen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(85, 80, 'HAM', 'Hamburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(86, 80, 'HES', 'Hessen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(87, 80, 'MEC', 'Mecklenburg-Vorpommern'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(88, 80, 'NRW', 'Nordrhein-Westfalen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(89, 80, 'RHE', 'Rheinland-Pfalz'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(90, 80, 'SAR', 'Saarland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(91, 80, 'SAS', 'Sachsen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(92, 80, 'SAC', 'Sachsen-Anhalt'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(93, 80, 'SCN', 'Schleswig-Holstein'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(94, 80, 'THE', 'Thüringen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(95, 14, 'WIE', 'Wien'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(96, 14, 'NO', 'NiederÖsterreich'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(97, 14, 'OO', 'OberÖsterreich'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(98, 14, 'SB', 'Salzburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(99, 14, 'KN', 'Kärnten'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(100, 14, 'ST', 'Steiermark'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(101, 14, 'TI', 'Tirol'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(102, 14, 'BL', 'Burgenland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(103, 14, 'VB', 'Voralberg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(104, 206, 'AG', 'Aargau'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(105, 206, 'AI', 'Appenzell Innerrhoden'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(106, 206, 'APP', 'Appenzell Ausserrhoden'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(107, 206, 'BE', 'Bern'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(108, 206, 'BLA', 'Basel-Landschaft'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(109, 206, 'BS', 'Basel-Stadt'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(110, 206, 'FR', 'Freiburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(111, 206, 'GE', 'Genf'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(112, 206, 'GL', 'Glarus'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(113, 206, 'JUB', 'Graubünden'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(114, 206, 'JU', 'Jura'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(115, 206, 'LU', 'Luzern'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(116, 206, 'NEU', 'Neuenburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(117, 206, 'NW', 'Nidwalden'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(118, 206, 'OW', 'Obwalden'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(119, 206, 'SG', 'St. Gallen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(120, 206, 'SH', 'Schaffhausen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(121, 206, 'SO', 'Solothurn'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(122, 206, 'SZ', 'Schwyz'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(123, 206, 'TG', 'Thurgau'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(124, 206, 'TE', 'Tessin'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(125, 206, 'UR', 'Uri'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(126, 206, 'VD', 'Waadt'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(127, 206, 'VS', 'Wallis'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(128, 206, 'ZG', 'Zug'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(129, 206, 'ZH', 'Zürich'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(130, 199, 'ACOR', 'A Coruña'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(131, 199, 'ALAV', 'Alava'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(132, 199, 'ALBA', 'Albacete'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(133, 199, 'ALIC', 'Alicante'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(134, 199, 'ALME', 'Almeria'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(135, 199, 'ASTU', 'Asturias'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(136, 199, 'AVIL', 'Avila'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(137, 199, 'BADA', 'Badajoz'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(138, 199, 'BALE', 'Baleares'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(139, 199, 'BARC', 'Barcelona'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(140, 199, 'BURG', 'Burgos'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(141, 199, 'CACE', 'Caceres'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(142, 199, 'CADI', 'Cadiz'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(143, 199, 'CANT', 'Cantabria'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(144, 199, 'CAST', 'Castellon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(145, 199, 'CEUT', 'Ceuta'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(146, 199, 'CIUD', 'Ciudad Real'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(147, 199, 'CORD', 'Cordoba'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(148, 199, 'CUEN', 'Cuenca'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(149, 199, 'GIRO', 'Girona'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(150, 199, 'GRAN', 'Granada'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(151, 199, 'GUAD', 'Guadalajara'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(152, 199, 'GUIP', 'Guipuzcoa'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(153, 199, 'HUEL', 'Huelva'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(154, 199, 'HUES', 'Huesca'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(155, 199, 'JAEN', 'Jaen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(156, 199, 'LAR', 'La Rioja'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(157, 199, 'LAS', 'Las Palmas'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(158, 199, 'LEON', 'Leon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(159, 199, 'LLEI', 'Lleida'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(160, 199, 'LUGO', 'Lugo'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(161, 199, 'MADR', 'Madrid'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(162, 199, 'MALA', 'Malaga'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(163, 199, 'MELI', 'Melilla'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(164, 199, 'MURC', 'Murcia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(165, 199, 'NAVA', 'Navarra'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(166, 199, 'OURE', 'Ourense'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(167, 199, 'PALE', 'Palencia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(168, 199, 'PONT', 'Pontevedra'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(169, 199, 'SALA', 'Salamanca'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(170, 199, 'SANT', 'Santa Cruz de Tenerife'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(171, 199, 'SEGO', 'Segovia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(172, 199, 'SEVI', 'Sevilla'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(173, 199, 'SORI', 'Soria'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(174, 199, 'TARR', 'Tarragona'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(175, 199, 'TERU', 'Teruel'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(176, 199, 'TOLE', 'Toledo'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(177, 199, 'VALE', 'Valencia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(178, 199, 'VALL', 'Valladolid'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(179, 199, 'VIZC', 'Vizcaya'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(180, 199, 'ZAMO', 'Zamora'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(181, 199, 'ZARA', 'Zaragoza'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(415, 103, 'CW', 'Carlow'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(416, 103, 'CN', 'Cavan'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(417, 103, 'CE', 'Clare'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(418, 103, 'C', 'Cork'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(419, 103, 'DL', 'Donegal'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(420, 103, 'D', 'Dublin'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(421, 103, 'G', 'Galway'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(422, 103, 'KY', 'Kerry'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(423, 103, 'KE', 'Kildare'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(424, 103, 'KK', 'Kilkenny'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(425, 103, 'LS', 'Laois'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(426, 103, 'LM', 'Leitrim'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(427, 103, 'LK', 'Limerick'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(428, 103, 'LD', 'Longford'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(429, 103, 'LH', 'Louth'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(430, 103, 'MO', 'Mayo'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(431, 103, 'MH', 'Meath'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(432, 103, 'MN', 'Monaghan'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(433, 103, 'OY', 'Offaly'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(434, 103, 'RN', 'Roscommon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(435, 103, 'SO', 'Sligo'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(436, 103, 'TA', 'Tipperary'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(437, 103, 'WD', 'Waterford'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(438, 103, 'WH', 'Westmeath'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(439, 103, 'WX', 'Wexford'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(440, 103, 'WW', 'Wicklow'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(441, 225, 'AVN', 'Avon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(442, 225, 'BDF', 'Bedfordshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(443, 225, 'BRK', 'Berkshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(444, 225, 'BKM', 'Buckinghamshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(445, 225, 'CAM', 'Cambridgeshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(446, 225, 'CHS', 'Cheshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(447, 225, 'CLV', 'Cleveland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(448, 225, 'CON', 'Cornwall'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(449, 225, 'CUL', 'Cumberland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(450, 225, 'CMA', 'Cumbria'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(451, 225, 'DBY', 'Derbyshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(452, 225, 'DEV', 'Devon'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(453, 225, 'DOR', 'Dorset'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(454, 225, 'DUR', 'County Durham'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(455, 225, 'ESS', 'Essex'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(456, 225, 'GLS', 'Gloucestershire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(457, 225, 'HAM', 'Hampshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(458, 225, 'HWR', 'Hereford and Worcester'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(459, 225, 'HEF', 'Herefordshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(460, 225, 'HRT', 'Hertfordshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(461, 225, 'HUM', 'Humberside'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(462, 225, 'HUN', 'Huntingdonshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(463, 225, 'IOW', 'Isle of Wight'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(464, 225, 'KEN', 'Kent'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(465, 225, 'LAN', 'Lancashire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(466, 225, 'LEI', 'Leicestershire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(467, 225, 'LIN', 'Lincolnshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(468, 225, 'GTM', 'Greater Manchester'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(469, 225, 'GTL', 'Greater London'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(470, 225, 'MSY', 'Merseyside'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(471, 225, 'MDX', 'Middlesex'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(472, 225, 'NFK', 'Norfolk'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(473, 225, 'NTH', 'Northamptonshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(474, 225, 'NBL', 'Northumberland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(475, 225, 'NTT', 'Nottinghamshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(476, 225, 'OXF', 'Oxfordshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(477, 225, 'RUT', 'Rutland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(478, 225, 'SAL', 'Shropshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(479, 225, 'SOM', 'Somerset'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(480, 225, 'STS', 'Staffordshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(481, 225, 'SFK', 'Suffolk'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(482, 225, 'SRY', 'Surrey'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(484, 225, 'TWR', 'Tyne and Wear'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(485, 225, 'WAR', 'Warwickshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(486, 225, 'WMD', 'West Midlands'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(487, 225, 'WES', 'Westmorland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(488, 225, 'WIL', 'Wiltshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(489, 225, 'WOR', 'Worcestershire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(491, 225, 'ABD', 'Aberdeenshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(492, 225, 'ANS', 'Angus'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(493, 225, 'ARL', 'Argyll'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(494, 225, 'AYR', 'Ayrshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(495, 225, 'BAN', 'Banffshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(496, 225, 'BEW', 'Berwickshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(497, 225, 'BUT', 'Bute'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(498, 225, 'CAI', 'Caithness'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(499, 225, 'CLK', 'Clackmannanshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(500, 225, 'CRO', 'Cromartyshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(501, 225, 'DFS', 'Dumfriesshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(502, 225, 'DNB', 'Dunbartonshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(503, 225, 'ELN', 'East Lothian'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(504, 225, 'FIF', 'Fife'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(505, 225, 'INV', 'Inverness-shire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(506, 225, 'KRS', 'Kinross-shire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(507, 225, 'KKD', 'Kirkcudbrightshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(508, 225, 'LKS', 'Lanarkshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(509, 225, 'MLN', 'Midlothian'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(510, 225, 'MOR', 'Moray'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(511, 225, 'NAI', 'Nairnshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(512, 225, 'OKI', 'Orkney'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(513, 225, 'PEE', 'Peeblesshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(514, 225, 'PER', 'Perthshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(515, 225, 'RFW', 'Renfrewshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(516, 225, 'ROC', 'Ross'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(517, 225, 'ROX', 'Roxburghshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(518, 225, 'SEL', 'Selkirkshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(519, 225, 'SHI', 'Shetland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(520, 225, 'STI', 'Stirlingshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(521, 225, 'SUT', 'Sutherland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(522, 225, 'WLN', 'West Lothian'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(523, 225, 'WIG', 'Wigtownshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(524, 225, 'AGY', 'Anglesey'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(525, 225, 'BRN', 'Brecknockshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(526, 225, 'CAE', 'Caernarfonshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(527, 225, 'CAD', 'Ceredigion'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(528, 225, 'CRR', 'Carmarthenshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(529, 225, 'CLW', 'Clwyd'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(530, 225, 'DEN', 'Denbighshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(531, 225, 'DFD', 'Dyfed'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(532, 225, 'FLN', 'Flintshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(533, 225, 'GLA', 'Glamorgan'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(534, 225, 'GNT', 'Gwent'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(535, 225, 'GWN', 'Gwynedd'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(536, 225, 'MER', 'Merionethshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(537, 225, 'MON', 'Monmouthshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(538, 225, 'MGY', 'Montgomeryshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(539, 225, 'PEM', 'Pembrokeshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(540, 225, 'POW', 'Powys'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(541, 225, 'RAD', 'Radnorshire'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(542, 225, 'ANT', 'Antrim'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(543, 225, 'ARM', 'Armagh'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(544, 225, 'LDY', 'Londonderry'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(545, 225, 'DOW', 'Down'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(546, 225, 'FER', 'Fermanagh'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(547, 225, 'TYR', 'Tyrone'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(548, 150, 'DR', 'Drenthe'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(549, 150, 'FL', 'Flevoland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(550, 150, 'FR', 'Friesland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(551, 150, 'GLD', 'Gelderland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(552, 150, 'GR', 'Groningen'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(553, 150, 'LI', 'Limburg'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(554, 150, 'NB', 'Noord-Brabant'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(555, 150, 'NH', 'Noord-Holland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(556, 150, 'OV', 'Overijssel'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(557, 150, 'UT', 'Utrecht'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(558, 150, 'ZL', 'Zeeland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(559, 150, 'ZH', 'Zuid-Holland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(560, 13, 'ACT', 'Australian Capital Territory'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(561, 13, 'NSW', 'New South Wales'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(562, 13, 'NT', 'Northern Territory'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(563, 13, 'QLD', 'Queensland'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(564, 13, 'SA', 'South Australia'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(565, 13, 'TAS', 'Tasmania'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(566, 13, 'VIC', 'Victoria'); #EOQ
INSERT INTO `CubeCart_geo_zone` (`id`, `country_id`, `abbrev`, `name`) VALUES(567, 13, 'WA', 'Western Australia'); #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'East Sussex', `abbrev` = 'SXE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'West Sussex', `abbrev` = 'SXW'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'North Yorkshire', `abbrev` = 'YSN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'South Yorkshire', `abbrev` = 'YSS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'West Yorkshire', `abbrev` = 'YSW'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'East Yorkshire', `abbrev` = 'ERY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'London', `abbrev` = 'LND'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Catamarca', `abbrev` = 'CT'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Chaco', `abbrev` = 'CC'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Chubut', `abbrev` = 'CH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Ciudad de Buenos Aires', `abbrev` = 'DF'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Corrientes', `abbrev` = 'CN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Córdoba', `abbrev` = 'CB'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Entre Ríos', `abbrev` = 'ER'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Formosa', `abbrev` = 'FM'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Jujuy', `abbrev` = 'JY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'La Pampa', `abbrev` = 'LP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'La Rioja', `abbrev` = 'LR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Mendoza', `abbrev` = 'MZ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Misiones', `abbrev` = 'MN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Neuquén', `abbrev` = 'NQ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Río Negro', `abbrev` = 'RN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Salta', `abbrev` = 'SA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'San Juan', `abbrev` = 'SJ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'San Luis', `abbrev` = 'SL'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Santa Cruz', `abbrev` = 'SC'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Santa Fe', `abbrev` = 'SF'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Santiago del Estero', `abbrev` = 'SE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Tierra del Fuego', `abbrev` = 'TF'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 10, `name` = 'Tucumán', `abbrev` = 'TM'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Acre', `abbrev` = 'AC'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Alagoas', `abbrev` = 'AL'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Amapá', `abbrev` = 'AP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Amazonas', `abbrev` = 'AM'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Bahia', `abbrev` = 'BA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Ceará', `abbrev` = 'CE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Distrito Federal', `abbrev` = 'DF'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Espírito Santo', `abbrev` = 'ES'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Goiás', `abbrev` = 'GO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Maranhão', `abbrev` = 'MA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Mato Grosso', `abbrev` = 'MT'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Mato Grosso do Sul', `abbrev` = 'MS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Minas Gerais', `abbrev` = 'MG'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Paraná', `abbrev` = 'PR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Paraíba', `abbrev` = 'PB'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Pará', `abbrev` = 'PA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Pemambuco', `abbrev` = 'PE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Piauí', `abbrev` = 'PI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Rio Grande do Norte', `abbrev` = 'RN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Rio Grande do Sul', `abbrev` = 'RS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Rio de Janeiro', `abbrev` = 'RJ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Rondônia', `abbrev` = 'RO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Roraima', `abbrev` = 'RR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Santa Catarina', `abbrev` = 'SC'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Sergipe', `abbrev` = 'SE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'São Paulo', `abbrev` = 'SP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 30, `name` = 'Tocatins', `abbrev` = 'TO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Andaman and Nicobar Islands', `abbrev` = 'INAN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Andhra Pradesh', `abbrev` = 'INAP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Arunachal Pradesh', `abbrev` = 'INAR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Assam', `abbrev` = 'INAS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Bihar', `abbrev` = 'INBR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Chandigarh', `abbrev` = 'INCH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Chhattisgarh', `abbrev` = 'INCT'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Dadra and Nagar Haveli', `abbrev` = 'INDN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Daman and Diu', `abbrev` = 'INDD'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Delhi', `abbrev` = 'INDL'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Goa', `abbrev` = 'INGA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Gujarat', `abbrev` = 'INGJ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Haryana', `abbrev` = 'INHR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Himachal Pradesh', `abbrev` = 'INHP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Jammu and Kashmir', `abbrev` = 'INJK'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Jharkhand', `abbrev` = 'INJH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Karnataka', `abbrev` = 'INKA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Kerala', `abbrev` = 'INKL'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Lakshadweep', `abbrev` = 'INLD'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Madhya Pradesh', `abbrev` = 'INMP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Maharashtra', `abbrev` = 'INMH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Manipur', `abbrev` = 'INMN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Meghalaya', `abbrev` = 'INML'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Mizoram', `abbrev` = 'INMZ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Nagaland', `abbrev` = 'INNL'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Odisha', `abbrev` = 'INOR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Puducherry', `abbrev` = 'INPY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Punjab', `abbrev` = 'INPB'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Rajasthan', `abbrev` = 'INRJ'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Sikkim', `abbrev` = 'INSK'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Tamil Nadu', `abbrev` = 'INTN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Telangana', `abbrev` = 'INTG'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Tripura', `abbrev` = 'INTR'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'Uttar Pradesh', `abbrev` = 'INUP'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 99, `name` = 'West Bengal', `abbrev` = 'INWB'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Aichi', `abbrev` = 'AI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Akita', `abbrev` = 'AK'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Aomori', `abbrev` = 'AO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Chiba', `abbrev` = 'CH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Ehime', `abbrev` = 'EH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Fukui', `abbrev` = 'FI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Fukuoka', `abbrev` = 'FA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Gifu', `abbrev` = 'GI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Gunma', `abbrev` = 'GU'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Hiroshima', `abbrev` = 'HI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Hokkaido', `abbrev` = 'HO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Hyogo', `abbrev` = 'HY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Ibaraki', `abbrev` = 'IB'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Ishikawa', `abbrev` = 'IS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Iwate', `abbrev` = 'IW'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Kagawa', `abbrev` = 'KAG'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Kanagawa', `abbrev` = 'KAN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Kochi', `abbrev` = 'KO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Kumamoto', `abbrev` = 'KU'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Kyoto', `abbrev` = 'KY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Mie', `abbrev` = 'MIE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Miyagi', `abbrev` = 'MIGI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Miyazaki', `abbrev` = 'MIKI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Nagano', `abbrev` = 'NAO'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Nagasaki', `abbrev` = 'NAKI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Nara', `abbrev` = 'NARA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Niigata', `abbrev` = 'NI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Oita', `abbrev` = 'OI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Okayama', `abbrev` = 'OKA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Okinawa', `abbrev` = 'OKI'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Osaka', `abbrev` = 'OS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Saga', `abbrev` = 'SAGA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Saitama', `abbrev` = 'SAIT'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Shiga', `abbrev` = 'SHGA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Shimane', `abbrev` = 'SHNE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Shizuoka', `abbrev` = 'SHKA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Tochigi', `abbrev` = 'TOCH'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Tokushima', `abbrev` = 'TOKU'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Tokyo', `abbrev` = 'TOKY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Tottori', `abbrev` = 'TOTT'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Toyama', `abbrev` = 'TOY'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Wakayama', `abbrev` = 'WAK'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Yamagata', `abbrev` = 'YATA'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Yamaguchi', `abbrev` = 'YAGU'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 107, `name` = 'Yamanashi', `abbrev` = 'YANA'; #EOQ

INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (1, 'AF', 'Afghanistan', 'AFG', 004); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (2, 'AL', 'Albania', 'ALB', 008); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (3, 'DZ', 'Algeria', 'DZA', 012); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (4, 'AS', 'American Samoa', 'ASM', 016); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (5, 'AD', 'Andorra', 'AND', 020); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (6, 'AO', 'Angola', 'AGO', 244); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (7, 'AI', 'Anguilla', 'AIA', 660); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (8, 'AQ', 'Antarctica', 'ATA', 010); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (9, 'AG', 'Antigua and Barbuda', 'ATG', 028); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (10, 'AR', 'Argentina', 'ARG', 032); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (12, 'AW', 'Aruba', 'ABW', 533); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (13, 'AU', 'Australia', 'AUS', 036); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (14, 'AT', 'Austria', 'AUT', 040); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (15, 'AZ', 'Azerbaijan', 'AZE', 031); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (16, 'BS', 'Bahamas', 'BHS', 044); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (17, 'BH', 'Bahrain', 'BHR', 048); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (18, 'BD', 'Bangladesh', 'BGD', 050); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (19, 'BB', 'Barbados', 'BRB', 052); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (20, 'BY', 'Belarus', 'BLR', 112); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (21, 'BE', 'Belgium', 'BEL', 056); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (22, 'BZ', 'Belize', 'BLZ', 084); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (23, 'BJ', 'Benin', 'BEN', 204); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (24, 'BM', 'Bermuda', 'BMU', 060); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (25, 'BT', 'Bhutan', 'BTN', 064); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (26, 'BO', 'Bolivia', 'BOL', 068); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (27, 'BA', 'Bosnia and Herzegovina', 'BIH', 070); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (28, 'BW', 'Botswana', 'BWA', 072); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (29, 'BV', 'Bouvet Island', 'BVT', 074); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (30, 'BR', 'Brazil', 'BRA', 076); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (31, 'IO', 'British Indian Ocean Territory', 'IOT', 086); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (32, 'BN', 'Brunei Darussalam', 'BRN', 096); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (33, 'BG', 'Bulgaria', 'BGR', 100); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (34, 'BF', 'Burkina Faso', 'BFA', 854); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (35, 'BI', 'Burundi', 'BDI', 108); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (36, 'KH', 'Cambodia', 'KHM', 116); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (37, 'CM', 'Cameroon', 'CMR', 120); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (38, 'CA', 'Canada', 'CAN', 124); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (39, 'CV', 'Cape Verde', 'CPV', 132); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (40, 'KY', 'Cayman Islands', 'CYM', 136); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (41, 'CF', 'Central African Republic', 'CAF', 140); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (42, 'TD', 'Chad', 'TCD', 148); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (43, 'CL', 'Chile', 'CHL', 152); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (44, 'CN', 'China', 'CHN', 156); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (45, 'CX', 'Christmas Island', 'CXR', 162); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (46, 'CC', 'Cocos (Keeling) Islands', 'CCK', 166); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (47, 'CO', 'Colombia', 'COL', 170); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (48, 'KM', 'Comoros', 'COM', 174); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (49, 'CG', 'Congo', 'COG', 178); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (50, 'CD', 'Congo, the Democratic Republic of the', 'COD', 180); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (51, 'CK', 'Cook Islands', 'COK', 184); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (52, 'CR', 'Costa Rica', 'CRI', 188); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (53, 'CI', 'Cote D\'Ivoire', 'CIV', 384); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (54, 'HR', 'Croatia', 'HRV', 191); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (55, 'CU', 'Cuba', 'CUB', 192); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (56, 'CY', 'Cyprus', 'CYP', 196); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (57, 'CZ', 'Czech Republic', 'CZE', 203); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (58, 'DK', 'Denmark', 'DNK', 208); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (59, 'DJ', 'Djibouti', 'DJI', 262); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (60, 'DM', 'Dominica', 'DMA', 212); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (61, 'DO', 'Dominican Republic', 'DOM', 214); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (62, 'EC', 'Ecuador', 'ECU', 218); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (63, 'EG', 'Egypt', 'EGY', 818); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (64, 'SV', 'El Salvador', 'SLV', 222); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (65, 'GQ', 'Equatorial Guinea', 'GNQ', 226); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (66, 'ER', 'Eritrea', 'ERI', 232); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (67, 'EE', 'Estonia', 'EST', 233); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (68, 'ET', 'Ethiopia', 'ETH', 231); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (69, 'FK', 'Falkland Islands (Malvinas)', 'FLK', 238); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (70, 'FO', 'Faroe Islands', 'FRO', 234); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (71, 'FJ', 'Fiji', 'FJI', 242); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (72, 'FI', 'Finland', 'FIN', 246); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (73, 'FR', 'France', 'FRA', 250); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (74, 'GF', 'French Guiana', 'GUF', 254); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (75, 'PF', 'French Polynesia', 'PYF', 258); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (76, 'TF', 'French Southern Territories', 'ATF', 260); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (77, 'GA', 'Gabon', 'GAB', 266); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (78, 'GM', 'Gambia', 'GMB', 270); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (79, 'GE', 'Georgia', 'GEO', 268); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (80, 'DE', 'Germany', 'DEU', 276); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (81, 'GH', 'Ghana', 'GHA', 288); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (82, 'GI', 'Gibraltar', 'GIB', 292); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (83, 'GR', 'Greece', 'GRC', 300); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (84, 'GL', 'Greenland', 'GRL', 304); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (85, 'GD', 'Grenada', 'GRD', 308); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (86, 'GP', 'Guadeloupe', 'GLP', 312); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (87, 'GU', 'Guam', 'GUM', 316); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (88, 'GT', 'Guatemala', 'GTM', 320); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (89, 'GN', 'Guinea', 'GIN', 324); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (90, 'GW', 'Guinea-Bissau', 'GNB', 624); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (91, 'GY', 'Guyana', 'GUY', 328); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (92, 'HT', 'Haiti', 'HTI', 332); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (93, 'HM', 'Heard Island and Mcdonald Islands', 'HMD', 334); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (94, 'VA', 'Holy See (Vatican City State)', 'VAT', 336); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (95, 'HN', 'Honduras', 'HND', 340); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (96, 'HK', 'Hong Kong', 'HKG', 344); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (97, 'HU', 'Hungary', 'HUN', 348); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (98, 'IS', 'Iceland', 'ISL', 352); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (99, 'IN', 'India', 'IND', 356); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (100, 'ID', 'Indonesia', 'IDN', 360); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (101, 'IR', 'Iran, Islamic Republic of', 'IRN', 364); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (102, 'IQ', 'Iraq', 'IRQ', 368); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (103, 'IE', 'Ireland', 'IRL', 372); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (104, 'IL', 'Israel', 'ISR', 376); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (105, 'IT', 'Italy', 'ITA', 380); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (106, 'JM', 'Jamaica', 'JAM', 388); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (107, 'JP', 'Japan', 'JPN', 392); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (108, 'JO', 'Jordan', 'JOR', 400); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (109, 'KZ', 'Kazakhstan', 'KAZ', 398); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (110, 'KE', 'Kenya', 'KEN', 404); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (111, 'KI', 'Kiribati', 'KIR', 296); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (112, 'KP', 'Korea, Democratic People\'s Republic of', 'PRK', 408); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (113, 'KR', 'Korea, Republic of', 'KOR', 410); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (114, 'KW', 'Kuwait', 'KWT', 414); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (115, 'KG', 'Kyrgyzstan', 'KGZ', 417); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (116, 'LA', 'Lao People\'s Democratic Republic', 'LAO', 418); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (117, 'LV', 'Latvia', 'LVA', 428); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (118, 'LB', 'Lebanon', 'LBN', 422); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (119, 'LS', 'Lesotho', 'LSO', 426); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (121, 'LY', 'Libyan Arab Jamahiriya', 'LBY', 434); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (122, 'LI', 'Liechtenstein', 'LIE', 438); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (123, 'LT', 'Lithuania', 'LTU', 440); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (124, 'LU', 'Luxembourg', 'LUX', 442); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (125, 'MO', 'Macao', 'MAC', 446); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (126, 'MK', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (127, 'MG', 'Madagascar', 'MDG', 450); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (128, 'MW', 'Malawi', 'MWI', 454); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (129, 'MY', 'Malaysia', 'MYS', 458); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (130, 'MV', 'Maldives', 'MDV', 462); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (131, 'ML', 'Mali', 'MLI', 466); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (132, 'MT', 'Malta', 'MLT', 470); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (133, 'MH', 'Marshall Islands', 'MHL', 584); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (134, 'MQ', 'Martinique', 'MTQ', 474); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (135, 'MR', 'Mauritania', 'MRT', 478); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (136, 'MU', 'Mauritius', 'MUS', 480); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (137, 'YT', 'Mayotte', 'MYT', 175); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (138, 'MX', 'Mexico', 'MEX', 484); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (139, 'FM', 'Micronesia, Federated States of', 'FSM', 583); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (140, 'MD', 'Moldova, Republic of', 'MDA', 498); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (141, 'MC', 'Monaco', 'MCO', 492); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (142, 'MN', 'Mongolia', 'MNG', 496); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (143, 'MS', 'Montserrat', 'MSR', 500); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (144, 'MA', 'Morocco', 'MAR', 504); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (145, 'MZ', 'Mozambique', 'MOZ', 508); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (146, 'MM', 'Myanmar', 'MMR', 104); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (147, 'NA', 'Namibia', 'NAM', 516); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (148, 'NR', 'Nauru', 'NRU', 520); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (149, 'NP', 'Nepal', 'NPL', 524); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (150, 'NL', 'Netherlands', 'NLD', 528); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (151, 'AN', 'Netherlands Antilles', 'ANT', 530); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (152, 'NC', 'New Caledonia', 'NCL', 540); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (153, 'NZ', 'New Zealand', 'NZL', 554); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (154, 'NI', 'Nicaragua', 'NIC', 558); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (155, 'NE', 'Niger', 'NER', 562); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (156, 'NG', 'Nigeria', 'NGA', 566); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (157, 'NU', 'Niue', 'NIU', 570); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (158, 'NF', 'Norfolk Island', 'NFK', 574); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (159, 'MP', 'Northern Mariana Islands', 'MNP', 580); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (160, 'NO', 'Norway', 'NOR', 578); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (161, 'OM', 'Oman', 'OMN', 512); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (162, 'PK', 'Pakistan', 'PAK', 586); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (163, 'PW', 'Palau', 'PLW', 585); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (164, 'PS', 'Palestinian Territory, Occupied', 'PSE', 275); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (165, 'PA', 'Panama', 'PAN', 591); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (166, 'PG', 'Papua New Guinea', 'PNG', 598); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (167, 'PY', 'Paraguay', 'PRY', 600); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (168, 'PE', 'Peru', 'PER', 604); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (169, 'PH', 'Philippines', 'PHL', 608); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (170, 'PN', 'Pitcairn', 'PCN', 612); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (171, 'PL', 'Poland', 'POL', 616); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (172, 'PT', 'Portugal', 'PRT', 620); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (173, 'PR', 'Puerto Rico', 'PRI', 630); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (174, 'QA', 'Qatar', 'QAT', 634); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (175, 'RE', 'Reunion', 'REU', 638); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (176, 'RO', 'Romania', 'ROM', 642); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (177, 'RU', 'Russian Federation', 'RUS', 643); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (178, 'RW', 'Rwanda', 'RWA', 646); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (179, 'SH', 'Saint Helena', 'SHN', 654); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (180, 'KN', 'Saint Kitts and Nevis', 'KNA', 659); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (181, 'LC', 'Saint Lucia', 'LCA', 662); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (182, 'PM', 'Saint Pierre and Miquelon', 'SPM', 666); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (183, 'VC', 'Saint Vincent and the Grenadines', 'VCT', 670); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (184, 'WS', 'Samoa', 'WSM', 882); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (185, 'SM', 'San Marino', 'SMR', 674); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (186, 'ST', 'Sao Tome and Principe', 'STP', 678); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (187, 'SA', 'Saudi Arabia', 'SAU', 682); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (188, 'SN', 'Senegal', 'SEN', 686); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (244, 'RS', 'Serbia', 'SRB', 688); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (190, 'SC', 'Seychelles', 'SYC', 690); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (191, 'SL', 'Sierra Leone', 'SLE', 694); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (192, 'SG', 'Singapore', 'SGP', 702); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (193, 'SK', 'Slovakia', 'SVK', 703); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (194, 'SI', 'Slovenia', 'SVN', 705); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (195, 'SB', 'Solomon Islands', 'SLB', 090); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (196, 'SO', 'Somalia', 'SOM', 706); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (197, 'ZA', 'South Africa', 'ZAF', 710); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (198, 'GS', 'South Georgia and the South Sandwich Islands', 'SGS', 239); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (199, 'ES', 'Spain', 'ESP', 724); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (200, 'LK', 'Sri Lanka', 'LKA', 144); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (201, 'SD', 'Sudan', 'SDN', 736); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (202, 'SR', 'Suriname', 'SUR', 740); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (203, 'SJ', 'Svalbard and Jan Mayen', 'SJM', 744); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (204, 'SZ', 'Swaziland', 'SWZ', 748); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (205, 'SE', 'Sweden', 'SWE', 752); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (206, 'CH', 'Switzerland', 'CHE', 756); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (207, 'SY', 'Syrian Arab Republic', 'SYR', 760); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (208, 'TW', 'Taiwan', 'TWN', 158); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (209, 'TJ', 'Tajikistan', 'TJK', 762); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (210, 'TZ', 'Tanzania, United Republic of', 'TZA', 834); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (211, 'TH', 'Thailand', 'THA', 764); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (212, 'TL', 'Timor-Leste', 'TLS', 626); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (213, 'TG', 'Togo', 'TGO', 768); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (214, 'TK', 'Tokelau', 'TKL', 772); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (215, 'TO', 'Tonga', 'TON', 776); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (216, 'TT', 'Trinidad and Tobago', 'TTO', 780); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (217, 'TN', 'Tunisia', 'TUN', 788); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (218, 'TR', 'Turkey', 'TUR', 792); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (219, 'TM', 'Turkmenistan', 'TKM', 795); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (220, 'TC', 'Turks and Caicos Islands', 'TCA', 796); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (221, 'TV', 'Tuvalu', 'TUV', 798); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (222, 'UG', 'Uganda', 'UGA', 800); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (223, 'UA', 'Ukraine', 'UKR', 804); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (224, 'AE', 'United Arab Emirates', 'ARE', 784); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (225, 'GB', 'United Kingdom', 'GBR', 826); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (226, 'US', 'United States', 'USA', 840); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (227, 'UM', 'United States Minor Outlying Islands', 'UMI', 581); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (228, 'UY', 'Uruguay', 'URY', 858); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (229, 'UZ', 'Uzbekistan', 'UZB', 860); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (230, 'VU', 'Vanuatu', 'VUT', 548); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (231, 'VE', 'Venezuela', 'VEN', 862); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (232, 'VN', 'Viet Nam', 'VNM', 704); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (233, 'VG', 'Virgin Islands, British', 'VGB', 092); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (234, 'VI', 'Virgin Islands, U.s.', 'VIR', 850); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (235, 'WF', 'Wallis and Futuna', 'WLF', 876); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (236, 'EH', 'Western Sahara', 'ESH', 732); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (237, 'YE', 'Yemen', 'YEM', 887); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (238, 'ZM', 'Zambia', 'ZMB', 894); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (239, 'ZW', 'Zimbabwe', 'ZWE', 716); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (240, 'ME', 'Montenegro', 'MNE', 499); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (241, 'GG', 'Guernsey', 'GGY', 831); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (242, 'JE', 'Jersey', 'JEY', 832); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (243, 'IM', 'Isle of Man', 'IMN', 833); #EOQ
UPDATE `CubeCart_geo_country` SET `eu` = '1' WHERE `iso` IN('BE','BG','CZ','DK','DE','EE','GR','IE','ES','FR','HR','IT','CY','LV','LT','LU','HU','MT','NL','AT','PL','PT','RO','SI','SK','FI','SE'); #EOQ
UPDATE `CubeCart_geo_country` SET `status` = 2 WHERE `iso` NOT IN('AR', 'BR', 'CA', 'CN', 'ID', 'IN', 'JP', 'MX', 'TH', 'US'); #EOQ