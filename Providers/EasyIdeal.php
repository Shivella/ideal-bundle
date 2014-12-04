<?php
/*
	**********************************************
	*	Qantani Payments XML wrapper class
	*	E-mail: support@qantani.com
	*	Copyright 2014 - Qantani B.V.
	***********************************************
*/

namespace Usoft\IDealBundle\Providers;

/**
 * Class EasyIdeal
 */
class EasyIdeal
{
    static public $endpoint = 'https://www.qantanipayments.com/api/';
    static public $version = '1.2.3';

    static public function CreateInstance($merchant_id, $merchant_key, $merchant_secret){

        $instance = new self();

        $instance->_merchant_id = $merchant_id;
        $instance->_merchant_key = $merchant_key;
        $instance->_merchant_secret = $merchant_secret;

        return $instance;
    }

    public function CC_Execute($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description', 'Return'));
        return $this->_call('CC.EXECUTE', $parameters, array('Response', 'Response', 'BankURL', 'value'));
    }

    public function Ideal_getBanks($parameters = array()){
        $this->_mandatory($parameters, array());
        $data= $this->_call('IDEAL.GETBANKS', $parameters, array('Response', 'Banks', 'Bank'));
        $res = array();
        foreach($data as $item){
            $res[] = array(
                'Name'=>$item['Name']['value'],
                'Id'=>$item['Id']['value']
            );
        }
        return $res;
    }

    public function Ideal_execute($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description', 'Bank', 'Return'));
        return $this->_call('IDEAL.EXECUTE', $parameters, array('Response', 'Response', 'BankURL', 'value'));
    }

    public function paylater_execute($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description'));

        if (count($this->_products) == 0){
            // no product info
            throw new \Exception('You have not defined any products. Please call $qantani->addProduct()');
        }
        if (count($this->_customer) == 0){
            // no customer info
            throw new \Exception('You have not defined a customer. Please call $qantani->addCustomer()');
        }

        return $this->_call('PAYLATER.EXECUTE', $parameters, array('Response', 'Status', 'value'));
    }

    public function bitpay_execute($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description'));
        return $this->_call('BITPAY.EXECUTE', $parameters, array('Response', 'Response', 'BankURL', 'value'));
    }


    public function GetPaymentMethods($parameters = array()){
        $this->_mandatory($parameters, array());
        $data = $this->_call('GETPAYMENTMETHODS', $parameters, array('Response', 'Response', 'PaymentMethods', 'PaymentMethod'));
        $res = array();
        foreach($data as $item){
            if ($item['Active']['value'] == 'Y')
                $res[] = array('name'=>$item['Name']['value']);
        }
        return $res;
    }

    public function Paypal_execute($parameters = array()){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description', 'Return'));
        return $this->_call('PAYPAL.EXECUTE', $parameters, array('Response', 'Response', 'BankURL', 'value'));
    }

    public function Sofort_execute($parameters = array()){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description', 'Return'));
        return $this->_call('SOFORT.EXECUTE', $parameters, array('Response', 'Response', 'BankURL', 'value'));
    }

    public function AddProduct($id, $description, $amount, $price, $currency='EUR', $vat_percentage=21){
        $this->_products[] = array(
            'ID'=>$id,
            'Description'=>$description,
            'Amount'=>$amount,
            'Price'=>$price,
            'Currency'=>$currency,
            'Vat'=>$vat_percentage
        );
    }

    public function AddCustomer($type, $id, $companyname, $chamberofcommerce, $firstname, $familyname, $gender, $street, $housenumber, $houseextension, $postalcode, $city, $birthdate, $email, $phonenumber, $ip){

        $this->_customer = array(

            'ID'=>$id,
            'BirthDate'=>$birthdate,
            'Email'=>$email,
            'Familyname'=>$familyname,
            'Firstname'=>$firstname,
            'HouseNumber'=>$housenumber,
            'HouseExtension'=>$houseextension,

            'CompanyName'=>$companyname,
            'ChamberOfCommerce'=>$chamberofcommerce,

            'Ip'=>$ip,
            'PhoneNumber'=>$phonenumber,
            'PostalCode'=>$postalcode,
            'Type'=>$type,
            'Gender'=>$gender,
            'City'=>$city,
            'Street'=>$street,

        );

    }

    public function GetLastError(){
        return $this->_last_error;
    }

    public function GetLastErrorId(){
        return $this->_last_error_id;
    }

    public function getLastCustomer(){

        return $this->_lastcustomer;

    }

    public function GetLastTransactionCode(){
        if (!$this->_transaction_code){
            throw new \Exception('No transaction code received');
        }
        return $this->_transaction_code;
    }

