<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->get();
        $products = Product::with('variations')->get();

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $paymentStatuses = ['pending', 'paid', 'failed'];
        $paymentMethods = ['ssl_commerz', 'bkash', 'nagad', 'manual'];
        $shippingMethods = ['shah_sports_team', 'pathao_courier'];

        // Create 15 sample orders
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $address = $customer->addresses->first();
            
            if (!$address) {
                continue;
            }

            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $status === 'delivered' ? 'paid' : $paymentStatuses[array_rand($paymentStatuses)];
            $orderType = $i < 12 ? 'online' : 'in_store'; // 12 online, 3 POS orders

            $order = Order::create([
                'user_id' => $orderType === 'online' ? $customer->id : null,
                'order_type' => $orderType,
                'shipping_address_id' => $orderType === 'online' ? $address->id : null,
                'billing_address_id' => $orderType === 'online' ? $address->id : null,
                'subtotal' => 0,
                'shipping_cost' => rand(80, 350),
                'discount_amount' => rand(0, 500),
                'tax_amount' => 0,
                'total_amount' => 0,
                'shipping_method' => $shippingMethods[array_rand($shippingMethods)],
                'tracking_number' => $status === 'shipped' || $status === 'delivered' ? 'TRK' . strtoupper(substr(md5(rand()), 0, 10)) : null,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'notes' => $i % 5 === 0 ? 'Please deliver before 6 PM' : null,
                'customer_name' => $orderType === 'in_store' ? $customer->full_name : null,
                'customer_email' => $orderType === 'in_store' ? $customer->email : null,
                'customer_phone' => $orderType === 'in_store' ? $customer->phone : null,
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Add 1-4 items to each order
            $itemCount = rand(1, 4);
            $subtotal = 0;
            $orderProducts = $products->random($itemCount);

            foreach ($orderProducts as $product) {
                $variation = $product->variations->first();
                $quantity = rand(1, 3);
                $unitPrice = $variation ? $variation->price : $product->price;
                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variation_id' => $variation?->id,
                    'product_name' => $product->name,
                    'variation_details' => $variation ? json_encode(['sku' => $variation->sku]) : null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + $order->shipping_cost - $order->discount_amount,
            ]);

            // Create payment record for paid orders
            if ($paymentStatus === 'paid') {
                Payment::create([
                    'user_id' => $customer->id,
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_method' => $orderType === 'in_store' ? 'manual' : $paymentMethods[array_rand(array_slice($paymentMethods, 0, 3))],
                    'transaction_id' => 'TXN' . strtoupper(substr(md5(rand()), 0, 12)),
                    'status' => 'completed',
                    'gateway_response' => json_encode(['status' => 'success']),
                    'paid_at' => $order->created_at->addHours(rand(1, 24)),
                ]);
            }
        }
    }
}
