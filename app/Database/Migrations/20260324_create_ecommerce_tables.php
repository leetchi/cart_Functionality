<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEcommerceTables extends Migration
{
    public function up()
    {
        // products
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'base_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false, 'default' => 0.00],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'DATETIME', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug');
        $this->forge->createTable('products', true);

        // product variants (size/color/other attributes)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'variant_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'size' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'color' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false, 'default' => 0.00],
            'stock' => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'DATETIME', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_variants', true);

        // cart items
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'product_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'variant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'quantity' => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 1],
            'price_each' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false, 'default' => 0.00],
            'added_at' => ['type' => 'DATETIME', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('cart_items', true);

        // Seed sample products + variants
        $this->db->table('products')->insertBatch([
            ['name' => 'Classic T-Shirt', 'slug' => 'classic-t-shirt', 'description' => 'Comfort cotton tee', 'base_price' => 20.00],
            ['name' => 'Sneaker Shoes', 'slug' => 'sneaker-shoes', 'description' => 'Sporty everyday sneaker', 'base_price' => 55.00],
        ]);

        $this->db->table('product_variants')->insertBatch([
            ['product_id' => 1, 'sku' => 'TSHIRT-S-RED', 'variant_name' => 'S / Red', 'size' => 'S', 'color' => 'Red', 'price' => 20.00, 'stock' => 20, 'is_active' => 1],
            ['product_id' => 1, 'sku' => 'TSHIRT-M-BLU', 'variant_name' => 'M / Blue', 'size' => 'M', 'color' => 'Blue', 'price' => 22.00, 'stock' => 15, 'is_active' => 1],
            ['product_id' => 2, 'sku' => 'SNEAKER-8-BLK', 'variant_name' => '8 / Black', 'size' => '8', 'color' => 'Black', 'price' => 55.00, 'stock' => 12, 'is_active' => 1],
            ['product_id' => 2, 'sku' => 'SNEAKER-9-WHT', 'variant_name' => '9 / White', 'size' => '9', 'color' => 'White', 'price' => 58.00, 'stock' => 10, 'is_active' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('cart_items', true);
        $this->forge->dropTable('product_variants', true);
        $this->forge->dropTable('products', true);
    }
}
