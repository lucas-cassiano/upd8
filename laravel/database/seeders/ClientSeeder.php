<?php

namespace Database\Seeders;
use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::create([
            'nome' => 'Lucas Cassiano',
            'cpf' => '51774096196',
            'nascimento' => '1999-01-25',
            'sexo' => 'M',
            'endereco' => 'Rua santa quiteria, n 15',
            'estado' => 'Alagoas',
            'cidade' => 'Maragogi',
        ]);
    }
}
