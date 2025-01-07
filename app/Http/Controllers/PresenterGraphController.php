<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PresenterGraphController extends Controller
{
    /**
     * Proxies access to myaweb so that remote clients will have same access to the data as on-site clients.
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function data(Request $request)
    {
        /*
         * Example valid request that we're proxying.
         * https://myaweb.acc.jlab.org/myStatsSampler/data
         * ?sUnit=second
         * &jsonp=jQuery1102029716386933872196_1679930632273&n=48
         * &s=1800&b=2023-03-26+07:23
         * &l=40MVA:totkW,33MVA:totkW,substn:totkW,substn:actDemkW
         * &_=1679930632274
         */
        // We take steps to ensure that we only pass expected parameters to the back end.
        return Http::get('https://myaweb.acc.jlab.org/myStatsSampler/data', $request->only('sUnit', 'jsonp', 'n', 's', 'b', 'l', '_'));
    }
}
