<?php 
namespace Aimeos\MShop\Service\Provider\Payment;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Mail;
use \App\Models\User;
use \App\Models\BankReferenceOrderMap;

define ('HMAC_SHA256', 'sha256');
define ('SECRET_KEY', '');


class CyberSource
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
 	$code = /*'5a6eec2b942a1' ;*/uniqid();
    $signed_date_time = /*'2018-01-29T09:40:59Z' ;*/ gmdate("Y-m-d\TH:i:s\Z");
    $reference_number = time();

    // define the payment information that should be sent to the external payment gateway
    $bank_reference_no_order_map = new BankReferenceOrderMap();
    $bank_reference_no_order_map->order_id =  $order->getId();
    $bank_reference_no_order_map->amount =  $total;
    $bank_reference_no_order_map->user_id =  Auth::user()->id;
    $bank_reference_no_order_map->reference_number = $reference_number;

    $bank_reference_no_order_map->save();
    $status = \Aimeos\MShop\Order\Item\Base::PAY_PENDING;
    $order->setPaymentStatus( $status );
    $this->saveOrder( $order );
    
    $params = array(
    	"access_key" => "f20c336db9c531e0a406951841f5ad41",
	    "profile_id" => "bbk_pwc_7326655_kes", 
	    "transaction_uuid" => $code,
	    "signed_field_names" => "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code",
	    "unsigned_field_names" => "card_type,card_number,card_expiry_date",
	    "signed_date_time" => $signed_date_time,
	    "locale" => 'en',
	    "transaction_type" => 'sale',
	    "reference_number" => $reference_number,
	    "amount" => $total,
	    "currency" => "KES",
	    "payment_method" => "card",
	    "bill_to_forename" => "John",
	    "bill_to_surname" => "Doe",
	    "bill_to_email" => "null@cybersource.com",
	    "bill_to_phone" => "02890888888",
	    "bill_to_address_line1" => "20303",
	    "bill_to_address_city" => "Nairobi",
	    "bill_to_address_state" => "Nyandarua",
	    "bill_to_address_country" => "KE",
	    "bill_to_address_postal_code" => "94043",
	);
    $signature = $this->sign($params);
    
    if( !isset( $params['creditcardprovider.access_key'] ) || $params['creditcard.profile_id'] == '' ) {
	    $list = array(
	    	// The Access Key
	        'creditcardprovider.access_key' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Access Key',
	            'code' => 'creditcardprovider.access_key',
	            'internalcode' => 'access_key',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'f20c336db9c531e0a406951841f5ad41',
	            'public' => false,
	        ) ),
	        // THe profile ID
	        'creditcard.profile_id' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Profile ID Cyber source',
	            'code' => 'creditcard.profile_id',
	            'internalcode' => 'profile_id',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'bbk_pwc_7326655_kes',
	            'public' => false,
	        ) ),
	        // The transacion UID
	        'creditcard.transaction_uuid' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Transaction ID',
	            'code' => 'creditcard.transaction_uuid',
	            'internalcode' => 'transaction_uuid',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => $code,
	            'public' => false,
	        ) ),
	        // The signed field names
	        'creditcard.signed_field_names' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Signed field names',
	            'code' => 'creditcard.signed_field_names',
	            'internalcode' => 'signed_field_names',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code',
	            'public' => false,
	        ) ),
	        // The unsigned field names
	        'creditcard.unsigned_field_names' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Unsigned field names',
	            'code' => 'creditcard.unsigned_field_names',
	            'internalcode' => 'unsigned_field_names',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'card_type,card_number,card_expiry_date',
	            'public' => false,
	        ) ),
	        // The signed date field
	        'creditcard.signed_date_time' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Signed date type ',
	            'code' => 'creditcard.signed_date_time',
	            'internalcode' => 'signed_date_time',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => $signed_date_time,
	            'public' => false,
	        ) ),
	        // The locale
	        'creditcard.locale' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'locale ',
	            'code' => 'creditcard.locale',
	            'internalcode' => 'locale',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'en',
	            'public' => false,
	        ) ),
	        // The transaction type
	        'creditcard.transaction_type' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'transaction_type',
	            'code' => 'creditcard.transaction_type',
	            'internalcode' => 'transaction_type',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'sale',
	            'public' => false,
	        ) ),
	        // The reference number
	        'creditcard.reference_number' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'reference_number',
	            'code' => 'creditcard.reference_number',
	            'internalcode' => 'reference_number',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => $reference_number,
	            'public' => false,
	        ) ),
	        // The amount
	        'creditcard.amount' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'amount',
	            'code' => 'creditcard.amount',
	            'internalcode' => 'amount',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => $total,
	            'public' => false,
	        ) ),
	        // The currency
	        'creditcard.currency' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'currency',
	            'code' => 'creditcard.currency',
	            'internalcode' => 'currency',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'KES',
	            'public' => false,
	        ) ),
	        // The payment method
	        'creditcard.payment_method' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'payment_method',
	            'code' => 'creditcard.payment_method',
	            'internalcode' => 'payment_method',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'card',
	            'public' => false,
	        ) ),
	    	// The user details
	    	'creditcardprovider.bill_to_forename' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_forename ',
	            'code' => 'creditcardprovider.bill_to_forename',
	            'internalcode' => 'bill_to_forename',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'John',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_surname' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_surname ',
	            'code' => 'creditcardprovider.bill_to_surname',
	            'internalcode' => 'bill_to_surname',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'Doe',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_email' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_email ',
	            'code' => 'creditcardprovider.bill_to_email',
	            'internalcode' => 'bill_to_email',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'null@cybersource.com',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_phone' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_phone ',
	            'code' => 'creditcardprovider.bill_to_phone',
	            'internalcode' => 'bill_to_phone',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '02890888888',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_address_line1' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_address_line1 ',
	            'code' => 'creditcardprovider.bill_to_address_line1',
	            'internalcode' => 'bill_to_address_line1',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '20303',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_address_city' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_address_city ',
	            'code' => 'creditcardprovider.bill_to_address_city',
	            'internalcode' => 'bill_to_address_city',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'Nairobi',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_address_state' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_address_state ',
	            'code' => 'creditcardprovider.bill_to_address_state',
	            'internalcode' => 'bill_to_address_state',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'Nyandarua',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_address_country' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_address_country ',
	            'code' => 'creditcardprovider.bill_to_address_country',
	            'internalcode' => 'bill_to_address_country',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'KE',
	            'public' => false,
	        ) ),
	        'creditcardprovider.bill_to_address_postal_code' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'bill_to_address_postal_code ',
	            'code' => 'creditcardprovider.bill_to_address_postal_code',
	            'internalcode' => 'bill_to_address_postal_code',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '94043',
	            'public' => false,
	        ) ),
	        //The submit
	        'creditcard.submit' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'submit date type ',
	            'code' => 'creditcard.submit',
	            'internalcode' => 'submit',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => 'Submit',
	            'public' => false,
	        ) ),
	        // The signature
	        'creditcard.signature' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Signature',
	            'code' => 'creditcard.signature',
	            'internalcode' => 'signature',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => $signature,
	            'public' => false,
	        ) ),
	        // The card type
	        'creditcard.card_type' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'card_type  date times',
	            'code' => 'creditcard.card_type',
	            'internalcode' => 'card_type',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '001',
	            'public' => false,
	        ) ),
	        // The credit card number 
	        'creditcard.card_number' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Card number date times',
	            'code' => 'creditcard.card_number',
	            'internalcode' => 'card_number',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '4111111111111111',
	            'public' => false,
	        ) ),
	        // The expiry date
	        'creditcard.card_expiry_date' => new \Aimeos\MW\Criteria\Attribute\Standard( array(
	            'label' => 'Signed date times',
	            'code' => 'creditcard.card_expiry_date',
	            'internalcode' => 'card_expiry_date',
	            'internaltype' => 'string',
	            'type' => 'string',
	            'default' => '11-2020',
	            'public' => false,
	        ) ),
	    );
 	}
    $gatewayUrl = $this->getConfigValue( array( 'myprovider.url' ), 'https://testsecureacceptance.cybersource.com/silent/pay' );
    return new \Aimeos\MShop\Common\Item\Helper\Form\Standard( $gatewayUrl, 'POST', $list );
	}

	public function sign($params) {
	  return $this->signData($this->buildDataToSign($params), SECRET_KEY);
	}

	public function signData($data, $secretKey) {
	    return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

	public function buildDataToSign($params) {
	        $signedFieldNames = explode(",",$params["signed_field_names"]);
	        foreach ($signedFieldNames as $field) {
	           $dataToSign[] = $field . "=" . $params[$field];
	        }
	        return $this->commaSeparate($dataToSign);
	}

	public function commaSeparate($dataToSign) {
	    return implode(",",$dataToSign);
	}
}