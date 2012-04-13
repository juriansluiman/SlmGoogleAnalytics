<?php

namespace SlmGoogleAnalytics\Analytics\Ecommerce;

class Transaction
{
    protected $id;
    protected $affiliation;
    protected $total;
    protected $tax;
    protected $shipping;
    protected $city;
    protected $state;
    protected $country;
    
    protected $items;
    
    public function __construct ($id, $total)
    {
        $this->setId($id);
        $this->setTotal($total);
    }
    
    public function getId ()
    {
        return $this->id;
    }

    public function setId ($id)
    {
        $this->id = $id;
    }

    public function getAffiliation ()
    {
        return $this->affiliation;
    }

    public function setAffiliation ($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    public function getTotal ()
    {
        return $this->total;
    }

    public function setTotal ($total)
    {
        $this->total = $total;
    }

    public function getTax ()
    {
        return $this->tax;
    }

    public function setTax ($tax)
    {
        $this->tax = $tax;
    }

    public function getShipping ()
    {
        return $this->shipping;
    }

    public function setShipping ($shipping)
    {
        $this->shipping = $shipping;
    }

    public function getCity ()
    {
        return $this->city;
    }

    public function setCity ($city)
    {
        $this->city = $city;
    }

    public function getState ()
    {
        return $this->state;
    }

    public function setState ($state)
    {
        $this->state = $state;
    }

    public function getCountry ()
    {
        return $this->country;
    }

    public function setCountry ($country)
    {
        $this->country = $country;
    }
    
    public function items ()
    {
        return $this->items;
    }
    
    public function addItem (Item $item)
    {
        if (null === $this->items) {
            $this->items = array();
        }
        
        $sku = $item->getSku();
        if (array_key_exists($sku, $this->items)) {
            $quantity = $this->items[$sku]->getQuantity()
                      + $item->getQuantity();
            
            $this->items[$sku]->setQuantity($quantity);
        } else {
            $this->items[$sku] = $item;
        }
    }
}