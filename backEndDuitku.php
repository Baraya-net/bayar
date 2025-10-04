<?php
// BAGIAN SERVER (BACKEND)
// Kode ini hanya akan berjalan jika ada permintaan POST dari JavaScript di bawah
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set header sebagai JSON karena AJAX mengharapkan response JSON
    header('Content-Type: application/json');

    // --- Ambil Kredensial & Konfigurasi ---
    // Gunakan Kode Merchant dari info teks Anda
    $merchantCode = 'DS25287'; 
    // Gunakan Merchant Key dari file yang Anda berikan
    $merchantKey = '32d50d1ffd04213b5435877c65d6fd0e'; 
    // Arahkan ke Sandbox Duitku untuk testing
    $url = 'https://api-sandbox.duitku.com/api/merchant/createInvoice';

    // --- Ambil Data dari AJAX Request ---
    $paymentAmount = isset($_POST['paymentAmount']) ? (int)$_POST['paymentAmount'] : 0;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $phoneNumber = isset($_POST['phoneNumber']) ? $_POST['phoneNumber'] : null;
    $productDetails = isset($_POST['productDetail']) ? $_POST['productDetail'] : 'Test Product';
    
    // Buat ID Order yang unik
    $merchantOrderId = time(); 

    // --- URL Callback & Return ---
    // URL ini akan dipanggil oleh server Duitku setelah pembayaran selesai
    $callbackUrl = 'https://baraya.topsetting.com/billing/duitku-checkout.php';
    // URL ini adalah halaman tujuan customer setelah menyelesaikan pembayaran
    $returnUrl = 'https://baraya.topsetting.com:973/rad-admin';

    // --- Detail Customer & Item ---
    $customerVaName = 'John Doe';
    $firstName = "John";
    $lastName = "Doe";
    $address = array(
        'firstName' => $firstName, 'lastName' => $lastName, 'address' => 'Jl. Kembangan Raya',
        'city' => 'Jakarta', 'postalCode' => '11530', 'phone' => $phoneNumber, 'countryCode' => 'ID'
    );
    $customerDetail = array(
        'firstName' => $firstName, 'lastName' => $lastName, 'email' => $email, 'phoneNumber' => $phoneNumber,
        'billingAddress' => $address, 'shippingAddress' => $address
    );
    $itemDetails = array(
        array('name' => $productDetails, 'price' => $paymentAmount, 'quantity' => 1)
    );

    // --- Buat Payload untuk API Duitku ---
    $params = array(
        'merchantCode' => $merchantCode,
        'paymentAmount' => $paymentAmount,
        'merchantOrderId' => (string)$merchantOrderId,
        'productDetails' => $productDetails,
        'customerVaName' => $customerVaName,
        'email' => $email,
        'phoneNumber' => $phoneNumber,
        'itemDetails' => $itemDetails,
        'customerDetail' => $customerDetail,
        'callbackUrl' => $callbackUrl,
        'returnUrl' => $returnUrl,
        'expiryPeriod' => 10 // dalam menit
    );

    // --- Kirim Request ke Duitku menggunakan cURL ---
    $timestamp = round(microtime(true) * 1000);
    $signature = hash('sha256', $merchantCode . $timestamp . $merchantKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($params)),
        'x-duitku-signature:' . $signature,
        'x-duitku-timestamp:' . $timestamp,
        'x-duitku-merchantcode:' . $merchantCode
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // --- Kirimkan response dari Duitku kembali ke JavaScript ---
    echo $response;

    // Hentikan eksekusi script agar tidak mengirimkan HTML di bawah
    exit;
}
?>
