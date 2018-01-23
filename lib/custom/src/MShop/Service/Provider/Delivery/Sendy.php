<?php 

namespace Aimeos\MShop\Service\Provider\Delivery;
 
class Sendy
    extends \Aimeos\MShop\Service\Provider\Delivery\Base
    implements \Aimeos\MShop\Service\Provider\Delivery\Iface
{
    /**
     * Sends the order details to the ERP system for further processing.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $order Order invoice object to process
     */
    public function process( \Aimeos\MShop\Order\Item\Iface $order )
    {
    }
}