# Dooglys Adapter

Dooglys Adapter is a library for convenient work with the external API of Dooglys for any PHP application.

## Requirements

- PHP >= 7.0
- An arbitrary class autoloader implementing the PSR-4 standard.

## Installation

Installation via composer:
```
$ composer require dooglys-adapter/dooglys-adapter
```

## Introduction

You can find the documentation for Dooglys here API: https://dooglys.com/support/api/

At the moment, the library supports: 

- Create/Read/Update/Delete methods for products, product categories and sale points.

- Create/Read/Update methods for orders.

- Read method for users, warehouses.

The library does not support working with the terminal menu and the loyalty program.

## General concept

For the convenience of working with the library, it was decided to build it on the principle of ORM.
Thus, each Dooglys entity has its own model in the library.
It all starts with the DooglysAdapter class:

```php
<?php
require '../vendor/autoload.php';

use DooglysAdapter\DooglysAdapter;

$accessToken = 'your-access-token';
$domain = 'your-ternant-domain';
try {
    $dooglys = new DooglysAdapter($accessToken, $domain);
    //...
}catch (\Exception $e){
    //..
}
```

Next, you can create an instance of an available entity in two ways - either through the builder:

```php
$user = $dooglys->build('user');
```

or through the appropriate method:

```php
$user = $dooglys->user();
```

All available entities will be listed later.

Models have similar methods for convenience:

`getById( string $id) : array` - returns all the parameters of this entity from Dooglys or throws an exception with an error.

`getList( array $params ) : array` - returns an array of objects of the class of the corresponding entity, for example, Users. In case of any errors, an exception will be thrown.
The list of supported parameters for the `getList` method can be found on the official website of Dooglys.

`save() : self` - The method works differently depending on the presence of the `id` parameter. If the `id` is set, the entity will be saved in Dooglys with its current parameters. If the 'id` is not set, a new entity with the current parameters will be created in Dooglys. In case of any errors, an exception will be thrown.

`delete() : void` - The method removes the entity from Dooglys or throws an error exception.

The models also include methods for manipulating their parameters `getParams(): array`, `getParam(string $key)`, `setParams(array $params) : self`, `setParam(string $key, $value) : self`, `clearParams() : self`, `deleteParameter (string $key) : self`.

But for if you want you can use direct access to properties thanks to magic methods:

```php
var_dump($product->price);

$product->price = 150;
```

> As you have already understood, the library will throw exceptions for any unexpected behavior.

## Manual

### Dooglys Setting

You can get the current settings of your Dooglys using the `getSettings` command:

```php
var_dump($dooglys->getSettings());
```

### User

You can get a list of users or a specific user:
```php
//Get by ID
$user_id = 'your-user-id';
$user = $dooglys->user()->getById($user_id);
var_dump($user);

//Get List
$users = $dooglys->user()->getList();
var_dump($users);
```

### Warehouse

You can get a list of warehouses or a specific warehouse:

```php
//Get by ID
$warehouse_id = 'your-warehouse-id';
$warehouse = $dooglys->warehouse()->getById($warehouse_id);
var_dump($warehouse);

//Get List
$warehouses = $dooglys->warehouse()->getList();
var_dump($warehouses);
```

### SalePoint

You can Create/Read/Update/Delete and get list of sale points:

```php
//Get by ID
$salepointId = 'your-sale-point-id';
$salepoint = $dooglys->salepoint()->getById($salepointId);
var_dump($salepoint);

//Get List
$salepoints = $dooglys->salepoint()->getList();
var_dump($salepoints);

//Create
$salepoint = $dooglys->salepoint()
    ->setParam('name', 'Test Sale Point')
    ->setParam('organization_id', 'your-organization-id')
    ->save();

//Update
$salepointId = 'your-sale-point-id';
$salepoint = $dooglys->salepoint()
    ->getById($salepointId)
    ->setParam('name', 'Test Sale Point 123')
    ->save();
    
//Delete
$salepointId = 'your-sale-point-id';
$dooglys->salepoint()->getById($salepointId)->delete();
```

The required parameters for creation are: 
- `name`
- `organiztion_id`

### Product

You can Create/Read/Update/Delete and get list of product:

