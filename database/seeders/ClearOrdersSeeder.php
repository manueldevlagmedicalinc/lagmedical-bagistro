<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            'order_transactions',
            'refund_items',
            'refunds',
            'shipment_items',
            'shipments',
            'invoice_items',
            'invoices',
            'order_items',
            'order_addresses',
            'order_payment',
            'order_comments',
            'downloadable_link_purchased',
            'orders',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->command?->info('Todas las ordenes de prueba fueron eliminadas.');
    }
}
