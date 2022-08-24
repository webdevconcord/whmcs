<?php

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../../../modules/gateways/concordpay/ConcordPayApi.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

if (empty($_POST)) {
    $response = json_decode(file_get_contents("php://input"), true);
    $_POST = array();
    foreach ($response as $key => $value) {
        $_POST[$key] = $value;
    }
}

// Retrieve data from callback.
list($invoiceId) = explode("#", $_POST["orderReference"]);
$transactionId   = $_POST["transactionId"];
$paymentAmount   = $_POST["amount"];

/**
 * Validate callback authenticity.
 *
 * Most payment gateways provide a method of verifying that a callback
 * originated from them. In the case of our example here, this is achieved by
 * way of a shared secret which is used to build and compare a hash.
 */

$concordpaySettings['MERCHANT']   = $gatewayParams['merchant_id'];
$concordpaySettings['SECURE_KEY'] = $gatewayParams['secret_key'];

$concordpay = new ConcordPayApi($concordpaySettings['SECURE_KEY']);

$signature = $concordpay->getResponseSignature($_POST);

if ($signature === $_POST['merchantSignature']) {
    switch ($_POST['transactionStatus']) {
        case ConcordPayApi::TRANSACTION_STATUS_APPROVED:
            $transactionStatus = 'success';
            break;
        case ConcordPayApi::TRANSACTION_STATUS_DECLINED:
            $transactionStatus = 'failure';
            break;
        default:
            $transactionStatus = 'unhandled concordpay order status';
            break;
    }
}

$transactionStatus = 'failure';
if (isset($_POST['type']) && in_array($_POST['type'], ConcordPayApi::getAllowedOperationTypes())) {
    if ($_POST['type'] === ConcordPayApi::RESPONSE_TYPE_PAYMENT) {
        $transactionStatus = 'success';
    } elseif ($_POST['type'] === ConcordPayApi::RESPONSE_TYPE_REVERSE) {
        $transactionStatus = 'success';
    }
}

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int    $invoiceId   Invoice ID
 * @param string $gatewayName Gateway Name
 */
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 *
 * @param string $transactionId Unique Transaction ID
 */
checkCbTransID($transactionId);

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string       $gatewayName       Display label
 * @param string|array $debugData         Data to log
 * @param string       $transactionStatus Status
 */
logTransaction($gatewayParams['name'], $_POST, $transactionStatus);

if ($transactionStatus === 'success') {
    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int    $invoiceId      Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float  $paymentAmount  Amount paid (defaults to full balance)
     * @param float  $paymentFee     Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment($invoiceId, $transactionId, $paymentAmount, 0, $gatewayModuleName);
}
