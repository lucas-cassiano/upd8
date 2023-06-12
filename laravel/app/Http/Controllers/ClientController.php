<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{

    public function cadastro(Request $request): JsonResponse{

        try{

            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|unique:client|max:11',
                'nascimento' => 'required|max:10',
                'sexo' => 'required|string|max:1',
                'endereco' => 'required|string|max:255',
                'estado' => 'required|string|max:2',
                'cidade' => 'required|string|max:255'
            ]);

            if($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }else if($this->validarCPF($request->input('cpf'))==false){
                return response()->json(['cpf' => [
                    'invalido'
                ]], 400);
            }else if($this->validarData($request->input('nascimento'))==false){
                return response()->json(['nasicmento' => [
                    'invalido'
                ]], 400);
            }else if($request->input('sexo') !== 'M' && $request->input('sexo') !== 'F'){
                return response()->json(['sexo' => [
                    'invalido'
                ]], 400);
            }

            $client = new Client();
            $client->nome = $request->input('nome');
            $client->cpf = $request->input('cpf');
            $client->nascimento = $this->converteDate($request->input('nascimento'));
            $client->sexo = $request->input('sexo');
            $client->endereco = $request->input('endereco');
            $client->estado = $request->input('estado');
            $client->cidade = $request->input('cidade');
            $client->save();

            return response()->json([
                'success' => true,
                'status' => 'Deu certo'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'success' => false
            ], 401);
        }

    }

    private function converteDate(string $data){

        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $matches)) {
            return '';
        }

        $dia = intval($matches[1]);
        $mes = intval($matches[2]);
        $ano = intval($matches[3]);

        return $ano.'-'.$mes.'-'.$dia;

    }

    public function filtro(Request $request): JsonResponse{

        try{

            $validator = Validator::make($request->all(), [
                'nome' => 'string|max:255',
                'cpf' => 'string|unique:client|max:11',
                'nascimento' => 'max:10',
                'sexo' => 'string|max:1',
                'endereco' => 'string|max:255',
                'estado' => 'string|max:2',
                'cidade' => 'string|max:255'
            ]);

            if($validator->fails()) {
             //   return response()->json($validator->errors(), 400);
            }else if($request->input('cpf')  && $this->validarCPF($request->input('cpf'))==false){
                return response()->json(['cpf' => [
                    'invalido'
                ]], 400);
            }else if($request->input('nascimento') && $this->validarData($request->input('nascimento'))==false){
                return response()->json(['nasicmento' => [
                    'invalido'
                ]], 400);
            }else if($request->input('sexo') && $request->input('sexo') !== 'M' && $request->input('sexo') !== 'F'){
                return response()->json(['sexo' => [
                    'invalido'
                ]], 400);
            }

            $query = Client::query();

            if($request->input('nome')){
                $nome = $request->input('nome');
                $query->orWhere('nome', 'LIKE', "%{$nome}%");
            }

            if($request->input('cpf')){
                $query->orWhere('cpf', '=', $request->input('cpf'));
            }

            if($request->input('nascimento')){
                $query->orWhere('nascimento', '=', $this->converteDate($request->input('nascimento')));
            }

            if($request->input('sexo')){
                $query->orWhere('sexo', '=', $request->input('sexo'));
            }

            if($request->input('estado')){
                $query->orWhere('estado', '=', $request->input('estado'));
            }

            if($request->input('cidade')){
                $query->orWhere('cidade', '=', $request->input('cidade'));
            }

            $clients = $query->get();

            $getInfo = [];
            foreach($clients as $client){
                $getInfo[] = [
                    'nome' => $client['nome'],
                    'cpf' => $client['nome'],
                    'nascimento' => $client['nascimento'],
                    'sexo' => $client['sexo'],
                    'estado' => $client['estado'],
                    'cidade' => $client['cidade'],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $getInfo
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'data' => []
            ], 401);
        }

    }

    private function validarData($data) {

        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $matches)) {
            return false;
        }

        $dia = intval($matches[1]);
        $mes = intval($matches[2]);
        $ano = intval($matches[3]);

        if ($dia < 1 || $dia > 31 || $mes < 1 || $mes > 12 || $ano < 1000 || $ano > 9999) {
            return false;
        }

        if (($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && $dia > 30) {
            return false;
        }

        if ($mes == 2) {
            if (($ano % 4 == 0 && $ano % 100 != 0) || $ano % 400 == 0) {
                if ($dia > 29) {
                    return false;
                }
            } else {
                if ($dia > 28) {
                    return false;
                }
            }
        }

        return true;
    }



    private function validarCPF($cpf) {

        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        for ($i = 9; $i < 11; $i++) {
            $soma = 0;
            for ($j = 0; $j < $i; $j++) {
                $soma += $cpf[$j] * (($i + 1) - $j);
            }
            $resto = $soma % 11;
            if ($resto < 2) {
                $digito = 0;
            } else {
                $digito = 11 - $resto;
            }
            if ($cpf[$i] != $digito) {
                return false;
            }
        }

        return true;
    }


}
