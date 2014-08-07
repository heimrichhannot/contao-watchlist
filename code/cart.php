<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <title>Testing the Shopping Cart</title>
</head>
<body>
<?php # cart.php
// This script uses the ShoppingCart and Item classes.

// Create the cart:
try {

require('ShoppingCart.php');
$cart = new ShoppingCart();

// Create some items:
require('Item.php');
$w1 = new Item('W139', 'Some Widget', 23.45);
$w2 = new Item('W384', 'Another Widget', 12.39);
$w3 = new Item('W55', 'Cheap Widget', 5.00);

// Add the items to the cart:
$cart->addItem($w1);
$cart->addItem($w2);
$cart->addItem($w3);

// Update some quantities:
$cart->updateItem($w2, 4);
$cart->updateItem($w1, 1);

// Delete an item:
$cart->deleteItem($w3);

// Show the cart contents:
echo '<h2>Cart Contents (' . count($cart) . ' items)</h2>';

if (!$cart->isEmpty()) {

	foreach ($cart as $arr) {

		// Get the item object:
		$item = $arr['item'];

		// Print the item:
		printf('<p><strong>%s</strong>: %d @ $%0.2f each.<p>', $item->getName(), $arr['qty'], $item->getPrice());

	} // End of foreach loop!

} // End of IF.

} catch (Exception $e) {
// Handle the exception.
}
?>
</body>
</html>