<?php
    $merchantCode = 'D0001'; // from duitku
    $merchantKey = '732B39FC61796845775D2C4FB05332AF'; // from duitku

	$paymentAmount = isset($_POST['paymentAmount']) ? $_POST['paymentAmount'] : null; // VC = Credit Card
	$email = isset($_POST['email']) ? $_POST['email'] : null; // your customer email
    $phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : null;// your customer phone number (optional)
	$productDetails = isset($_POST['productDetail']) ? $_POST['productDetail'] : null;
    $merchantOrderId = time(); // from merchant, unique   
    $additionalParam = ''; // optional
    $merchantUserInfo = ''; // optional
    $customerVaName = 'John Doe'; // display name on bank confirmation display
    $callbackUrl = 'http://example.com/callback'; // url for callback
    $returnUrl = 'http://localhost:8080/snap/contohDuitkuPopUi'; // url for redirect
    $expiryPeriod = 10; // set the expired time in minutes
    $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $merchantKey);

    // Customer Detail
    $firstName = "John";
    $lastName = "Doe";

    // Address
    $alamat = "Jl. Kembangan Raya";
    $city = "Jakarta";
    $postalCode = "11530";
    $countryCode = "ID";

    $address = array(
        'firstName' => $firstName,
        'lastName' => $lastName,
        'address' => $alamat,
        'city' => $city,
        'postalCode' => $postalCode,
        'phone' => $phoneNumber,
        'countryCode' => $countryCode
    );

    $customerDetail = array(
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'phoneNumber' => $phoneNumber,
        'billingAddress' => $address,
        'shippingAddress' => $address
    );


    $item1 = array(
        'name' => $productDetails,
        'price' => (int)$paymentAmount,
        'quantity' => 1);


    $itemDetails = array(
        $item1
    );

    $params = array(
        'merchantCode' => $merchantCode,
        'paymentAmount' => (int)$paymentAmount,
        'merchantOrderId' => (string)$merchantOrderId,
        'productDetails' => $productDetails,
        'additionalParam' => $additionalParam,
        'merchantUserInfo' => $merchantUserInfo,
        'customerVaName' => $customerVaName,
        'email' => $email,
        'phoneNumber' => $phoneNumber,
        'itemDetails' => $itemDetails,
        'customerDetail' => $customerDetail,
        'callbackUrl' => $callbackUrl,
        'returnUrl' => $returnUrl,
        'signature' => $signature,
        'expiryPeriod' => $expiryPeriod
    );

    $params_string = json_encode($params);
	$url = 'https://api-sandbox.duitku.com/api/merchant/createInvoice';
	
    $ch = curl_init();
	$timestamp = round(microtime(true) * 1000);
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($params_string),
			'x-duitku-signature:' . hash('sha256', $merchantCode.$timestamp.$merchantKey) ,
			'x-duitku-timestamp:' . $timestamp ,
			'x-duitku-merchantcode:' . $merchantCode	
		)                                                                       
    );   
	
	
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    //execute post
    $request = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($httpCode == 200)
    {
        $result = json_decode($request, true);
        //header('location: '. $result['paymentUrl']);
        echo $request;
    }
    else
        echo $request;
?>