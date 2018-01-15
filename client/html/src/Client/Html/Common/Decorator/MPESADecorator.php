<?php 
namespace Aimeos\Client\Html\Common\Decorator;
 
class MPESADecorator
    extends \Aimeos\Client\Html\Common\Decorator\Base
    implements \Aimeos\Client\Html\Common\Decorator\Iface
{
    private $view;
 
    public function getBody( $uid = '', array &$tags = array(), &$expire = null )
    {
        $this->setView( $this->setViewParams( $this->getView(), $tags, $expire ) );
        return $this->getClient()->getBody( $uid, $tags, $expire );
    }
 
    public function getHeader( $uid = '', array &$tags = array(), &$expire = null )
    {
        $this->setView( $this->setViewParams( $this->getView(), $tags, $expire ) );
        return $this->getClient()->getHeader( $uid, $tags, $expire );
    }
 
    protected function setViewParams( \Aimeos\MW\View\Iface $view, array &$tags = array(), &$expire = null )
    {
        if( $this->view === null )
        {
            // fetch the supplier item from the database
            $view->mydecorator_account_number = 'Actual account Number';
            $view->mydecoratorTotal = '24000';
 
            $this->view = $view;
        }
 
        return $this->view;
    }
}