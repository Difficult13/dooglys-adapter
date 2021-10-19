<?php

namespace DooglysAdapter\Models;

class ProductOrder{

    private Product $product;
    private float $price;
    private float $discount_value = 0;
    private float $discount_percent = 0;
    private int $quantity = 1;
    private string $name;

    public function __construct( Product $product){
        $this->product = $product;
        $this->price = (float) $product->price;
        $this->name = (string) $product->name;
    }

    public function setDiscountPercent( float $percent ) : self{
        if ( $percent < 0 || $percent > 100 )
            throw new \InvalidArgumentException('The number of percentages should be from 0 to 100');
        $this->discount_percent = $percent;
        $this->discount_value = 0;
        return $this;
    }

    public function setDiscountValue( float $value ) : self{
        if ( $value > $this->price )
            throw new \InvalidArgumentException('The discount value should not be greater than the product price');
        $this->discount_percent = 0;
        $this->discount_value = $value;
        return $this;
    }

    public function getPrice() : float{
        return $this->price;
    }

    public function setPrice( float $price ) : self{
        $this->price = $price;
        return $this;
    }

    public function getQuantity() : int{
        return $this->quantity;
    }

    public function setQuantity( int $quantity ) : self{
        $this->quantity = $quantity;
        return $this;
    }

    public function getDiscountValue() : float {
        if ( $this->discount_value )
            return $this->discount_value * $this->quantity;
        if ( $this->discount_percent )
            return $this->price * ($this->discount_percent / 100) * $this->quantity;
        return 0;
    }

    public function getProductId() : string {
        return $this->product->id;
    }

    public function setProductName( string $name ) : self{
        $this->name = $name;
        return $this;
    }

    public function getProductName() : string {
        return $this->name;
    }
}