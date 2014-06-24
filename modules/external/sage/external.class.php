<?php
class External {

	private $_module;
	public $_report_data;

	public function __construct($module = false) {
		$this->_module	= $module;
	}

	##################################################

	public function report_order_data($order_summary){
		
		// Format Date
		$date = date("d/m/Y", $order_summary['order_date']);

 		// Caculate values
        $order_summary['trans_details'] = addslashes(str_replace(",", " ", 'Order: '.$order_summary['cart_order_id']));
        $order_summary['trans_net'] = sprintf("%01.2f", $order_summary['total'] - $order_summary['total_tax']);
        $order_summary['trans_tax'] = sprintf("%01.2f", $order_summary['total_tax']);
        $total_gross = $order_summary['trans_tax'] + $order_summary['trans_net'];

		// Format account code
		if ($this->_module['exportCustomers'] == 'prefix') {
			$account = $this->_module['accountPrefix'].$order_summary['customer_id'];
		} elseif($this->_module['exportCustomers'] == 'prefix8') {
			$padded = 8 - (strlen($this->_module['accountPrefix']));
			$account = $this->_module['accountPrefix'].sprintf("%0".$padded."d", $order_summary['customer_id']);
		} else {
			$account = $this->_module['customerAccount'];
		}

		// Set exchange rate if applicable
		if(!empty($this->_module['exchangeRate'])) {
			$exchangeRate = ','.$this->_module['exchangeRate'];
		} else {
			$exchangeRate = null;
		}

		// Set Tax Code for the country
		if(isset($this->_module['taxCode_'.$order_summary['country']])) {
			$taxCode = $this->_module['taxCode_'.$order_summary['country']];
		} else {
			$taxCode = $this->_module['taxCode'];
		}

		// Build line data
		$report_data = array(
			"SI",
			$account,
			$this->_module['salesNominal'],
			1,
			$date,
			$order_summary['cart_order_id'],
			$order_summary['trans_details'],
			$order_summary['trans_net'],
			$taxCode,
			$order_summary['trans_tax'],
			$exchangeRate,
			null,
			"OnlineSystem"

		);
		$this->_report_data .= implode(",",$report_data)."\n";
		unset($report_data);

		## Export the settelment line if set to
		if($this->_module['exportPayments']){
			if(isset($this->_module['pymtNominal_'.$order_summary['gateway']])) {
				$paNom = $this->_module['pymtNominal_'.$order_summary['gateway']];
				## Build line data
				$report_data = array (
					"SA",
					$this->csvformat($account),
					$this->csvformat($paNom),
					1,
					$this->csvformat($date),
					$this->csvformat($order_summary['cart_order_id']),
					$this->csvformat($order_summary['gateway']." Payment"),
					$this->csvformat($total_gross),
					"T9",
					"0.00",
					$this->csvformat($exchangeRate),
					null,
					"OnlineSystem"

				);
				$this->_report_data .= implode(",",$report_data)."\n";
				unset($report_data);
			}
		}
	}

	public function report_customer_data($customer){
		if($this->_module['exportCustomers'] == 'prefix') {
			$account = $this->_module['accountPrefix'].$customer['customer_id'];
		} elseif($this->_module['exportCustomers'] == 'prefix8') {
			$padded = 8-(strlen($this->_module['accountPrefix']));
			$account = $this->_module['accountPrefix'].sprintf("%0".$padded."d", $customer['customer_id']);
		}
        if(!empty($customer['title'])) 		$name_parts[] = $customer['title'];
        if(!empty($customer['first_name'])) $name_parts[] = $customer['first_name'];
        if(!empty($customer['last_name'])) 	$name_parts[] = $customer['last_name'];
        $full_name = implode(' ',$name_parts);

        $report_data = array(
        	$this->csvformat($account),
        	$this->csvformat($full_name),
        	$this->csvformat($customer['line1']),
        	$this->csvformat($customer['line2']),
        	null, ## Address Line 3
        	$this->csvformat($customer['state']),
        	$this->csvformat($customer['postcode']),
        	$this->csvformat($full_name),
        	$this->csvformat($customer['phone']),
        	$this->csvformat($customer['mobile']),
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	null,
        	$this->csvformat($customer['email']),
        	null,
        	null,
        	null,
        	null,
        	null
        );
        $this->_report_data .= implode(",",$report_data)."\n";
		unset($report_data);
	}

	private function csvformat($string) {
		return strstr($string,",") ? '"'.$string.'"' : $string;
	}
}
