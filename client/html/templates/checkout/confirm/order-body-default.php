<?php

/**
 * @license LGPLv3, http://www.gnu.org/licenses/lgpl.php
 * @copyright Aimeos (aimeos.org), 2015-2017
 */

$enc = $this->encoder();

$addresses = $this->summaryBasket->getAddresses();
$services = $this->summaryBasket->getServices();


?>
<?php $this->block()->start( 'checkout/confirm/order' ); ?>
<div class="checkout-confirm-detail common-summary">
	<div class="common-summary-detail container">
		<div class="header">
			<h3><?= $enc->html( $this->translate( 'client', 'Order Details' ), $enc::TRUST ); ?></h3>
		</div>

		<div class="basket">
			<?= $this->partial(
				/** client/html/checkout/confirm/summary/detail
				 * Location of the detail partial template for the confirmation component
				 *
				 * To configure an alternative template for the detail partial, you
				 * have to configure its path relative to the template directory
				 * (usually client/html/templates/). It's then used to display the
				 * product detail block on the confirm page during the checkout process.
				 *
				 * @param string Relative path to the detail partial
				 * @since 2017.01
				 * @category Developer
				 * @see client/html/checkout/confirm/summary/address
				 * @see client/html/checkout/confirm/summary/service
				 */
				$this->config( 'client/html/checkout/confirm/summary/detail', 'common/summary/detail-default.php' ),
				array(
					'summaryBasket' => $this->summaryBasket,
					'summaryTaxRates' => $this->get( 'summaryTaxRates' ),
					'summaryShowDownloadAttributes' => $this->get( 'summaryShowDownloadAttributes' ),
				)
			); ?>
		</div>
	</div>

</div>
<?php $this->block()->stop(); ?>
<?= $this->block()->get( 'checkout/confirm/order' ); ?>
