<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '
# About Shah Sports

## Our Story
Shah Sports was founded in 2010 with a simple mission: to provide quality sports equipment to athletes across Bangladesh. What started as a small shop in Dhaka has grown into one of the country\'s leading sports retailers.

## Our Mission
To inspire and equip athletes of all levels with premium sports gear, helping them achieve their best performance.

## Our Vision
To be Bangladesh\'s most trusted destination for sports equipment, known for quality products, competitive prices, and exceptional customer service.

## What We Offer
- **Premium Brands**: We partner with world-renowned brands like Nike, Adidas, Yonex, Gray-Nicolls, and more.
- **Wide Selection**: From cricket to football, badminton to swimming, we have equipment for every sport.
- **Expert Advice**: Our team of sports enthusiasts is always ready to help you find the right gear.
- **Quality Guarantee**: Every product we sell meets our strict quality standards.

## Our Values
- **Quality**: We never compromise on the quality of our products
- **Integrity**: We believe in honest and transparent business practices
- **Customer First**: Your satisfaction is our top priority
- **Passion for Sports**: We love sports as much as you do

## Visit Us
**Shah Sports Flagship Store**
123 Sports Avenue, Gulshan
Dhaka 1212, Bangladesh

**Opening Hours**
Saturday - Thursday: 10 AM - 9 PM
Friday: 3 PM - 9 PM

## Contact Us
Phone: +880 1700-000001
Email: info@shahsports.com
                ',
                'meta_title' => 'About Us | Shah Sports',
                'meta_description' => 'Learn about Shah Sports - Bangladesh\'s leading sports equipment retailer since 2010.',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '
# Contact Us

We\'d love to hear from you! Whether you have a question about our products, need help with an order, or just want to say hello, we\'re here to help.

## Get in Touch

### Customer Service
- **Phone**: +880 1700-000001
- **Email**: support@shahsports.com
- **Hours**: Saturday - Thursday, 9 AM - 6 PM

### Sales Inquiries
- **Phone**: +880 1700-000002
- **Email**: sales@shahsports.com

### Corporate & Bulk Orders
- **Email**: corporate@shahsports.com

## Visit Our Store

**Shah Sports Flagship Store**
123 Sports Avenue, Gulshan
Dhaka 1212, Bangladesh

**Opening Hours**
- Saturday - Thursday: 10 AM - 9 PM
- Friday: 3 PM - 9 PM

## Follow Us
- Facebook: /shahsportsbd
- Instagram: @shahsportsbd
- YouTube: Shah Sports Bangladesh

## Feedback
Your feedback helps us improve. Please share your thoughts at feedback@shahsports.com
                ',
                'meta_title' => 'Contact Us | Shah Sports',
                'meta_description' => 'Get in touch with Shah Sports. Contact our customer service team for any questions or assistance.',
                'is_active' => true,
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '
# Frequently Asked Questions

## Orders & Shipping

### How long does delivery take?
- Dhaka: 2-3 business days
- Other cities: 3-5 business days

### Do you offer free shipping?
Yes! Free shipping on orders above ৳5,000 within Dhaka and ৳8,000 for other areas.

### Can I track my order?
Yes, you\'ll receive a tracking number via SMS and email once your order ships.

### Do you deliver outside Bangladesh?
Currently, we only deliver within Bangladesh.

## Returns & Refunds

### What is your return policy?
You can return most items within 7 days of delivery. Items must be unused and in original packaging.

### How do I return an item?
Contact our customer service to get a Return Authorization Number, then ship the item back to us.

### When will I receive my refund?
Refunds are processed within 5-7 business days after we receive the returned item.

## Products

### Are all products genuine?
Yes, we only sell 100% authentic products from authorized distributors.

### Do you offer warranty?
Warranty varies by product and brand. Check the product page for specific warranty information.

### Can I get product recommendations?
Absolutely! Contact our customer service team for personalized recommendations.

## Payment

### What payment methods do you accept?
- bKash
- Nagad
- Credit/Debit Cards (via SSL Commerz)
- Cash on Delivery

### Is online payment secure?
Yes, all online payments are processed through SSL-encrypted payment gateways.

## Account

### How do I create an account?
Click "Sign Up" and fill in your details. You can also checkout as a guest.

### I forgot my password. What do I do?
Click "Forgot Password" on the login page and follow the instructions.

## Still have questions?
Contact us at support@shahsports.com or call +880 1700-000001
                ',
                'meta_title' => 'FAQ | Shah Sports',
                'meta_description' => 'Find answers to frequently asked questions about orders, shipping, returns, and more at Shah Sports.',
                'is_active' => true,
            ],
            [
                'title' => 'Size Guide',
                'slug' => 'size-guide',
                'content' => '
# Size Guide

## Cricket Equipment

### Cricket Bats
| Size | Player Height | Age Group |
|------|---------------|-----------|
| Size 1 | Up to 4\'3" | 4-5 years |
| Size 2 | 4\'3" - 4\'6" | 6-7 years |
| Size 3 | 4\'6" - 4\'9" | 8-9 years |
| Size 4 | 4\'9" - 4\'11" | 9-10 years |
| Size 5 | 4\'11" - 5\'2" | 10-11 years |
| Size 6 | 5\'2" - 5\'6" | 11-13 years |
| Harrow | 5\'6" - 5\'9" | 13-15 years |
| Short Handle | 5\'9"+ | Adult |
| Long Handle | 6\'2"+ | Tall Adult |

### Batting Pads & Gloves
| Size | Age/Height |
|------|------------|
| Youth | Under 14 years |
| Men\'s Small | 5\'4" - 5\'7" |
| Men\'s Medium | 5\'7" - 5\'10" |
| Men\'s Large | 5\'10"+ |

## Football Boots
| EU Size | UK Size | US Size | Foot Length (cm) |
|---------|---------|---------|------------------|
| 39 | 6 | 6.5 | 24.5 |
| 40 | 6.5 | 7 | 25 |
| 41 | 7.5 | 8 | 25.5 |
| 42 | 8 | 8.5 | 26.5 |
| 43 | 9 | 9.5 | 27 |
| 44 | 9.5 | 10 | 28 |
| 45 | 10.5 | 11 | 28.5 |

## Swimwear
| Size | Chest (inches) | Waist (inches) |
|------|----------------|----------------|
| XS | 32-34 | 26-28 |
| S | 34-36 | 28-30 |
| M | 36-38 | 30-32 |
| L | 38-40 | 32-34 |
| XL | 40-42 | 34-36 |
| XXL | 42-44 | 36-38 |

## Tennis Rackets - Grip Size
| Grip Size | Circumference | Recommended For |
|-----------|---------------|-----------------|
| G1 | 4 1/8" | Small hands |
| G2 | 4 1/4" | Small-Medium hands |
| G3 | 4 3/8" | Medium hands (most common) |
| G4 | 4 1/2" | Large hands |
| G5 | 4 5/8" | Extra large hands |

## Need Help?
If you\'re unsure about sizing, contact our team at support@shahsports.com
                ',
                'meta_title' => 'Size Guide | Shah Sports',
                'meta_description' => 'Find the perfect fit with our comprehensive size guide for cricket equipment, football boots, swimwear, and more.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::create($page);
        }
    }
}
