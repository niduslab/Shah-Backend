<?php

namespace Database\Seeders;

use App\Models\StorePolicy;
use Illuminate\Database\Seeder;

class StorePolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'policy_type' => 'return_policy',
                'title' => 'Return & Refund Policy',
                'slug' => 'return-policy',
                'content' => '
# Return & Refund Policy

## Overview
At Shah Sports, we want you to be completely satisfied with your purchase. If you are not satisfied, you may return most items within 7 days of delivery for a full refund or exchange.

## Eligibility for Returns
- Items must be unused and in original packaging
- Items must be returned within 7 days of delivery
- Original receipt or proof of purchase is required
- Items must have all tags attached

## Non-Returnable Items
- Used or worn items
- Items without original packaging
- Customized or personalized items
- Swimwear (for hygiene reasons)
- Items marked as "Final Sale"

## Return Process
1. Contact our customer service at returns@shahsports.com
2. Receive a Return Authorization Number (RAN)
3. Pack the item securely with all original packaging
4. Ship the item to our return address
5. Refund will be processed within 5-7 business days after receiving the item

## Refund Methods
- Original payment method (credit/debit card, bKash, Nagad)
- Store credit (optional)

## Damaged or Defective Items
If you receive a damaged or defective item, please contact us within 48 hours of delivery. We will arrange for a replacement or full refund at no additional cost.

## Contact Us
Email: returns@shahsports.com
Phone: +880 1700-000001
                ',
                'is_active' => true,
            ],
            [
                'policy_type' => 'shipping_policy',
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'content' => '
# Shipping Policy

## Delivery Areas
We currently deliver to all districts in Bangladesh.

## Shipping Methods

### Shah Sports Team Delivery
- Available in Dhaka, Chittagong, Sylhet, and Rajshahi
- Delivery time: 2-3 business days
- Free shipping on orders above ৳5,000

### Pathao Courier
- Available nationwide
- Delivery time: 3-5 business days
- Free shipping on orders above ৳8,000

## Shipping Costs
- Standard items: ৳80 - ৳100
- Heavy items (over 5kg): ৳200 - ৳250
- Oversized items: ৳350

## Order Processing
- Orders placed before 2 PM are processed the same day
- Orders placed after 2 PM are processed the next business day
- You will receive a tracking number via SMS and email

## Tracking Your Order
Track your order using the tracking number provided via:
- Our website order tracking page
- Pathao app (for Pathao deliveries)
- Contact customer service

## Delivery Issues
If you experience any delivery issues, please contact us within 24 hours:
- Email: shipping@shahsports.com
- Phone: +880 1700-000001
                ',
                'is_active' => true,
            ],
            [
                'policy_type' => 'privacy_policy',
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '
# Privacy Policy

## Introduction
Shah Sports ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information.

## Information We Collect
- Personal information (name, email, phone number, address)
- Payment information (processed securely through payment gateways)
- Order history and preferences
- Device and browser information
- Cookies and usage data

## How We Use Your Information
- Process and fulfill orders
- Send order confirmations and updates
- Provide customer support
- Send promotional emails (with your consent)
- Improve our website and services
- Prevent fraud and ensure security

## Information Sharing
We do not sell your personal information. We may share information with:
- Payment processors (SSL Commerz, bKash, Nagad)
- Shipping partners (Pathao)
- Service providers who assist our operations

## Data Security
We implement industry-standard security measures to protect your data:
- SSL encryption for all transactions
- Secure payment processing
- Regular security audits

## Your Rights
You have the right to:
- Access your personal data
- Correct inaccurate data
- Delete your account
- Opt-out of marketing communications

## Contact Us
For privacy-related inquiries:
Email: privacy@shahsports.com
Phone: +880 1700-000001
                ',
                'is_active' => true,
            ],
            [
                'policy_type' => 'terms_conditions',
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => '
# Terms & Conditions

## Agreement to Terms
By accessing and using Shah Sports website, you agree to be bound by these Terms and Conditions.

## Use of Website
- You must be at least 18 years old to make purchases
- You are responsible for maintaining the confidentiality of your account
- You agree to provide accurate and complete information
- You may not use the website for any illegal purposes

## Products and Pricing
- All prices are in Bangladeshi Taka (BDT)
- Prices are subject to change without notice
- We reserve the right to limit quantities
- Product images are for illustration purposes

## Orders and Payment
- Orders are subject to acceptance and availability
- We accept bKash, Nagad, SSL Commerz, and cash on delivery
- Payment must be received before shipping (except COD)

## Intellectual Property
- All content on this website is owned by Shah Sports
- You may not reproduce, distribute, or modify any content without permission

## Limitation of Liability
Shah Sports shall not be liable for any indirect, incidental, or consequential damages arising from the use of our website or products.

## Governing Law
These terms are governed by the laws of Bangladesh.

## Changes to Terms
We reserve the right to modify these terms at any time. Continued use of the website constitutes acceptance of modified terms.

## Contact
Email: legal@shahsports.com
Phone: +880 1700-000001
                ',
                'is_active' => true,
            ],
        ];

        foreach ($policies as $policy) {
            StorePolicy::create($policy);
        }
    }
}
