<?php 
namespace Aimeos\MShop\Service\Provider\Decorator;
 
class MPESADecorator
	extends \Aimeos\MShop\Service\Provider\Decorator\Base
	implements \Aimeos\MShop\Service\Provider\Decorator\Iface
{
	public function addData( \Aimeos\MW\View\Iface $view, array &$tags = array(), &$expire = null )
    {
        $view = parent::addData( $view, $tags, $expire );
 
        // access already added data
        $products = $view->get( 'listsItems', [] );
 
        // fetch some items from the database
        $view->mydecoratorMyparam = 'Supuu';
        $view->mydecoratorTotal = '2349900';
 
        return $view;
    }
    public function calcPrice( \Aimeos\MShop\Order\Item\Base\Iface $basket )
	{
	    // do something before
	    $price = $this->getProvider()->calcPrice( $basket );
		$price->setCosts( $price->getCosts() + 1000);

	    // do something after
	    return $price;
	}
}