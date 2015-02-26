<?php
class ControllerOpenbayEbayListing extends Controller {
	private $error = array();

	public function bulkStep1() {
		$this->load->language('openbay/ebay_listing');
		$this->load->model('catalog/category');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!isset($this->request->post['selected']) || empty($this->request->post['selected'])) {
				$this->error['warning'] = $this->language->get('error_select_category');
			} else {
				$this->session->data['bulk_category_list']['categories'] = $this->request->post['selected'];

				$this->redirect($this->url->link('openbay/ebay_listing/bulkstep2', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['selected_categories'] = isset($this->session->data['bulk_category_list']['categories']) ? $this->session->data['bulk_category_list']['categories'] : array();

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/openbay.css');
		$this->document->addScript('view/javascript/openbay/faq.js');

		$this->template = 'openbay/ebay_bulk_step1.tpl';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->data['url_return']  = $this->url->link('openbay/ebay/dashboard', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['form_action'] = $this->url->link('openbay/ebay_listing/bulkstep1', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['button_submit'] = $this->language->get('button_submit');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['error_select_category'] = $this->language->get('error_select_category');
		$this->data['text_category'] = $this->language->get('text_category');

		$this->data['token'] = $this->session->data['token'];

		$this->data['categories'] = $this->model_catalog_category->getCategories(array());

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_openbay'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_ebay'),
			'separator' => ' :: '
		);

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->response->setOutput($this->render(true), $this->config->get('config_compression'));
	}

	public function bulkStep2() {
		$this->load->language('openbay/ebay_listing');
		$this->load->model('catalog/product');

		$this->data['error_warning'] = '';

		if (!isset($this->session->data['bulk_category_list']['categories']) || empty($this->session->data['bulk_category_list']['categories'])) {
			$this->redirect($this->url->link('openbay/ebay_listing/bulkstep1', 'token=' . $this->session->data['token'], 'SSL'));
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!isset($this->request->post['selected']) || empty($this->request->post['selected'])) {
				$this->data['error_warning'] = $this->language->get('error_select_product');
			} else {
				$this->session->data['bulk_category_list']['products'] = $this->request->post['selected'];

				$this->redirect($this->url->link('openbay/ebay_listing/bulkstep3', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['selected_products'] = isset($this->session->data['bulk_category_list']['products']) ? $this->session->data['bulk_category_list']['products'] : array();

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/openbay.css');
		$this->document->addScript('view/javascript/openbay/faq.js');

		$this->template = 'openbay/ebay_bulk_step2.tpl';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->data['url_return']  = $this->url->link('openbay/ebay/dashboard', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_back']  = $this->url->link('openbay/ebay_listing/bulkstep1', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['form_action'] = $this->url->link('openbay/ebay_listing/bulkstep2', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['button_submit'] = $this->language->get('button_submit');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_product'] = $this->language->get('text_product');
		$this->data['text_model'] = $this->language->get('text_model');
		$this->data['text_price'] = $this->language->get('text_price');
		$this->data['text_quantity'] = $this->language->get('text_quantity');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_fail_reason'] = $this->language->get('text_fail_reason');
		$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['error_select_category'] = $this->language->get('error_select_category');

		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_openbay'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_ebay'),
			'separator' => ' :: '
		);

		$categories = $this->session->data['bulk_category_list']['categories'];

		$ebay_active = $this->openbay->ebay->getLiveListingArray();

		$products = array();
		$products_fail = array();

		foreach ($categories as $category_id) {
			$category_products = $this->model_catalog_product->getProductsByCategoryId($category_id);

			foreach ($category_products as $category_product) {
				if (!array_key_exists($category_product['product_id'], $products)) {
					// can't list due to stock
					if ($category_product['quantity'] < 1) {
						$products_fail[$category_product['product_id']] = array(
							'name' => $category_product['name'],
							'price' => $category_product['price'],
							'model' => $category_product['model'],
							'quantity' => $category_product['quantity'],
							'reason' => $this->language->get('error_no_stock'),
						);
					} elseif ($category_product['price'] < 1) {
						$products_fail[$category_product['product_id']] = array(
							'name' => $category_product['name'],
							'price' => $category_product['price'],
							'model' => $category_product['model'],
							'quantity' => $category_product['quantity'],
							'reason' => $this->language->get('error_price'),
						);
					} elseif (strlen($category_product['name']) > 75) {
						$products_fail[$category_product['product_id']] = array('name' => $category_product['name'], 'price' => $category_product['price'], 'model' => $category_product['model'], 'quantity' => $category_product['quantity'], 'reason' => $this->language->get('error_title_length'),);
					//} elseif () { // check the image size

					} elseif (array_key_exists($category_product['product_id'], $ebay_active)) {
						$products_fail[$category_product['product_id']] = array(
							'name' => $category_product['name'],
							'price' => $category_product['price'],
							'model' => $category_product['model'],
							'quantity' => $category_product['quantity'],
							'reason' => $this->language->get('error_existing_item'),
						);
					} else {
						$products[$category_product['product_id']] = array(
							'name' => $category_product['name'],
							'price' => $category_product['price'],
							'model' => $category_product['model'],
							'quantity' => $category_product['quantity'],
						);
					}
				}
			}
		}

		$this->data['products'] = $products;
		$this->data['products_count'] = count($products);
		$this->data['text_products_count'] = sprintf($this->language->get('text_available_to_list'), count($products));

		$this->data['products_fail'] = $products_fail;
		$this->data['products_fail_count'] = count($products_fail);
		$this->data['text_products_fail_count'] = sprintf($this->language->get('text_unavailable_to_list'), count($products_fail));

		if ($this->data['products_count'] == 0) {
			$this->data['error_warning'] = $this->language->get('error_no_products');
		}

		$this->response->setOutput($this->render(true), $this->config->get('config_compression'));
	}

	public function bulkStep3() {
		$this->load->language('openbay/ebay_listing');
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_profile');

		if (!isset($this->session->data['bulk_category_list']['products']) || empty($this->session->data['bulk_category_list']['products'])) {
			$this->redirect($this->url->link('openbay/ebay_listing/bulkstep1', 'token=' . $this->session->data['token'], 'SSL'));
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {



		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/openbay.css');
		$this->document->addScript('view/javascript/openbay/faq.js');

		$this->template = 'openbay/ebay_bulk_step3.tpl';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->data['url_return']  = $this->url->link('openbay/ebay/dashboard', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_back']  = $this->url->link('openbay/ebay_listing/bulkstep2', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['form_action'] = $this->url->link('openbay/ebay_listing/bulkstep3', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['button_submit'] = $this->language->get('button_submit');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['error_ajax_noload'] = $this->language->get('error_ajax_noload');
		$this->data['error_category_sync'] = $this->language->get('error_category_sync');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_other'] = $this->language->get('text_other');
		$this->data['text_feature_loading'] = $this->language->get('text_feature_loading');
		$this->data['entry_category_features'] = $this->language->get('entry_category_features');
		$this->data['entry_listing_condition'] = $this->language->get('entry_listing_condition');
		$this->data['entry_listing_duration'] = $this->language->get('entry_listing_duration');
		$this->data['entry_profile_shipping'] = $this->language->get('entry_profile_shipping');
		$this->data['entry_profile_returns'] = $this->language->get('entry_profile_returns');
		$this->data['entry_profile_theme'] = $this->language->get('entry_profile_theme');
		$this->data['entry_profile_generic'] = $this->language->get('entry_profile_generic');
		$this->data['text_listing_1day'] = $this->language->get('text_listing_1day');
		$this->data['text_listing_3day'] = $this->language->get('text_listing_3day');
		$this->data['text_listing_5day'] = $this->language->get('text_listing_5day');
		$this->data['text_listing_7day'] = $this->language->get('text_listing_7day');
		$this->data['text_listing_10day'] = $this->language->get('text_listing_10day');
		$this->data['text_listing_30day'] = $this->language->get('text_listing_30day');
		$this->data['text_listing_gtc'] = $this->language->get('text_listing_gtc');

		$this->data['profiles']['shipping'] = $this->model_openbay_ebay_profile->getAll(0);
		$this->data['profiles']['shipping_default'] = $this->model_openbay_ebay_profile->getDefault(0);

		$this->data['profiles']['returns'] = $this->model_openbay_ebay_profile->getAll(1);
		$this->data['profiles']['returns_default'] = $this->model_openbay_ebay_profile->getDefault(1);

		$this->data['profiles']['theme'] = $this->model_openbay_ebay_profile->getAll(2);
		$this->data['profiles']['theme_default'] = $this->model_openbay_ebay_profile->getDefault(2);

		$this->data['profiles']['generic'] = $this->model_openbay_ebay_profile->getAll(3);
		$this->data['profiles']['generic_default'] = $this->model_openbay_ebay_profile->getDefault(3);

		$this->data['defaults']['listing_duration'] = $this->config->get('openbaypro_duration');
		if ($this->data['defaults']['listing_duration'] == '') {
			$this->data['defaults']['listing_duration'] = 'Days_30';
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_openbay'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_ebay'),
			'separator' => ' :: '
		);

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->response->setOutput($this->render(true), $this->config->get('config_compression'));
	}

	public function bulkStep4() {

	}
}