<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Variation;
use App\Models\VariationOption;
use App\Models\VariationValue;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        $shoeSizeVariation = Variation::where('slug', 'shoe-size-eu')->first();
        $batHandleVariation = Variation::where('slug', 'bat-handle')->first();
        $protectiveGearVariation = Variation::where('slug', 'protective-gear-size')->first();
        $sizeVariation = Variation::where('slug', 'size')->first();
        $gripSizeVariation = Variation::where('slug', 'grip-size')->first();

        // Add size variations to football boots
        $footballBoots = Product::where('sku', 'like', 'NK-MS9%')
            ->orWhere('sku', 'like', 'AD-PE1%')
            ->get();

        if ($shoeSizeVariation) {
            $bootSizeOptions = VariationOption::where('variation_id', $shoeSizeVariation->id)
                ->whereIn('value', ['39', '40', '41', '42', '43', '44', '45'])
                ->get();

            foreach ($footballBoots as $boot) {
                foreach ($bootSizeOptions as $index => $option) {
                    $variation = ProductVariation::create([
                        'product_id' => $boot->id,
                        'sku' => $boot->sku . '-' . $option->value,
                        'price' => $boot->price,
                        'quantity' => rand(3, 10),
                        'is_default' => $option->value === '41',
                    ]);

                    VariationValue::create([
                        'product_variation_id' => $variation->id,
                        'variation_option_id' => $option->id,
                    ]);
                }
                $boot->update(['quantity' => 0]);
            }
        }

        // Add size variations to cricket bats (Short Handle, Long Handle)
        $cricketBats = Product::whereHas('category', function ($q) {
            $q->where('slug', 'cricket-bats');
        })->get();

        if ($batHandleVariation) {
            $batHandleOptions = VariationOption::where('variation_id', $batHandleVariation->id)->get();

            foreach ($cricketBats as $bat) {
                foreach ($batHandleOptions as $option) {
                    $variation = ProductVariation::create([
                        'product_id' => $bat->id,
                        'sku' => $bat->sku . '-' . $option->value,
                        'price' => $bat->price,
                        'quantity' => rand(5, 15),
                        'is_default' => $option->value === 'SH',
                    ]);

                    VariationValue::create([
                        'product_variation_id' => $variation->id,
                        'variation_option_id' => $option->id,
                    ]);
                }
                $bat->update(['quantity' => 0]);
            }
        }

        // Add size variations to batting pads and gloves
        $protectiveGear = Product::whereHas('category', function ($q) {
            $q->whereIn('slug', ['batting-pads', 'batting-gloves']);
        })->get();

        if ($protectiveGearVariation) {
            $gearSizeOptions = VariationOption::where('variation_id', $protectiveGearVariation->id)->get();

            foreach ($protectiveGear as $gear) {
                foreach ($gearSizeOptions as $option) {
                    $variation = ProductVariation::create([
                        'product_id' => $gear->id,
                        'sku' => $gear->sku . '-' . $option->value,
                        'price' => $gear->price,
                        'quantity' => rand(5, 12),
                        'is_default' => $option->value === 'MD',
                    ]);

                    VariationValue::create([
                        'product_variation_id' => $variation->id,
                        'variation_option_id' => $option->id,
                    ]);
                }
                $gear->update(['quantity' => 0]);
            }
        }

        // Add size variations to swimwear
        $swimwear = Product::whereHas('category', function ($q) {
            $q->where('slug', 'swimwear');
        })->get();

        if ($sizeVariation) {
            $swimSizeOptions = VariationOption::where('variation_id', $sizeVariation->id)
                ->whereIn('value', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])
                ->get();

            foreach ($swimwear as $item) {
                foreach ($swimSizeOptions as $option) {
                    $variation = ProductVariation::create([
                        'product_id' => $item->id,
                        'sku' => $item->sku . '-' . $option->value,
                        'price' => $item->price,
                        'quantity' => rand(5, 15),
                        'is_default' => $option->value === 'M',
                    ]);

                    VariationValue::create([
                        'product_variation_id' => $variation->id,
                        'variation_option_id' => $option->id,
                    ]);
                }
                $item->update(['quantity' => 0]);
            }
        }

        // Add grip size variations to tennis rackets
        $tennisRackets = Product::whereHas('category', function ($q) {
            $q->where('slug', 'tennis-rackets');
        })->get();

        if ($gripSizeVariation) {
            $gripOptions = VariationOption::where('variation_id', $gripSizeVariation->id)
                ->whereIn('value', ['G2', 'G3', 'G4'])
                ->get();

            foreach ($tennisRackets as $racket) {
                foreach ($gripOptions as $option) {
                    $variation = ProductVariation::create([
                        'product_id' => $racket->id,
                        'sku' => $racket->sku . '-' . $option->value,
                        'price' => $racket->price,
                        'quantity' => rand(3, 8),
                        'is_default' => $option->value === 'G3',
                    ]);

                    VariationValue::create([
                        'product_variation_id' => $variation->id,
                        'variation_option_id' => $option->id,
                    ]);
                }
                $racket->update(['quantity' => 0]);
            }
        }
    }
}
