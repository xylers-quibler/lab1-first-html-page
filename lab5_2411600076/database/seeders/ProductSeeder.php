<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name'=>'Spring Rolls','sku'=>'APP-001','category'=>'Appetizer','quantity'=>0,'reorder_level'=>5,'unit_price'=>120.00,'supplier'=>'Local Market'],
            ['name'=>'Calamari','sku'=>'APP-002','category'=>'Appetizer','quantity'=>3,'reorder_level'=>10,'unit_price'=>180.00,'supplier'=>'Seafood Supplier'],
            ['name'=>'Garlic Bread','sku'=>'APP-003','category'=>'Appetizer','quantity'=>27,'reorder_level'=>10,'unit_price'=>90.00,'supplier'=>'Bakery'],
            ['name'=>'Grilled Salmon','sku'=>'MAIN-001','category'=>'Main Course','quantity'=>0,'reorder_level'=>5,'unit_price'=>450.00,'supplier'=>'Seafood Supplier'],
            ['name'=>'Beef Steak','sku'=>'MAIN-002','category'=>'Main Course','quantity'=>26,'reorder_level'=>10,'unit_price'=>520.00,'supplier'=>'Meat Supplier'],
            ['name'=>'Chicken Alfredo','sku'=>'MAIN-003','category'=>'Main Course','quantity'=>32,'reorder_level'=>10,'unit_price'=>280.00,'supplier'=>'Poultry Supplier'],
            ['name'=>'Margherita Pizza','sku'=>'MAIN-004','category'=>'Main Course','quantity'=>49,'reorder_level'=>15,'unit_price'=>320.00,'supplier'=>'Local Market'],
            ['name'=>'Chocolate Lava Cake','sku'=>'DES-001','category'=>'Dessert','quantity'=>31,'reorder_level'=>10,'unit_price'=>150.00,'supplier'=>'Bakery'],
            ['name'=>'Cheesecake','sku'=>'DES-002','category'=>'Dessert','quantity'=>33,'reorder_level'=>10,'unit_price'=>160.00,'supplier'=>'Bakery'],
            ['name'=>'Ice Cream Sundae','sku'=>'DES-003','category'=>'Dessert','quantity'=>26,'reorder_level'=>10,'unit_price'=>110.00,'supplier'=>'Dairy Supplier'],
            ['name'=>'Iced Tea','sku'=>'BEV-001','category'=>'Beverage','quantity'=>50,'reorder_level'=>20,'unit_price'=>60.00,'supplier'=>'Beverage Supplier'],
            ['name'=>'Fresh Lemonade','sku'=>'BEV-002','category'=>'Beverage','quantity'=>17,'reorder_level'=>20,'unit_price'=>70.00,'supplier'=>'Local Market'],
            ['name'=>'Craft Soda','sku'=>'BEV-003','category'=>'Beverage','quantity'=>8,'reorder_level'=>15,'unit_price'=>85.00,'supplier'=>'Beverage Supplier'],
            ['name'=>'House Wine','sku'=>'BEV-004','category'=>'Beverage','quantity'=>13,'reorder_level'=>5,'unit_price'=>220.00,'supplier'=>'Wine Distributor'],
            ['name'=>'Mango Shake','sku'=>'BEV-005','category'=>'Beverage','quantity'=>40,'reorder_level'=>10,'unit_price'=>95.00,'supplier'=>'Local Market'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}