<?php
/**
 * Copyright (c) 2012-2013 Jurian Sluiman.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@juriansluiman.nl>
 * @copyright   2012-2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://juriansluiman.nl
 */
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