<?php

namespace Database\Seeders;


use App\Models\LayoutSetup;
use Illuminate\Database\Seeder;


class LayoutTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LayoutSetup::create([
            'title' => 'Layout s menu vlevo',
            'description' => 'Toto je základní layout pro eshopy. Uzký header s menu vpravo a logem vlevo. Pod headerem menší banner. Vlevo sloupec na kategorie produktů. Pak už jenom obsah a uzký footer.',
            'sketch_url' => 'menu-left.png',
            'img_url' => '',
        ]);
    }
}
