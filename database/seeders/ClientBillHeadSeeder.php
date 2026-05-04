<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientBillHeadSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $heads = [
            'Consultation Fee',
            'Case Review',
            'Documentation Charges',
            'Filing Charges',
            'Legal Drafting',
            'Professional Services',
            'Project Coordination',
            'Travel Reimbursement',
            'Hearing Attendance',
            'Compliance Support',
            'Administrative Charges',
            'Urgent Processing Fee',
        ];

        foreach ($heads as $title) {
            $existing = DB::table('client_bill_heads')->where('title', $title)->first();

            if ($existing) {
                DB::table('client_bill_heads')
                    ->where('id', $existing->id)
                    ->update([
                        'status' => 'active',
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('client_bill_heads')->insert([
                'title' => $title,
                'status' => 'active',
                'created_by' => null,
                'created_by_role' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
