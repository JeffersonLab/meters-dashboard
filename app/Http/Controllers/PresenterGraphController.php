<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PresenterGraphController extends Controller
{
    public function data(Request $request){

        //http://localhost/presenter-graph/data?sUnit=second&jsonp=jQuery1102029716386933872196_1679930632273&n=48&s=1800&b=2023-03-26+07:23&l=40MVA:totkW,33MVA:totkW,substn:totkW,substn:actDemkW&_=1679930632274
        //https://myaweb.acc.jlab.org/myStatsSampler/data?sUnit=second&jsonp=jQuery1102029716386933872196_1679930632273&n=48&s=1800&b=2023-03-26+07:23&l=40MVA:totkW,33MVA:totkW,substn:totkW,substn:actDemkW&_=1679930632274
        $response = Http::get('https://myaweb.acc.jlab.org/myStatsSampler/data', $request->only('sUnit','jsonp','n','s','b','l','_'));
        return $response;
    }
}
