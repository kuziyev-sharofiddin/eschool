<?php

namespace App\Services\Payment;

use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Razorpay\Api\Api;

class RazorpayPayment implements PaymentInterface {
    private Api $api;
    private string $currencyCode;

    public function __construct($secretKey, $publicKey, $currencyCode) {
        // Call Stripe Class and Create Payment Intent
        $this->api = new Api($publicKey, $secretKey);
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param $amount
     * @param $customMetaData
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent($amount, $customMetaData) {
        try {
            // Convert amount to integer (in paise)
            $amount = $this->minimumAmountValidation($this->currencyCode, $amount);
            $amount = (float)$amount * 100;

            // Ensure all metadata values are strings and limit to 15 fields
            $notes = [];
            $count = 0;
            foreach ($customMetaData as $key => $value) {
                if ($count >= 15) break;
                $notes[$key] = (string)$value;
                $count++;
            }
            
            $paymentData = [
                'amount' => (int)$amount, // Ensure amount is integer
                'currency' => $this->currencyCode,
                'notes' => $notes,
                'payment_capture' => 1 // Auto capture the payment
            ];

            Log::info("Creating Razorpay Order:", $paymentData);
            $order = $this->api->order->create($paymentData);
            Log::info("Razorpay Order Created:", ['order' => $order->toArray()]);
            
            return $order;
        } catch (\Exception $e) {
            Log::error('Failed to create Razorpay order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $paymentId
     * @return array
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent($paymentId): array {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Razorpay payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $currency
     * @param $amount
     * @return float|int
     */
    public function minimumAmountValidation($currency, $amount) {
        $currencies = [
            'USD' => 0.50,
            'AED' => 2.00,
            'AUD' => 0.50,
            'BGN' => 1.00,
            'BRL' => 0.50,
            'CAD' => 0.50,
            'CHF' => 0.50,
            'CZK' => 15.00,
            'DKK' => 2.50,
            'EUR' => 0.50,
            'GBP' => 0.30,
            'HKD' => 4.00,
            'HUF' => 175.00,
            'INR' => 1.00,
            'JPY' => 50,
            'MXN' => 10,
            'MYR' => 2.00,
            'NOK' => 3.00,
            'NZD' => 0.50,
            'PLN' => 2.00,
            'RON' => 2.00,
            'SEK' => 3.00,
            'SGD' => 0.50,
            'THB' => 10,
            'ZAR' => 10,
        ];

        if ($amount <= 0) {
            return 0;
        }

        if (isset($currencies[$currency])) {
            return max($currencies[$currency], $amount);
        }

        return $amount;
    }

    /**
     * @param $amount
     * @param $customMetaData
     * @return array
     * @throws ApiErrorException
     */
    public function createAndFormatPaymentIntent($amount, $customMetaData): array {
        $order = $this->createPaymentIntent($amount, $customMetaData);
        return [
            'id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'status' => $order->status,
            'notes' => $order->notes,
            'raw' => $order->toArray()
        ];
    }

    /**
     * @param $paymentIntent
     * @return array
     */
    public function formatPaymentIntent($id, $amount, $currency, $status, $metadata, $paymentIntent): array {
        return [
            'id' => $id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'created_at' => $paymentIntent->created_at,
            'notes' => $paymentIntent->notes,
        ];
    }
}