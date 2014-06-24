<?php
$capture_key =  $GLOBALS['config']->get('PostcodeAnywhere','capture_key');
$body_js[] = '<script type="text/javascript">
      var fields = [
         { element: "addr_line1", field: "Line1" },
         { element: "addr_line2", field: "Line2", mode: pca.fieldMode.POPULATE },
         { element: "addr_town", field: "City", mode: pca.fieldMode.POPULATE },
         { element: "state-list", field: "ProvinceName", mode: pca.fieldMode.POPULATE },
         { element: "addr_postcode", field: "PostalCode" },
         { element: "country-list", field: "CountryName", mode: pca.fieldMode.COUNTRY }/*,
         { element: "del_line1", field: "Line1" },
         { element: "del_line2", field: "Line2", mode: pca.fieldMode.POPULATE },
         { element: "del_town", field: "City", mode: pca.fieldMode.POPULATE },
         { element: "delivery_state", field: "ProvinceName", mode: pca.fieldMode.POPULATE },
         { element: "del_postcode", field: "PostalCode" },
         { element: "delivery_country", field: "CountryName", mode: pca.fieldMode.COUNTRY }*/
      ],
      options = {
         key: "'.$capture_key.'"
      },
      control = new pca.Address(fields, options);
      </script>
      <script src="'.$GLOBALS['storeURL'].'/modules/plugins/PostcodeAnywhere/js/cubecart.min.js"></script>';