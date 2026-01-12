<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // UsersTableSeeder::class,
            // CategoriesTableSeeder::class,
            // BrandsTableSeeder::class,
            // ProductModelsTableSeeder::class,
            VariationsTableSeeder::class,
            VariationOptionsTableSeeder::class,
            // VariationOptionDescriptionsTableSeeder::class,
            // ImagesTableSeeder::class,
            // AddressesTableSeeder::class,
            // UserProfilesTableSeeder::class,
            // VendorShopsTableSeeder::class,
            // ProductsTableSeeder::class,
            // ProductImagesSeeder::class,
            // ProductSeoConfigsTableSeeder::class,
            // ProductVariationSeeder::class,
            // NotificationsTableSeeder::class,
            // LoyaltyPointsTableSeeder::class,
            // MembershipsTableSeeder::class,
            // ShippingZonesTableSeeder::class,
            // ShippingCountryAreasTableSeeder::class,
            // ShippingCountriesTableSeeder::class,
            // ShippingRatesTableSeeder::class,
            // ReviewsTableSeeder::class,
            // VendorWalletsTableSeeder::class,
            // VendorPayoutsTableSeeder::class,
            // PaymentsTableSeeder::class,
            // MessagesTableSeeder::class,
            // PromotionsTableSeeder::class,
            // PromotionProductsTableSeeder::class,
            // LiveChatsTableSeeder::class,
            // ConversationsTableSeeder::class,
            // VendorShopSeoConfigsTableSeeder::class,
            // OrdersTableSeeder::class,
            // OrderItemsTableSeeder::class,
            // VendorTransactionsTableSeeder::class,
            // DownloadableProductSeeder::class,
        ]);
    }
}
