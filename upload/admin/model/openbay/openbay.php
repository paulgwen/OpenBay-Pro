<?php
class ModelOpenbayOpenbay extends Model
{
    private $url    = 'https://account.openbaypro.com/';

    public function setUrl($url){
        $this->url = $url;
    }

    public function getNotifications() {
        $data = $this->call('update/getNotifications/');
        return $data;
    }

    public function getVersion() {
        $data = $this->call('update/getStableVersion/');
        return $data;
    }

    public function faqGet($route){
        if($this->faqIsDismissed($route) != true){
            $data = $this->call('faq/get/', array('route' => $route));
            return $data;
        }else{
            return false;
        }
    }

    public function faqIsDismissed($route){
        $this->faqDbTableCheck();

        $sql = $this->db->query("SELECT * FROM `".DB_PREFIX."openbay_faq` WHERE `route` = '".$this->db->escape($route)."'");

        if($sql->num_rows > 0){
            return true;
        }else{
            return false;
        }
    }

    public function faqDismiss($route){
        $this->faqDbTableCheck();
        $this->db->query("INSERT INTO `".DB_PREFIX."openbay_faq` SET `route` = '".$this->db->escape($route)."'");
    }

    public function faqClear(){
        $this->faqDbTableCheck();
        $this->db->query("TRUNCATE `" . DB_PREFIX . "openbay_faq`");
    }