    public function GetLastTransactionId(){
        if (!$this->_transaction_id){
            throw new \Exception('No transaction id received');
        }
        return $this->_transaction_id;
    }


    public function getPaymentStatus($transactioncode){
        if ($_GET['checksum'] == sha1($_GET['id'] . $transactioncode . $_GET['status'] . $_GET['salt'])){
            return $_GET['status'] == '1';
        }
        else{
            throw new \Exception('Checksum error');
        }
    }

    public function getTransactionStatus($parameters){
        $this->_mandatory($parameters, array('TransactionID', 'TransactionCode'));
        $result = $this->_call('TRANSACTIONSTATUS', $parameters, array('Response', 'Transaction'));

        if ($result && count($result)){
            if (isset($result['Consumer']) && isset($result['Consumer']['IBAN']) && isset($result['Consumer']['IBAN']['value'])){
                $this->_lastcustomer = array(
                    'Iban'=> $result['Consumer']['IBAN']['value'],
                    'Bank'=> $result['Consumer']['Bank']['value'],
                    'Name'=> $result['Consumer']['Name']['value']
                );
            }
            else{
                $this->_lastcustomer = array();
            }
        }
        else{
            $this->_lastcustomer = array();
        }

        return $result['Paid']['value'] == 'Y';
    }

