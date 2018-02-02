<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2017
 */

$enc = $this->encoder();
$account_number = $this->get( 'mydecorator_account_number');

?>
<?php $this->block()->start( 'checkout/confirm/intro' ); ?>

<div class="checkout-confirm-intro">
	<?php
		// Create the name of all the products as one string
		$all_products_names = '';
		foreach( $this->summaryBasket->getProducts() as $position => $product ) {
			$all_products_names .= $product->getName().', ';
		}
		$currency = $this->summaryBasket->getServices()['payment']->getPrice()->getCurrencyId();
		$price = $this->summaryBasket->getPrice();
	    $country = $this->summaryBasket->getAddresses()['payment']->getCountryId();
		if ($country == "KE") {
	    	// Do nothing i.e include all the VAT
	    	$total = $price->getValue() + $price->getCosts() + $price->getTaxValue();
	    }
	    else {
	    	// Remove the tax, only charge the costs and price
		    $total = $price->getValue() + $price->getCosts();
	    }
		$total = number_format($total );
	?>
		<p class="note"><?= nl2br( $enc->html( $this->translate( 'client', "Your order of $all_products_names Act(s) was placed successfully and a payment of $currency, $total is pending in order to complete the transaction."), $enc::TRUST ) ); ?></p>
<?= $this->get( 'introBody' ); ?>
</div>
<?php $this->block()->stop(); ?>
<?= $this->block()->get( 'checkout/confirm/intro' ); ?>
