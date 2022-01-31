<?php


namespace DooglysAdapter\Models;

use DooglysAdapter\Exceptions\EmptyElementException;
use DooglysAdapter\Interfaces\IGetById;
use DooglysAdapter\Interfaces\IGetList;
use DooglysAdapter\Interfaces\IDelete;
use DooglysAdapter\Interfaces\ISave;
use DooglysAdapter\Models\Traits\GetList;
use DooglysAdapter\Models\Traits\GetById;
use DooglysAdapter\Models\Traits\Delete;
use DooglysAdapter\Models\Traits\Save;

class Order extends BaseEntity implements IGetById, IGetList, ISave, IDelete {

    use GetById, GetList, Save, Delete;

    const LIST_URI = '/api/v1/sales/order/list';
    const UNIT_URI = '/api/v1/sales/order/view/{id}';
    const CREATE_URI = '/api/v1/sales/order/create';
    const UPDATE_URI = '/api/v1/sales/order/update/{id}';
    const DELETE_URI = '/api/v1/sales/order/delete/{id}';

    private array $products = [];
    private string $defaultSource = 'external';

    public function addProduct( Product $product ) : ProductOrder {
        $productOrder = new ProductOrder( $product );
        $this->products[] = $productOrder;
        return $productOrder;
    }

    public function removeProduct( ProductOrder $productOrder ) : self{
        $key = array_search($productOrder, $this->products, true);
        if ( $key )
            unset($this->products[$key]);
        return $this;
    }

    public function setOrdered() : self{
        if ( !$this->id )
            throw new EmptyElementException('Dooglys-adapter error: Attempt to set ordered an empty element');

        $uri = str_replace('{id}', $this->id, self::UPDATE_URI);

        $params = [
            'order_status' => 'ordered',
            'id' => $this->id
        ];

        $this->connector->send( $uri, 'POST', $params);

        return $this;
    }

    public function cancelOrder() : self{
        if ( !$this->id )
            throw new EmptyElementException('Dooglys-adapter error: Attempt to cancel an empty order');

        $uri = str_replace('{id}', $this->id, self::UPDATE_URI);

        $params = [
            'order_status' => 'canceled',
            'user_cancelled_id' => $this->user_id,
            'date_returned' => time(),
            'id' => $this->id
        ];

        $this->connector->send( $uri, 'POST', $params);

        return $this;
    }

    public function completeOrder() : self{
        if ( !$this->id )
            throw new EmptyElementException('Dooglys-adapter error: Attempt to complete an empty order');

        $uri = str_replace('{id}', $this->id, self::UPDATE_URI);

        $params = [
            'order_status' => 'completed',
            'id' => $this->id
        ];

        $this->connector->send( $uri, 'POST', $params);

        return $this;
    }

    private function createEntity(){
        $uri = self::CREATE_URI;

        $this->id = $this->generator->generate();

        if ( !$this->date_created )
            $this->date_created = time();

        if ( !$this->source )
            $this->source = $this->defaultSource;

        if ( !$this->order_status )
            $this->order_status = 'ordered';

        if ( !$this->order_items )
            $this->setOrderPriceParams();

        $this->connector->send($uri, 'POST', $this->getParams());
    }

    private function setOrderPriceParams(){

        $this->items_cost = 0;
        $this->total_cost = 0;

        $this->discount_value = 0;
        $this->discount_percent = 0;

        $order_items = [];

        foreach ($this->products as $product):

            $items_cost = $product->getPrice() * $product->getQuantity();
            $discount_value = $product->getDiscountValue();
            $total_cost = $items_cost - $discount_value;

            $this->items_cost += $items_cost;
            $this->discount_value += $discount_value;
            $this->total_cost += $total_cost;

            $order_items[] = [
                "product_id" => $product->getProductId(),
                "product_name" => $product->getProductName(),
                "quantity" =>  $product->getQuantity(),
                "price" => $product->getPrice(),
                "total_cost" => $total_cost,
                "item_cost" => $items_cost,
                "discount_value" => $discount_value,
            ];
        endforeach;

        if ( $this->delivery_cost )
            $this->total_cost += $this->delivery_cost;

        $this->order_items = $order_items;
    }

}