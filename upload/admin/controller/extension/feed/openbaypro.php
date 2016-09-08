<?php
class ControllerExtensionFeedOpenbaypro extends Controller {
	private $error = array();

	public function index() {
        $this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], true));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/openbaypro')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->load->model('extension/event');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/openbay');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/openbay');

		$settings = $this->model_setting_setting->getSetting('openbaypro');
		$settings['openbaypro_status'] = 1;
		$this->model_setting_setting->editSetting('openbaypro', $settings);

		$this->model_extension_event->addEvent('openbay_product_del_after', 'admin/model/catalog/product/deleteProduct/after', 'extension/openbay/eventDeleteProduct');

		$this->model_extension_event->addEvent('openbay_product_edit_after', 'admin/model/catalog/product/editProduct/after', 'extension/openbay/eventEditProduct');

		$this->model_extension_event->addEvent('openbay_menu', 'admin/view/common/column_left/before', 'extension/openbay/eventMenu');
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->load->model('extension/event');

		$settings = $this->model_setting_setting->getSetting('openbaypro');
		$settings['openbaypro_status'] = 0;
		$this->model_setting_setting->editSetting('openbaypro', $settings);

		$this->model_extension_event->deleteEvent('openbay_product_del_after');
		$this->model_extension_event->deleteEvent('openbay_product_edit_after');
		$this->model_extension_event->deleteEvent('openbay_menu');
	}
}
