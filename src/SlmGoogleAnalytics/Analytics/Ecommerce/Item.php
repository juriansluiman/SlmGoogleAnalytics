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

class Item
{
    protected $sku;
    protected $price;
    protected $quantity;
    protected $product;
    protected $category;

    public function __construct ($sku, $price, $quantity, $product = null, $category = null)
    {
        $this->setSku($sku);
        $this->setPrice($price);
        $this->setQuantity($quantity);

        if (null !== $product) {
            $this->setProduct($product);
        }

        if (null !== $category) {
            $this->setCategory($category);
        }
    }

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