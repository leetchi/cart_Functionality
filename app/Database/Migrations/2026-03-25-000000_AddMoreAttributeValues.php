<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMoreAttributeValues extends Migration
{
    public function up()
    {
        $attributeTable = $this->db->table('attributes');
        $valueTable = $this->db->table('attribute_values');

        $size = $attributeTable->where('slug', 'size')->get()->getRowArray();
        $color = $attributeTable->where('slug', 'color')->get()->getRowArray();

        if (!$size || !$color) {
            return;
        }

        $sizeId = (int) $size['id'];
        $colorId = (int) $color['id'];

        $sizeValues = ['XL', 'XXL'];
        $colorValues = ['Green', 'Beige', 'Navy'];

        $existingSize = $valueTable->where('attribute_id', $sizeId)->get()->getResultArray();
        $existingColor = $valueTable->where('attribute_id', $colorId)->get()->getResultArray();

        $existingSizeMap = array_column($existingSize, 'value', 'value');
        $existingColorMap = array_column($existingColor, 'value', 'value');

        $rows = [];
        foreach ($sizeValues as $value) {
            if (!isset($existingSizeMap[$value])) {
                $rows[] = ['attribute_id' => $sizeId, 'value' => $value];
            }
        }
        foreach ($colorValues as $value) {
            if (!isset($existingColorMap[$value])) {
                $rows[] = ['attribute_id' => $colorId, 'value' => $value];
            }
        }

        if (!empty($rows)) {
            $valueTable->insertBatch($rows);
        }
    }

    public function down()
    {
        $attributeTable = $this->db->table('attributes');
        $valueTable = $this->db->table('attribute_values');

        $size = $attributeTable->where('slug', 'size')->get()->getRowArray();
        $color = $attributeTable->where('slug', 'color')->get()->getRowArray();

        if (!$size || !$color) {
            return;
        }

        $sizeId = (int) $size['id'];
        $colorId = (int) $color['id'];

        $sizeValues = ['XL', 'XXL'];
        $colorValues = ['Green', 'Beige', 'Navy'];

        $valueTable->where('attribute_id', $sizeId)->whereIn('value', $sizeValues)->delete();
        $valueTable->where('attribute_id', $colorId)->whereIn('value', $colorValues)->delete();
    }
}
