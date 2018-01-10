<?php 

namespace Aimeos\MShop\Service\Provider\Payment;
 
class MPesa
	extends \Aimeos\MShop\Service\Provider\Payment\Base
	implements \Aimeos\MShop\Service\Provider\Payment\Iface
{
    /**
     * Tries to get an authorization or captures the money immediately for the given
     * order if capturing isn't supported or not configured by the shop owner.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $order Order invoice object
     * @param array $params Request parameter if available
     * @return \Aimeos\MShop\Common\Item\Helper\Form\Standard Form object with URL, action
     *  and parameters to redirect to	(e.g. to an external server of the payment
     *  provider or to a local success page)
     */
    public function process( \Aimeos\MShop\Order\Item\Iface $order, array $params = array() )
    {
        $basket = $this->getOrderBase( $order->getBaseId() );
        $total = $basket->getPrice()->getValue() + $basket->getPrice()->getCosts();
     
        // send the payment details to an external payment gateway
     
        $status = \Aimeos\MShop\Order\Item\Base::PAY_RECEIVED;
        $order->setPaymentStatus( $status );
        $this->saveOrder( $order );
     
        return parent::process( $order, $params );
    }
}