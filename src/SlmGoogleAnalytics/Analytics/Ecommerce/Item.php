<?php

namespace SlmGoogleAnalytics\Analytics\Ecommerce;

class Item
{
    protected $sku;
    protected $product;
    protected $category;
    protected $price;
    protected $quantity;
    
    public function getSku ()
    {
        return $this->sku;
    }
    
    public function setSku ($sku)
    {
        $this->sku = $sku;
    }
    
    public function getProduct ()
    {
        return $this->product;
    }

    public function setProduct ($product)
    {
        $this->product = $product;
    }

    public function getCategory ()
    {
        return $this->category;
    }

    public function setCategory ($category)
    {
        $this->category = $category;
    }

    public function getPrice ()
    {
        return $this->price;
    }

    public function setPrice ($price)
    {
        $this->price = $price;
    }

    public function getQuantity ()
    {
        return $this->quantity;
    }

    public function setQuantity ($quantity)
    {
        $this->quantity = $quantity;
    }
}