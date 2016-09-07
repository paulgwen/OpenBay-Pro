<?php
class ModelExtensionOpenBayAmazonusOrder extends Model {
	public function acknowledgeOrder($order_id) {
		$amazonus_order_id = $this->getAmazonusOrderId($order_id);

		$request_xml = "<Request>
  <AmazonOrderId>$amazonus_order_id</AmazonOrderId>
  <MerchantOrderId>$order_id</MerchantOrderId>
</Request>";

		$this->openbay->amazonus->callNoResponse('order/acknowledge', $request_xml, false);
	}

	public function getProductId($sku) {
		$row = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "amazonus_product_link` WHERE `amazonus_sku` = '" . $this->db->escape($sku) . "'")->row;

		if (isset($row['product_id']) && !empty($row['product_id'])) {
			return $row['product_id'];
		}

		return 0;
	}

	public function getProductVar($sku) {
		$row = $this->db->query("SELECT `var` FROM `" . DB_PREFIX . "amazonus_product_link` WHERE `amazonus_sku` = '" . $this->db->escape($sku) . "'")->row;

		if (isset($row['var'])) {
			return $row['var'];
		}

		return '';
	}

	public function decreaseProductQuantity($product_id, $delta, $var = '') {
		if ($product_id == 0) {
			return;
		}
		if ($var == '') {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = GREATEST(`quantity` - '" . (int)$delta . "', 0) WHERE `product_id` = '" . (int)$product_id . "' AND `subtract` = '1'");
		} else {
			$this->db->query("UPDATE `" . DB_PREFIX . "product_option_variant` SET `stock` = GREATEST(`stock` - '" . (int)$delta . "', 0) WHERE `product_id` = '" . (int)$product_id . "' AND `sku` = '" . $this->db->escape($var) . "' AND `subtract` = '1'");
		}
	}

	public function getMappedStatus($amazonus_status) {
		$amazonus_status = trim(strtolower($amazonus_status));

		switch ($amazonus_status) {
			case 'pending':
				$order_status = $this->config->get('openbay_amazonus_order_status_pending');
				break;

			case 'unshipped':
				$order_status = $this->config->get('openbay_amazonus_order_status_unshipped');
				break;

			case 'partiallyshipped':
				$order_status = $this->config->get('openbay_amazonus_order_status_partially_shipped');
				break;

			case 'shipped':
				$order_status = $this->config->get('openbay_amazonus_order_status_shipped');
				break;

			case 'canceled':
				$order_status = $this->config->get('openbay_amazonus_order_status_canceled');
				break;

			case 'unfulfillable':
				$order_status = $this->config->get('openbay_amazonus_order_status_unfulfillable');
				break;

			default:
				$order_status = $this->config->get('config_order_status_id');
				break;
		}

		return $order_status;
	}

	public function getCountryName($country_code) {
		$row = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "country` WHERE LOWER(`iso_code_2`) = '" . $this->db->escape(strtolower($country_code)) . "'")->row;

		if (isset($row['name']) && !empty($row['name'])) {
			return $row['name'];
		}

		return '';
	}

	public function getCountryId($country_code) {
		$row = $this->db->query("SELECT `country_id` FROM `" . DB_PREFIX . "country` WHERE LOWER(`iso_code_2`) = '" . $this->db->escape(strtolower($country_code)) . "'")->row;

		if (isset($row['country_id']) && !empty($row['country_id'])) {
			return (int)$row['country_id'];
		}

		return 0;
	}

	public function getZoneId($zone_name) {
		$row = $this->db->query("SELECT `zone_id` FROM `" . DB_PREFIX . "zone` WHERE LOWER(`name`) = '" . $this->db->escape(strtolower($zone_name)) . "'")->row;

		if (isset($row['country_id']) && !empty($row['country_id'])) {
			return (int)$row['country_id'];
		}

		return 0;
	}

	public function updateOrderStatus($order_id, $status_id) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` (`order_id`, `order_status_id`, `notify`, `comment`, `date_added`) VALUES (" . (int)$order_id . ", " . (int)$status_id . ", 0, '', NOW())");

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = " . (int)$status_id . " WHERE `order_id` = " . (int)$order_id);
	}

	public function addAmazonusOrder($order_id, $amazonus_order_id) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "amazonus_order` (`order_id`, `amazonus_order_id`) VALUES (" . (int)$order_id . ", '" . $this->db->escape($amazonus_order_id) . "')");
	}

	/* $data = array(PRODUCT_SKU => ORDER_ITEM_ID) */
	public function addAmazonusOrderProducts($order_id, $data) {
		foreach ($data as $sku => $order_item_id) {

			$row = $this->db->query("SELECT `order_product_id` FROM `" . DB_PREFIX . "order_product` WHERE `model` = '" . $this->db->escape($sku) . "' AND `order_id` = " . (int)$order_id . "LIMIT 1")->row;

			if (!isset($row['order_product_id']) || empty($row['order_product_id'])) {
				continue;
			}

			$order_product_id = $row['order_product_id'];

			$this->db->query("INSERT INTO `" . DB_PREFIX . "amazonus_order_product` (`order_product_id`, `amazonus_order_item_id`) VALUES (" . (int)$order_product_id . ", '" . $this->db->escape($order_item_id) . "')");
		}
	}

	public function getOrderId($amazonus_order_id) {
		$row = $this->db->query("SELECT `order_id` FROM `" . DB_PREFIX . "amazonus_order` WHERE `amazonus_order_id` = '" . $this->db->escape($amazonus_order_id) . "' LIMIT 1")->row;

		if (isset($row['order_id']) && !empty($row['order_id'])) {
			return $row['order_id'];
		}

		return '';
	}

	public function getOrderStatus($order_id) {
		$row = $this->db->query("SELECT `order_status_id` FROM `" . DB_PREFIX . "order` WHERE `order_id` = " . (int)$order_id)->row;

		if (isset($row['order_status_id']) && !empty($row['order_status_id'])) {
			return $row['order_status_id'];
		}

		return 0;
	}

	public function getAmazonusOrderId($order_id) {
		$row = $this->db->query("SELECT `amazonus_order_id` FROM `" . DB_PREFIX . "amazonus_order` WHERE `order_id` = " . (int)$order_id . " LIMIT 1")->row;

		if (isset($row['amazonus_order_id']) && !empty($row['amazonus_order_id'])) {
			return $row['amazonus_order_id'];
		}

		return null;
	}

	public function getProductOptionsByVar($product_var) {
		$options = array();

        if ($this->openbay->addonLoad('openstock')) {
            // search for the product_option_variant_id
            $product_option_variant = $this->db->query("SELECT `product_option_variant_id` FROM `" . DB_PREFIX . "product_option_variant` WHERE `sku` = '" . $this->db->escape($product_var) . "' LIMIT 1")->row;

            if ($product_option_variant) {
                $product_option_variant_values = $this->db->query("
			SELECT
				`pov`.`product_option_value_id`,
				`po`.`product_option_id`,
				`o`.`type`,
				`od`.`name` AS `option_name`,
				`ovd`.`name`  AS `option_value_name`
			FROM `" . DB_PREFIX . "product_option_variant_value` `povv`
			LEFT JOIN `" . DB_PREFIX . "product_option_value` `pov` ON `povv`.`product_option_value_id` = `pov`.`product_option_value_id`
			LEFT JOIN `" . DB_PREFIX . "product_option` `po` ON `pov`.`product_option_id` = `po`.`product_option_id`
			LEFT JOIN `" . DB_PREFIX . "option` `o` ON `po`.`option_id` = `o`.`option_id`
			LEFT JOIN `" . DB_PREFIX . "option_description` `od` ON `o`.`option_id` = `od`.`option_id`
			LEFT JOIN `" . DB_PREFIX . "option_value_description` `ovd` ON `pov`.`option_value_id` = `ovd`.`option_value_id`
			WHERE `povv`.`product_option_variant_id` = '" . (int)$product_option_variant['product_option_variant_id'] . "' 
			AND `od`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
			AND `ovd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
			ORDER BY `povv`.`sort_order` ASC
			");

                if ($product_option_variant_values->num_rows > 0) {
                    // get all of the option data for the variant ordered by sort
                    foreach ($product_option_variant_values->rows as $variant_value) {
                        $options[] = array(
                            'product_option_id' => (int)$variant_value['product_option_id'],
                            'product_option_value_id' => (int)$variant_value['product_option_value_id'],
                            'name' => $variant_value['option_name'],
                            'value' => $variant_value['option_value_name'],
                            'type' => $variant_value['type']
                        );
                    }
                }
            }
        }

		return $options;
	}

	public function addOrderHistory($order_id) {
		$logger = new Log('amazonus_stocks.log');
		$logger->write('addOrderHistory() called with order id: ' . $order_id);

		if ($this->config->get('openbay_amazonus_status') != 1) {
			$logger->write('addOrderHistory() openbay_amazonus_status is disabled');
			return;
		}

		$order_products = $this->openbay->getOrderProducts($order_id);
		
		$logger->write($order_products);

		if (!empty($order_products)) {
			foreach ($order_products as $order_product) {
				$this->openbay->amazonus->productUpdateListen($order_product['product_id']);
			}
		} else {
			$logger->write('Order product array empty!');
		}

		$logger->write('addOrder() exiting');
	}
}