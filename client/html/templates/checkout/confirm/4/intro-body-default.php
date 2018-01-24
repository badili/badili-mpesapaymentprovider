<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2017
 */

$enc = $this->encoder();
$total = number_format($this->get( 'mydecoratorTotal'));
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
	?>
	<p class="note"><?= nl2br( $enc->html( $this->translate( 'client', "Your order of $all_products_names Act(s) was placed successfully and a payment of KShs. $total is pending in order to complete the transaction."), $enc::TRUST ) ); ?></p>
<?= $this->get( 'introBody' ); ?>
	<?php echo $this->partial( 'checkout/partials/pending-payment.php',['total' => $total, 'account_number' => $account_number ]); ?>
</div>
<?php $this->block()->stop(); ?>
<?= $this->block()->get( 'checkout/confirm/intro' ); ?>