    public function faqDbTableCheck(){
        if(!$this->openbay->testDbTable(DB_PREFIX . "openbay_faq")){
            $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."openbay_faq` (`id` int(11) NOT NULL AUTO_INCREMENT,`route` text NOT NULL, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        }
    }
    
    public function checkMcrypt(){
        if(function_exists('mcrypt_encrypt')){
            return true;
        }else{
            return false;
        }
    }
    
    public function checkMbstings(){
        if(function_exists('mb_detect_encoding')){
            return true;
        }else{
            return false;
        }
    }
    
    private function call($call, array $post = NULL, array $options = array(), $content_type = 'json'){
        if(defined("HTTP_CATALOG")){
            $domain = HTTP_CATALOG;
        }else{
            $domain = HTTP_SERVER;
        }

        $data = array(
            'token'             => '', 
            'language'          => $this->config->get('openbay_language'), 
            'secret'            => '', 
            'server'            => 1, 
            'domain'            => $domain, 
            'openbay_version'   => (int)$this->config->get('openbay_version'),
            'data'              => $post, 
            'content_type'      => $content_type,
            'ocversion'         => VERSION
        );

        $useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

        $defaults = array(
            CURLOPT_POST            => 1,
            CURLOPT_HEADER          => 0,
            CURLOPT_URL             => $this->url.$call,
            CURLOPT_USERAGENT       => $useragent, 
            CURLOPT_FRESH_CONNECT   => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_FORBID_REUSE    => 1,
            CURLOPT_TIMEOUT         => 0,
            CURLOPT_SSL_VERIFYPEER  => 0,
            CURLOPT_SSL_VERIFYHOST  => 0,
            CURLOPT_POSTFIELDS      => http_build_query($data, '', "&")
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);
        curl_close($ch);

        if($content_type == 'json'){
            $encoding = mb_detect_encoding($result);

            /* some json data may have BOM due to php not handling types correctly */
            if($encoding == 'UTF-8') {
              $result = preg_replace('/[^(\x20-\x7F)]*/','', $result);    
            } 

            $result             = json_decode($result, 1);
            $this->lasterror    = $result['error'];
            $this->lastmsg      = $result['msg'];

            if(!empty($result['data'])){
                return $result['data'];
            }else{
                return false;
            }
        }elseif($content_type == 'xml'){
            $result             = simplexml_load_string($result);
            $this->lasterror    = $result->error;
            $this->lastmsg      = $result->msg;

            if(!empty($result->data)){
                return $result->data;
            }else{
                return false;
            }
        }
    }

    public function getTotalProducts($data = array()) {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (!empty($data['filter_category'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        if ($data['filter_market_name'] == 'ebay') {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "ebay_listing` `ebay` ON (`p`.`product_id` = `ebay`.`product_id`)";

            if($data['filter_market_id'] == 0){
                $sql .= " LEFT JOIN (SELECT product_id, IF( SUM( `status` ) = 0, 0, 1 ) AS 'listing_status' FROM " . DB_PREFIX . "ebay_listing GROUP BY product_id ) ebay2 ON (p.product_id = ebay2.product_id)";
            }
        }
        
        if ($data['filter_market_name'] == 'amazon') {
            if($data['filter_market_id'] <= 4) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazon_product ap ON p.product_id = ap.product_id";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazon_product_link apl ON p.product_id = apl.product_id";
            }
            
            $amazon_status = array(
                1 => 'saved',
                2 => 'uploaded',
                3 => 'ok',
                4 => 'error',
                5 => 'amazon_linked',
                6 => 'amazon_not_linked',
            );
        }

        if ($data['filter_market_name'] == 'play') {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "play_product_insert` `play` ON (`p`.`product_id` = `play`.`product_id`)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_category'])) {
            if($data['filter_category'] == 'none'){
                $sql .= " AND p2c.category_id IS NULL";
            }else{
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
            }
        }

        if ($data['filter_market_name'] == 'ebay') {
            if($data['filter_market_id'] == 0){
                $sql .= " AND ebay.ebay_listing_id IS NULL OR ebay2.listing_status = 0";
            }else{
                $sql .= " AND ebay.ebay_listing_id IS NOT NULL AND ebay.status = 1";
            }
        }
        
        if ($data['filter_market_name'] == 'amazon') {
            if ($data['filter_market_id'] == 0) {
                $sql .= " AND ap.product_id IS NULL ";
            } elseif($data['filter_market_id'] == 5) { 
                $sql .= " AND apl.id IS NOT NULL";
            } elseif($data['filter_market_id'] == 6) { 
                $sql .= " AND apl.id IS NULL";
            } else {
                $sql .= " AND FIND_IN_SET('" . $this->db->escape($amazon_status[$data['filter_market_id']]) . "', ap.`status`) != 0";
            }
        }

        if ($data['filter_market_name'] == 'play') {
            if($data['filter_market_id'] == 0){
                $sql .= " AND play.play_product_insert_id IS NULL";
            }else{
                $sql .= " AND play.status = '" . (int)$data['filter_market_id'] . "'";
            }
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price >= '" . (double)$data['filter_price'] . "'";
        }

        if (!empty($data['filter_price_to'])) {
            $sql .= " AND p.price <= '" . (double)$data['filter_price_to'] . "'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity >= '" . $this->db->escape($data['filter_quantity']) . "'";
        }

        if (isset($data['filter_quantity_to']) && !is_null($data['filter_quantity_to'])) {
            $sql .= " AND p.quantity <= '" . $this->db->escape($data['filter_quantity_to']) . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_sku']) && !is_null($data['filter_sku'])) {
            $sql .= " AND p.sku != ''";
        }

        if (isset($data['filter_desc']) && !is_null($data['filter_desc'])) {
            $sql .= " AND pd.description != ''";
        }

        if (isset($data['filter_manufacturer']) && !is_null($data['filter_manufacturer'])) {
            $sql .= " AND pd.description != '" . (int)$data['filter_manufacturer'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getProducts($data = array()) {
        $sql = "SELECT p.*, pd.* FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (!empty($data['filter_category'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        if ($data['filter_market_name'] == 'ebay') {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "ebay_listing` `ebay` ON (`p`.`product_id` = `ebay`.`product_id`)";

            if($data['filter_market_id'] == 0){
                $sql .= " LEFT JOIN (SELECT product_id, IF( SUM( `status` ) = 0, 0, 1 ) AS 'listing_status' FROM " . DB_PREFIX . "ebay_listing GROUP BY product_id ) ebay2 ON (p.product_id = ebay2.product_id)";
            }
        }

        if ($data['filter_market_name'] == 'amazon') {
            if($data['filter_market_id'] <= 4) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazon_product ap ON p.product_id = ap.product_id";
            } elseif($data['filter_market_id'] <= 6) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazon_product_link apl ON p.product_id = apl.product_id";
            }
            
            $amazon_status = array(
                1 => 'saved',
                2 => 'uploaded',
                3 => 'ok',
                4 => 'error',
            );
        }
        
        if ($data['filter_market_name'] == 'amazonus') {
            if($data['filter_market_id'] <= 4) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazonus_product ap ON p.product_id = ap.product_id";
            } elseif($data['filter_market_id'] <= 6) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "amazonus_product_link apl ON p.product_id = apl.product_id";
            }
            
            $amazonus_status = array(
                1 => 'saved',
                2 => 'uploaded',
                3 => 'ok',
                4 => 'error',
            );
        }
        
        if ($data['filter_market_name'] == 'play') {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "play_product_insert` `play` ON (`p`.`product_id` = `play`.`product_id`)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_category'])) {
            if($data['filter_category'] == 'none'){
                $sql .= " AND p2c.category_id IS NULL";
            }else{
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
            }
        }

        if ($data['filter_market_name'] == 'ebay') {
            if($data['filter_market_id'] == 0){
                $sql .= " AND ebay.ebay_listing_id IS NULL OR ebay2.listing_status = 0";
            }else{
                $sql .= " AND ebay.ebay_listing_id IS NOT NULL AND ebay.status = 1";
            }
        }
        
        if ($data['filter_market_name'] == 'amazon') {
            if ($data['filter_market_id'] == 0) {
                $sql .= " AND ap.product_id IS NULL ";
            } elseif($data['filter_market_id'] == 5) { 
                $sql .= " AND apl.id IS NOT NULL"; 
            } elseif($data['filter_market_id'] == 6) { 
                $sql .= " AND apl.id IS NULL";
            } else {
                $sql .= " AND FIND_IN_SET('" . $this->db->escape($amazon_status[$data['filter_market_id']]) . "', ap.`status`) != 0";
            }
        }
        
        if ($data['filter_market_name'] == 'amazonus') {
            if ($data['filter_market_id'] == 0) {
                $sql .= " AND ap.product_id IS NULL ";
            } elseif($data['filter_market_id'] == 5) { 
                $sql .= " AND apl.id IS NOT NULL"; 
            } elseif($data['filter_market_id'] == 6) { 
                $sql .= " AND apl.id IS NULL";
            } else {
                $sql .= " AND FIND_IN_SET('" . $this->db->escape($amazonus_status[$data['filter_market_id']]) . "', ap.`status`) != 0";
            }
        }

        if ($data['filter_market_name'] == 'play') {
            if($data['filter_market_id'] == 0){
                $sql .= " AND play.play_product_insert_id IS NULL";
            }else{
                $sql .= " AND play.status = '" . (int)$data['filter_market_id'] . "'";
            }
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price >= '" . (double)$data['filter_price'] . "'";
        }

        if (!empty($data['filter_price_to'])) {
            $sql .= " AND p.price <= '" . (double)$data['filter_price_to'] . "'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity >= '" . $this->db->escape($data['filter_quantity']) . "'";
        }

        if (isset($data['filter_quantity_to']) && !is_null($data['filter_quantity_to'])) {
            $sql .= " AND p.quantity <= '" . $this->db->escape($data['filter_quantity_to']) . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_sku']) && !is_null($data['filter_sku'])) {
            $sql .= " AND p.sku != ''";
        }

        if (isset($data['filter_desc']) && !is_null($data['filter_desc'])) {
            $sql .= " AND pd.description != ''";
        }

        if (isset($data['filter_manufacturer']) && !is_null($data['filter_manufacturer'])) {
            $sql .= " AND pd.description != '" . (int)$data['filter_manufacturer'] . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }
}