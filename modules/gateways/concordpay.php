<?php

// Prepend direct access.
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once dirname(__FILE__) . "/concordpay/ConcordPayApi.php";

/**
 * @return string[]
 */
function concordpay_MetaData()
{
    return array(
        'DisplayName'                => 'ConcordPay',
        'APIVersion'                 => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage'           => false,
    );
}

/**
 * Admin menu for payment gateway.
 *
 * @return array
 */
function concordpay_config()
{
    return array(
        // The friendly display name for a payment gateway should be
        // defined here for backwards compatibility.
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'ConcordPay',
        ),
        // Text field type allows for single line text input.
        'merchant_id' => array(
            'FriendlyName' => 'Merchant ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Merchant ID',
        ),
        // Password field type allows for masked text input.
        'secret_key' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Secret key',
        ),
        // Dropdown field type renders a select menu of options
        'language' => array(
            'FriendlyName' => 'Select Language',
            'Type' => 'dropdown',
            'Options' => array(
                'ua' => 'UA',
                'ru' => 'RU',
                'en' => 'EN'
            ),
            'Description' => 'Choose payment page language',
        ),
        'currency' => array(
            'FriendlyName' => 'Select Default Currency',
            'Type' => 'dropdown',
            'Options' => array(
                'UAH' => 'Ukrainian Hryvnia',
                'USD' => 'US Dollar',
                'EUR' => 'Euro'
            ),
            'Description' => 'Choose currency',
        ),
    );
}

/**
 * Generate payment form.
 *
 * @param $params
 * @return string
 */
function concordpay_link($params)
{
    $concordpay = new ConcordPayApi($params['secret_key']);

    // Gateway Configuration Parameters
    $merchant_id = $params['merchant_id'];
    $secret_key = $params['secret_key'];

    // Invoice Parameters
    $invoiceId    = $params['invoiceid'];
    $amount       = $params['amount'];
    $currency_iso = $params['currency'];

    // Client Parameters
    $email = $params['clientdetails']['email'] ?? '';
    $phone = ($params['clientdetails']['phonecc'] . $params['clientdetails']['phonenumber']) ?? '' ;

    $client_first_name = $params['clientdetails']['firstname'] ?? '';
    $client_last_name  = $params['clientdetails']['lastname'] ?? '';

    $langPayNow = $params['langpaynow'];
    $moduleName = $params['paymentmethod'];

    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $systemUrl . 'modules/gateways/concordpay/result.php?result=';

    // Invoice Parameters (additional)
    $description = "Оплата карткою на сайті $systemUrl, $client_first_name $client_last_name, $phone";

    $request = [
        'operation'    => 'Purchase',
        'merchant_id'  => $merchant_id,
        'amount'       => $amount,
        'order_id'     => $invoiceId . ConcordPayApi::ORDER_SEPARATOR . time(),
        'currency_iso' => $currency_iso,
        'description'  => $description,
        'approve_url'  => $returnUrl . 'success',
        'decline_url'  => $returnUrl . 'fail',
        'cancel_url'   => $returnUrl . 'cancel',
        'callback_url' => $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php',
        'language'     => $params['language'],
        // Statistics.
        'client_last_name'  => $client_last_name,
        'client_first_name' => $client_first_name,
        'email'             => $email,
        'phone'             => $phone
    ];

    $request['signature'] = $concordpay->getRequestSignature($request);

    $url = ConcordPayApi::getApiUrl();

    $html = "<form method='post' action='$url'>";
    foreach ($request as $key => $value) {
        $html .= "<input type='hidden' name='$key' value='$value'/>";
    }
    $html .= "<input type='submit' class='btn btn-action' value='$langPayNow'/>";
    $html .= '</form>';

    return $html;
}
