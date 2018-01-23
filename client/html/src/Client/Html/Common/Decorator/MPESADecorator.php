<?php 
namespace Aimeos\Client\Html\Common\Decorator;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\DB;
use \App\Models\PaybillAccountNoOrderMap;

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
            // fetch the last record from the DB for the paybill account_number just recorded
            $paybill_account_number_order_map = DB::table('paybill_account_no_order_map')->where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first();
            if (isset($paybill_account_number_order_map)) {
                $view->mydecorator_account_number = $paybill_account_number_order_map->account_number;
                $view->mydecoratorTotal = $paybill_account_number_order_map->amount;
            }
            $this->view = $view;
        }
 
        return $this->view;
    }
}