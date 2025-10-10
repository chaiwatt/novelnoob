<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TwelvedataApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('twelvedata_apis')->insert([
            [
                'api' => 'cedad3bb15fb4183b92d8c939e2bdda7',    
            ],
            [
                'api' => 'da989f98cd374576803ef14c63e4624e',    
            ],
            [
                'api' => '7f55cb292eec441db420c1e1886a55cd',    
            ],
            [
                'api' => '2573fe64f2f5495f9cdba27b60e261c0',    
            ],
            [
                'api' => 'ad4e6b76adfa4efea9c041cd775d5ae2',    
            ],
            [
                'api' => '13ffe7c83fdc49a0b51addcc069e700d',    
            ],
            [
                'api' => 'b8cc1db317f648f482216ce3b03ac470',    
            ],
            [
                'api' => '034e48fd4d41479fba76d963f2e303a9',    
            ],
            [
                'api' => 'e4af52dba97f4a22ac334fa72208ac5b',    
            ],
            [
                'api' => '77d7e5f62a6c4eee8b0aa9e51eec6ff5',    
            ],
            [
                'api' => '2917d72fa74d4b2b876fcbfd76177bd0',    
            ],
            [
                'api' => 'aa19cd34d0024c6e82dfb5d7980d2fc2',    
            ],
            [
                'api' => '7d69560796644a19acad80004003fe92',    
            ],
            [
                'api' => '0fd0c0f3ee964d69af51c7c948122889',    
            ],
            [
                'api' => '6bb1057e643f4fe5a5456e242853d9a9',    
            ]
        ]);

    
    }
}
