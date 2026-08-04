<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChemicalProduct extends Model
{
    protected $table = 'chemical_products';

    protected $fillable = [
        'name', 'sku', 'category', 'brand', 'barcode',
        'description', 'invoice_description',
        'stock_on_hand', 'stock_unit_id',
        'formula_code', 'ph_level', 'viscosity_id', 'active_ingredient_id',
        'fragrance_id', 'colour_id', 'concentration', 'dilution_ratio',
        'bag_type_id', 'container_size_id', 'material_type_id',
        'cap_type_id', 'label_type_id', 'units_per_carton', 'carton_weight_kg',
        'batch_size_litres', 'units_per_batch', 'mixing_time_minutes',
        'filling_speed_per_hour', 'yield_percentage', 'shelf_life_months',
        'weight_source', 'weight_per_unit_grams',
        'raw_material_cost', 'packaging_cost', 'labour_cost_per_unit',
        'overhead_cost', 'markup_percentage', 'price',
        'vat_applicable', 'vat_rate',
        'show_weight_on_label', 'show_date_on_label',
        'show_expiry_date_on_label', 'show_barcode_on_label',
        'is_active', 'created_by', 'updated_by',
    ];
}