```php
//Get by ID
$productId = 'your-product-id';
$product = $dooglys->product()->getById($productId);
var_dump($product);

//Get List
$products = $dooglys->product()->getList();
var_dump($products);

//Create
$product = $dooglys->product()
    ->setParam('name', 'Test Product')
    ->setParam('product_category_id', 'your-category-id')
    ->save();

//Update
$productId = 'your-product-id';
$product = $dooglys->product()
    ->getById($productId)
    ->setParam('name', 'Test Product 123')
    ->save();
    
//Delete
$productId = 'your-product-id';
$dooglys->product()->getById($productId)->delete();
```

The required parameters for creation are: 
- `name`
- `product_category_id`

### Product Category

You can Create/Read/Update/Delete and get list of product category:

```php
//Get by ID
$categoryId = 'your-category-id';
$category = $dooglys->category()->getById($categoryId);
var_dump($category);

//Get List
$categories = $dooglys->category()->getList();
var_dump($categories);

//Create
$category = $dooglys->category()
    ->setParam('name', 'Test Category')
    ->setParam('product_type', 'product')
    ->save();

//Update
$categoryId = 'your-category-id';
$category = $dooglys->category()
    ->getById($categoryId)
    ->setParam('name', 'Test Category 123')
    ->save();
    
//Delete
$categoryId = 'your-category-id';
$dooglys->category()->getById($categoryId)->delete();
```

The required parameters for creation are: 
- `name`
- `product_type`

### Order

You can Create/Read/Update and get list of order:

```php
//Get by ID
$orderId = 'your-order-id';
$order = $dooglys->order()->getById($orderId);
var_dump($order);

//Get List
$orders = $dooglys->order()->getList();
var_dump($orders);

//Update
$orderId = 'your-order-id';
$order = $dooglys->order()
    ->getById($orderId)
    ->setParam('number', '1a')
    ->save();
```
Let's look at the process of creating an order in more detail.
For ease of use, it was decided to develop several auxiliary methods.

So let's go:

```php
//Get the products
$pizza = $dooglys->product()->getById('pizza-id');
$roll = $dooglys->product()->getById('roll-id');

//We will put the products in the order, as in the basket
$pizza_in_order = $order
                    ->addProduct($pizza);
//We put one pizza in the order. The cost and name of this pizza is set from the pizza object. There is no discount.

$roll_in_order = $order
    ->addProduct($roll)
    ->setProductName('Roll in order')
    ->setPrice(55)
    ->setQuantity(5)
    ->setDiscountPercent(10);
//We specified the price and name of this roll manually, and also indicated a percentage discount. The discount can be specified as a value using the function `setDiscountValue`


//We can delete the item added earlier to the order
$order->removeProduct($roll_in_order);

//Next, we prescribe the order parameters
$order
    ->setParam('number', '1')
    ->setParam('user_id', 'your-user-id')
    ->setParam('sale_point_id', 'your-sale-point-id')
    ->setParam('order_type', 'delivery')
    ->setParam('first_name', 'client-first-name')
    ->setParam('last_name', 'client-last-name')
    ->setParam('phone', 'client-phone')
    ->setParam('payment_type', 'cash')
    ->setParam('comment', 'client-comment')
    ->setParam('address', 'delivery-address')
    ->setParam('entrance', '1')
    ->setParam('floor', '4')
    ->setParam('apartment', '22')
    ->setParam('delivery_cost', '15')
    ->save();
```

If you used auxiliary methods, the library will automatically calculate the cost of the order, taking into account discounts and delivery
Otherwise, you will have to fill in these parameters yourself.
For more information about the order parameters, see the official website of Dooglys.

The required parameters for creation are:
- `number`
- `user_id`
- `sale_point_id`
- `order_type`
- `first_name` - for delivery type
- `phone` - for delivery type
- `date_created` - installed by the library automatically
- `order_items` - installed by the library automatically if auxiliary methods are used 
- `items_cost` - installed by the library automatically if auxiliary methods are used
- `total_cost` - installed by the library automatically if auxiliary methods are used

There are three more methods for managing order statuses:
```php
//Initial status
$order->setOrdered();
//Cancellation status
$order->cancelOrder();
//Completion status
$order->completeOrder();
```