    public function CC_Direct($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'Description', 'Brand', 'Number', 'Expiry', 'Verification', 'Holder'));
        return $this->_call('CC.DIRECT', $parameters, array('Response', 'Response', 'TransactionID', 'value'));
    }

    public function CC_direct_internal($parameters){
        $this->_mandatory($parameters, array('Amount', 'Currency', 'TransactionID', 'Brand', 'Number', 'Expiry', 'Verification', 'Holder'));
        return $this->_call('CC.DIRECT.INTERNAL', $parameters, array('Response', 'Response', 'TransactionID', 'value'));
    }

    public function register_callback($parameters){
        $this->_mandatory($parameters, array('TransactionID', 'Success', 'Failure'));
        return $this->_call('REGISTER_CALLBACK', $parameters, array('Response', 'Status', 'value'));
    }

    public function cancel_callback($parameters){
        $this->_mandatory($parameters, array('TransactionID'));
        return $this->_call('CANCEL_CALLBACK', $parameters, array('Response', 'Status', 'value'));
    }


    private $_merchant_id = '';
    private $_merchant_key = '';
    private $_merchant_secret = '';
    private $_last_error = '';
    private $_last_error_id = 0;
    private $_transaction_code = '';
    private $_transaction_id = '';
    private $_customer = array();
    private $_products = array();
    private $_lastcustomer = array();

    public $xml_in = '';
    public $xml_out = '';

    static public $debug = false;

    private function _call($function, $parameters, $look_for=array()){

        $this->_last_error = '';
        $this->_last_error_id = 0;

        $data = array(
            'Transaction' => array(
                'Action' => array(
                    'Name' => $function,
                    'Version' => 1,
                    'ClientVersion' => self::$version,
                ),
                'Parameters' => $parameters,
                'Merchant' =>array(
                    'ID' => $this->_merchant_id,
                    'Key' => $this->_merchant_key,
                    'Checksum' => $this->_generateChecksum($parameters),
                ),
            )
        );

        if (count($this->_products)){
            $data['Transaction']['Products'] = array();
            foreach($this->_products as $index=>$product){
                $data['Transaction']['Products']['Product.' . $index] = $product;
            }
        }

        if (count($this->_customer)){
            $data['Transaction']['Customer'] = $this->_customer;
        }

        $xml = self::_encodeXML($data);

        if (self::$debug){
            echo '<strong>XML out:</strong>';
            echo '<pre>';
            echo str_replace('<', '&lt;', $xml);
            echo '</pre><br /><br />';
        }

        $ch = curl_init(self::$endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_URL, self::$endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //  todo: get from server class
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode($xml));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

        $returndata = curl_exec($ch);

        if (self::$debug){
            echo '<strong>XML in:</strong>';
            echo '<pre>';
            echo str_replace('<', '&lt;', $returndata);
            echo '</pre><br /><br />';
        }

        $result = self::_decodexml($returndata);
        if (isset($result['Response']) && isset($result['Response']['Status']) && $result['Response']['Status']['value'] == 'OK'){

            if (isset($result['Response']['Response']) && isset($result['Response']['Response']['Code'])){
                $this->_transaction_code = $result['Response']['Response']['Code']['value'];
            }
            else{
                $this->_transaction_code = null;
            }

            if (isset($result['Response']['Response']) && isset($result['Response']['Response']['TransactionID'])){
                $this->_transaction_id = $result['Response']['Response']['TransactionID']['value'];
            }
            else{
                $this->_transaction_id = null;
            }
            $pnt = $result;
            foreach($look_for as $item){
                if (isset($pnt[$item])){
                    $pnt = $pnt[$item];
                }
                else{
                    return true;
                }
            }
            return $pnt;

        }
        else{

            if (isset($result['Response']) && isset($result['Response']['Status'])){
                $this->xml_in = $returndata;
                $this->xml_out = $xml;
                $this->_last_error = $result['Response']['Error']['Description']['value'];
                $this->_last_error_id = $result['Response']['Error']['ID']['value'];
            }
            else{
                $this->xml_in = $returndata;
                $this->xml_out = $xml;
                $this->_last_error = 'Invalid response from server';
                $this->_last_error_id = 1;
                mail('transaction-log@qantani.com', 'err', $returndata);
            }
            return false;
        }

    }

    private function _generateChecksum($par){
        ksort($par);
        $checksum = '';
        foreach($par as $k=>$v){
            $checksum .= $v;
        }

        $lines = array();


        // insert products
        if (count($this->_products)){
            foreach($this->_products as $index=>$product){
                $lines[] .= $product['Amount'] . $product['Currency'] . $product['Description'] . $product['ID'] . $product['Price'] . $product['Vat'];
            }
        }

        // insert customer
        if (count($this->_customer)){
            ksort($this->_customer);
            $line = '';
            foreach($this->_customer as $v){
                $line .= $v;
            }
            $lines[] = $line;
        }

        return sha1($checksum . implode('', $lines) . $this->_merchant_secret);
    }

    private function _mandatory($parameters, $mandatory){
        foreach($mandatory as $item){
            if (!isset($parameters[$item])){
                throw new \Exception($item . ' has not been set');
            }
        }
    }

    private function _encodeXML($data, $level=0){
        $xml = ($level == 0) ? '<?xml version="1.0" encoding="UTF-8"?' .'>' . "\n" : '';

        foreach($data as $k=>$v){
            $xml .= str_repeat("\t", $level);
            if (preg_match('/^(.+)\.(\d+)$/', $k, $matches))
                $k = $matches[1];
            $xml .= '<' . $k;
            $children = array();
            if (is_array($v)){
                foreach($v as $k2=>$v2){
                    if (substr($k2, 0, 5) == 'attr:'){
                        $xml .= ' ' . substr($k2,5) . '="' . $v2 . '"';
                        unset($v[$k2]);
                    }
                }
            }
            if (is_array($v) && count($v)){
                $xml .= '>' . "\n";
                $xml .= self::_encodeXML($v, $level+1);
                $xml .= str_repeat("\t", $level) . '</' . $k . '>' . "\n";
            }
            else{
                if ($v){
                    $xml .= '>' . htmlentities($v) . '</' . $k . '>' . "\n";
                }
                else{
                    $xml .= ' />' . "\n";
                }
            }

        }
        return $xml;
    }

    private function _decodeXML($contents){
        if(!$contents) return array();
        $priority = '';

        if(!function_exists('xml_parser_create')) {
            return array();
        }

        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if(!$xml_values) return;


        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array;

        $repeated_tag_index = array();
        foreach($xml_values as $data) {
            unset($attributes,$value);


            extract($data);

            $result = array();
            $attributes_data = array();

            if(isset($value)) {
                if($priority == 'tag') $result = $value;
                else $result['value'] = $value;
            }


            if(isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {
                    if($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val;
                }
            }


            if($type == "open") {
                $parent[$level-1] = &$current;
                if(!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag.'_'.$level] = 1;

                    $current = &$current[$tag];

                } else {
                    if(isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {
                        $current[$tag] = array($current[$tag],$result);
                        $repeated_tag_index[$tag.'_'.$level] = 2;

                        if(isset($current[$tag.'_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                    $current = &$current[$tag][$last_item_index];
                }

            } elseif($type == "complete") {

                if(!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

                } else {
                    if(isset($current[$tag][0]) and is_array($current[$tag])) {

                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                        if($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;

                    } else {
                        $current[$tag] = array($current[$tag],$result);
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $get_attributes) {
                            if(isset($current[$tag.'_attr'])) {

                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                            if($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;
                    }
                }

            } elseif($type == 'close') {
                $current = &$parent[$level-1];
            }
        }

        return($xml_array);

    }
} 
