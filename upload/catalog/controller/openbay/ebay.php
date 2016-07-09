<?php
class ControllerOpenbayEbay extends Controller {
	public function eventAddOrder($route, $data) {

	}

	public function eventAddOrderHistory($route, $output, $order_id) {
		$this->openbay->ebay->log('eventAddOrderHistory Event fired: ' . $route);
		$this->openbay->ebay->log('eventAddOrderHistory Order ID: ' . $order_id);

		if (!empty($order_id)) {
			$this->load->model('openbay/ebay_order');

			$this->model_openbay_ebay_order->addOrderHistory($order_id);
		}
	}
}