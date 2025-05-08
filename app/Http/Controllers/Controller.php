<?php

namespace App\Http\Controllers;



use App\Console\Commands\TianYanCha;
use App\Console\Commands\TianYanCha3;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Libraries\Response;
use Illuminate\Support\Facades\DB;


class Controller
{



    public function index(Request $request){

        $res = (new TianYanCha())->search('深圳市龙岗区实惠佳便利店');
        return $res;
    }



}
