<?php

namespace DooglysAdapter;

use Ramsey\Uuid\Uuid;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DooglysAdapter{

    /**
     * Relative path for getting an product list
     */
    const GET_PRODUCT_LIST_URI = '/api/v1/nomenclature/product/list';

    /**
     * Relative path for getting an product
     */
    const GET_PRODUCT_URI = '/api/v1/nomenclature/product/view/';

    /**
     * Relative path for getting an category list
     */
    const GET_CATEGORY_LIST_URI = '/api/v1/nomenclature/product-category/list';

    /**
     * Relative path for category an product
     */
    const GET_CATEGORY_URI = '/api/v1/nomenclature/product-category/view/';

    /**
     * Relative path for creating an order
     */
    const CREATE_ORDER_URI = '/api/v1/sales/order/create';

    /**
     * Relative path for updating an order
     */
    const UPDATE_ORDER_URI = '/api/v1/sales/order/update/';

    /**
     * Relative path for creating a product
     */
    const CREATE_PRODUCT_URI = '/api/v1/nomenclature/product/create';

    /**
     * Relative path for updating a product
     */
    const UPDATE_PRODUCT_URI = '/api/v1/nomenclature/product/update/';

    /**
     * Relative path for deleting a product
     */
    const DELETE_PRODUCT_URI = '/api/v1/nomenclature/product/delete/';

    /**
     *
     * Access token for Dooglys
     *
     * @var $accessToken
     */
    protected $accessToken;

    /**
     *
     * Base url
     *
     * @var $baseUrl
     */
    protected $baseUrl;

    /**
     *
     * domain of Dooglys
     *
     * @var $tenantDomain
     */
    protected $tenantDomain;

    /**
     *
     * Web client for data transfer
     *
     * @var GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * @var Monolog\Logger $logger
     */
    protected $logger;

    /**
     * DooglysAdapter constructor.
     * @param $accessToken
     * @param $domain
     * @param bool $logging
     */
    public function __construct($accessToken, $domain, bool $logging = true){

        $this->accessToken = $accessToken;
        $this->tenantDomain = $domain;
        $this->baseUrl = "https://{$this->tenantDomain}.dooglys.com";

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Access-Token' => $this->accessToken,
                'Tenant-Domain' => $this->tenantDomain
            ]
        ]);

        if ($logging):
            $this->logger = new Logger('main');
            $this->logger->pushHandler(new StreamHandler(__DIR__ . '/dooglys_logs/errors.log', Logger::ERROR));
        endif;
    }

    /**
     *
     * Function for getting a list of products
     *
     * @param int $per_page
     * @param int $include_deleted
     * @return array|bool
     */
    public function getProductsList(array $customParams = []){
        $url = $this->baseUrl . self::GET_PRODUCT_LIST_URI;
        $params = [
            'query' => $customParams
        ];

        return $this->sendRequest('GET', $url, $params);
    }

    /**
     *
     * Function for receiving one product
     *
     * @param $id
     * @return array|bool
     */
    public function getProduct($id){
        $url = $this->baseUrl . self::GET_PRODUCT_URI . $id;
        return $this->sendRequest('GET', $url);
    }

    /**
     *
     * Function for getting a list of categories
     *
     * @param int $per_page
     * @param int $include_deleted
     * @return array|bool
     */
    public function getCategoryList(int $per_page = 999, int $include_deleted = 1){
        $url = $this->baseUrl . self::GET_CATEGORY_LIST_URI;
        $params = [
            'query' => [
                'per-page' => $per_page,
                'include_deleted' => $include_deleted
            ]
        ];

        return $this->sendRequest('GET', $url, $params);
    }

    /**
     *
     * Function for getting a single category
     *
     * @param $id
     * @return array|bool
     */
    public function getCategory($id){
        $url = $this->baseUrl . self::GET_CATEGORY_URI . $id;
        return $this->sendRequest('GET', $url);
    }


    /**
     *
     * Function for creating an order
     *
     * @param array $customParams
     * @return bool|string
     */
    public function createOrder($items, array $customParams = []){
        $url = $this->baseUrl . self::CREATE_ORDER_URI;

        $id = $this->generateUuid();

        $params = [];

        $params['date_created'] = time();
        $params['source'] = 'external';

        $params = array_merge($params, $customParams);

        $params['id'] = $id;
        $params['order_status'] = 'ordered';

        $items_cost = 0;
        $total_cost = 0;
        $discount = 0;

        foreach ($items as $item):
            $items_cost += (float) $item['item_cost'];
            $total_cost += (float) $item['total_cost'];
            $discount += (float) $item['discount_value'] * (float) $item['quantity'];
        endforeach;

        if ( isset($params['delivery_cost']) && !empty($params['delivery_cost']) )
            $total_cost += (float) $params['delivery_cost'];

        $params['items_cost'] = $items_cost;
        $params['total_cost'] = $total_cost;
        $params['discount_value'] = $discount;

        $params['order_items'] = $items;

        $params = [
            'form_params' => $params
        ];

        if ($this->sendRequest('POST', $url, $params) === true):
            return $id;
        else:
            return false;
        endif;
    }

    /**
     *
     * Prepare products for order
     *
     * @param $product_id
     * @param $product_name
     * @param $quantity
     * @param $price
     * @param int $discount
     * @return array
     */
    public function getProductRowForOrder($product_id, $product_name, $quantity, $price, $discount = 0){
        return [
            "product_id" => $product_id,
            "product_name" => $product_name,
            "quantity" =>  $quantity,
            "price" => $price,
            "cost" => $price,
            "total_cost" => ($price * $quantity) - ($discount * $quantity),
            "item_cost" => $price * $quantity,
            "discount_value" => $discount
        ];
    }

    /**
     *
     * Function for creating a product
     *
     * @param array $customParams
     * @return bool
     */
    public function createProduct(array $customParams = []){
        $url = $this->baseUrl . self::CREATE_PRODUCT_URI;

        $id = $this->generateUuid();

        $params = [];
        $params['id'] = $id;
        $params = array_merge($params, $customParams);

        $params = [
            'form_params' => $params
        ];


        if ( $this->sendRequest('POST', $url, $params)):
            return $id;
        else:
            return false;
        endif;
    }

    /**
     *
     * Function for updating the order
     *
     * @param array $customParams
     * @return bool
     */
    public function updateOrder($id, array $customParams = []){
        $url = $this->baseUrl . self::UPDATE_ORDER_URI . $id;
        $params = [
            'form_params' => $customParams
        ];

        return $this->sendRequest('POST', $url, $params);
    }

    /**
     *
     * Function for updating the product
     *
     * @param array $customParams
     * @return bool
     */
    public function updateProduct($id, array $customParams = []){
        $url = $this->baseUrl . self::UPDATE_PRODUCT_URI . $id;
        $params = [
            'form_params' => $customParams
        ];

        return $this->sendRequest('POST', $url, $params);
    }

    /**
     *
     * Function for deleting the product
     *
     * @param array $customParams
     * @return bool
     */
    public function deleteProduct($id){
        $url = $this->baseUrl . self::DELETE_PRODUCT_URI . $id;
        return $this->sendRequest('POST', $url);
    }

    /**
     *
     * Сancel an order
     *
     * @param $id
     * @param string $reason
     * @return bool
     */
    public function cancelOrder($id, $employee, $reason = ''){

        $params = [
            'order_status' => 'canceled',
            'user_cancelled_id' => $employee,
            'date_returned' => time(),
            'rejection_reason' => $reason
        ];

        return $this->updateOrder($id, $params);
    }


    //Function for making a request
    protected function sendRequest($method, $url, $params = []){
        $result = false;
        try{
            $response = $this->client->request($method, $url, $params);

            if ( $response->getStatusCode() === 200 ):
                if ($method === 'GET')
                    $result = $this->responseDecode($response->getBody()->getContents());
                else
                    $result = true;
            endif;
        } catch ( BadResponseException $exception ){
            if ($this->logger)
                $this->logger->error($exception->getCode(), $this->responseDecode($exception->getResponse()->getBody()->getContents()));
        }

        return $result;
    }

    /**
     *
     * Decode api response
     *
     * @param string $data
     * @return array
     */
    protected function responseDecode( string $data ){
        return json_decode($data, true) ?? [];
    }

    /**
     *
     * Generate uuid
     *
     * @return string
     */
    protected function generateUuid(){
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }

}