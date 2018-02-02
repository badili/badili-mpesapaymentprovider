<?php 

namespace Aimeos\MShop\Service\Provider\Payment;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Mail;
use \App\Models\User;
use \App\Models\PaybillAccountNoOrderMap;

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
     *  and parameters to redirect to   (e.g. to an external server of the payment
     *  provider or to a local success page)
     */
    public function process( \Aimeos\MShop\Order\Item\Iface $order, array $params = array() )
    {
        // send the payment details to an external payment gateway
        $order_id = $order->getId();
        $basket = $this->getOrderBase( $order->getBaseId() );
        $paybill_account_number_for_transaction = $this->unique_id();
        $view = $this->getContext()->getView();
        
        // If the user address is not in Kenya remove the VAT from the price
        $price = $basket->getPrice();
        // Helpers to get the country code
        $controller = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'basket' );
        $parts = \Aimeos\MShop\Order\Manager\Base\Base::PARTS_ALL;
        $summaryBasket = $controller->load( $order->getBaseId(), $parts, false );
        $taxRates = $price->getTaxRate( $summaryBasket );

        $country = $summaryBasket->getAddresses()['payment']->getCountryId();
        if ($country == "KE") {
            // Do nothing i.e include all the VAT
            $total = $this->getAmount($price);
        }
        else {
            // Remove the tax, only charge the costs and price
            $total = $price->getValue() + $price->getCosts();
        }

        // associate the order with the paybill account number to be used , this
        // will be used on the /confirm method from the safaricom integration
        $new_paybill_acc_no_order_id_assoc = new PaybillAccountNoOrderMap();
        $new_paybill_acc_no_order_id_assoc->order_id =  $order_id;
        $new_paybill_acc_no_order_id_assoc->amount =  $total;
        $new_paybill_acc_no_order_id_assoc->user_id =  Auth::user()->id;
        $new_paybill_acc_no_order_id_assoc->account_number = $paybill_account_number_for_transaction;

        $new_paybill_acc_no_order_id_assoc->save();
        $status = \Aimeos\MShop\Order\Item\Base::PAY_PENDING;
        $order->setPaymentStatus( $status );
        $this->saveOrder( $order );

        // Send an email to the user with the order details
        $product_name = 'VAT Testing 2018'; // Get this from the DB
        $product_price = 2000; // Get this from the DB
        $product_quantity = $basket->getPrice()->getQuantity();
        $order_tax = $basket->getPrice()->getTaxValue();
        $delivery_price = $basket->getServices()['delivery']->getPrice()->getCosts();
        
        $user_id = Auth::user()->id;
        $user = User::where('id', $user_id)->first();
        $email_data = [];
        $email_data['user_full_name'] = $user->firstname.' '.$user->lastname;
        $email_data['user_email'] = $user->email;
        $email_data['amount'] = $total;
        $email_data['order_id'] = $order_id;
        $email_data['product_name'] = $product_name;
        $email_data['product_price'] = $product_price;
        $email_data['user_phone'] = $user->telephone;
        $email_data['user_address'] = $user->address1.' '.$user->postal;
        $email_data['user_city'] = $user->city;
        $email_data['user_country'] = $user->countryid;
        $email_data['account_number'] = $paybill_account_number_for_transaction;


        Mail::send('emails.mpesa-details', $email_data, function($msg) use ($email_data) {
            $msg->from('taxlawpundit@pwc.com', 'Pwc Tax Law Pundit');
            $msg->to($email_data['user_email']);
            $msg->subject('PwC Tax Law Pundit || Order #'.$email_data['order_id'].' MPESA Payment Details!');
        });

        // Update the context to include stuff we have added
        return parent::process( $order, $params );
    }
    public function unique_id(){
        $better_token = md5(uniqid(rand(), true));
        $uniqueid = strtoupper(substr($better_token, 0, 5));
        
        return $uniqueid;
    }
}