<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttributeTables extends Migration
{
    public function up()
    {
        // attributes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('attributes', true);

        // attribute values
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'attribute_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'value' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('attribute_id');
        $this->forge->addForeignKey('attribute_id', 'attributes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attribute_values', true);

        // variant attribute assignment
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'variant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'attribute_value_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['variant_id', 'attribute_value_id']);
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('attribute_value_id', 'attribute_values', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_variant_attribute_values', true);

        // Seed attributes + values
        $this->db->table('attributes')->insertBatch([
            ['name' => 'Size', 'slug' => 'size'],
            ['name' => 'Color', 'slug' => 'color'],
        ]);

        $sizeId = 1;
        $colorId = 2;

        $this->db->table('attribute_values')->insertBatch([
            ['attribute_id' => $sizeId, 'value' => 'S'],
            ['attribute_id' => $sizeId, 'value' => 'M'],
            ['attribute_id' => $sizeId, 'value' => 'L'],
            ['attribute_id' => $colorId, 'value' => 'Red'],
            ['attribute_id' => $colorId, 'value' => 'Blue'],
            ['attribute_id' => $colorId, 'value' => 'Black'],
            ['attribute_id' => $colorId, 'value' => 'White'],
        ]);

        // Map existing variants to attribute values if possible.
        $variantMap = [
            1 => ['size' => 'S', 'color' => 'Red'],
            2 => ['size' => 'M', 'color' => 'Blue'],
            3 => ['size' => '8', 'color' => 'Black'],
            4 => ['size' => '9', 'color' => 'White'],
        ];

        $values = $this->db->table('attribute_values')->get()->getResultArray();
        $mapValue = [];
        foreach ($values as $v) {
            $mapValue[$v['attribute_id']][$v['value']] = $v['id'];
        }

        $rows = [];
        foreach ($variantMap as $variantId => $parts) {
            if (isset($mapValue[$sizeId][$parts['size']])) {
                $rows[] = ['variant_id' => $variantId, 'attribute_value_id' => $mapValue[$sizeId][$parts['size']]];
            }
            if (isset($mapValue[$colorId][$parts['color']])) {
                $rows[] = ['variant_id' => $variantId, 'attribute_value_id' => $mapValue[$colorId][$parts['color']]];
            }
        }

        if (!empty($rows)) {
            $this->db->table('product_variant_attribute_values')->insertBatch($rows);
        }
    }

    public function down()
    {
        $this->forge->dropTable('product_variant_attribute_values', true);
        $this->forge->dropTable('attribute_values', true);
        $this->forge->dropTable('attributes', true);
    }
}
