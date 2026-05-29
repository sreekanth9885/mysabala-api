<?php

require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Razorpay\Api\Api;

class OrderController
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function createOrder()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $grandTotal = $data['grand_total'];

        $api = new Api(
            $_ENV['RAZORPAY_KEY_ID'],
            $_ENV['RAZORPAY_KEY_SECRET']
        );

        $razorpayOrder = $api->order->create([
            'receipt' => 'receipt_' . time(),

            'amount' => $grandTotal * 100,

            'currency' => 'INR'
        ]);

        Response::json([
            "razorpay_order_id" => $razorpayOrder['id'],
            "amount" => $grandTotal,
            "key" => $_ENV['RAZORPAY_KEY_ID']
        ]);
    }

    public function verifyPayment()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $customerName = $data['customer_name'];
        $phone = $data['phone'];

        $address = $data['address'];
        $city = $data['city'];
        $pincode = $data['pincode'];

        $subtotal = $data['subtotal'];
        $deliveryFee = $data['delivery_fee'];
        $gst = $data['gst'];
        $grandTotal = $data['grand_total'];

        $cartItems = $data['cart_items'];

        $paymentId = $data['razorpay_payment_id'];
        $orderId = $data['razorpay_order_id'];
        $signature = $data['razorpay_signature'];

        $stmt = $this->db->prepare("
            INSERT INTO orders (
                customer_name,
                customer_phone,
                address,
                city,
                pincode,
                subtotal,
                delivery_fee,
                gst,
                grand_total,
                payment_method,
                payment_status,
                razorpay_order_id,
                razorpay_payment_id,
                razorpay_signature
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $customerName,
            $phone,
            $address,
            $city,
            $pincode,
            $subtotal,
            $deliveryFee,
            $gst,
            $grandTotal,
            'razorpay',
            'paid',
            $orderId,
            $paymentId,
            $signature
        ]);

        $orderDbId = $this->db->lastInsertId();

        foreach ($cartItems as $item) {

            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (
                    order_id,
                    food_item_id,
                    food_name,
                    quantity,
                    price,
                    total
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmtItem->execute([
                $orderDbId,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }

        Response::json([
            "message" => "Order placed successfully"
        ]);
    }
    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $stmtItems = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$order['id']]);
            $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        Response::json($orders);
    }
    public function show($id)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM orders 
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

        $stmt->execute([$id]);

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$orders) {
            Response::json([], 200);
            return;
        }

        foreach ($orders as &$order) {

            $stmtItems = $this->db->prepare("
            SELECT * FROM order_items 
            WHERE order_id = ?
        ");

            $stmtItems->execute([$order['id']]);

            $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        Response::json($orders);
    }
}