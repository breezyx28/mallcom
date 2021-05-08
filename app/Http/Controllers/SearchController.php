<?php

namespace App\Http\Controllers;

use App\Events\SearchKeysEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(SearchRequest $request)
    {
        $validate = (object) $request->validated();

        $result = \App\Models\Product::with('category', 'store', 'product_sizes')->search($validate->search)->limit(50)->get();

        // if there is a result
        if ($result->isEmpty()) {
            return Resp::Error('لا توجد نائج', $result);
        }

        // save the key word to search_keys database
        event(new SearchKeysEvent($validate->search));

        // check if there is sort
        if (isset($validate->sort)) {
            $result = [
                'lowerPrice' => $result->sortBy('price')->values()->all(),
                'higherPrice' => $result->sortByDesc('price')->values()->all(),
                'newFirst' => $result->sortBy('created_at')->values()->all(),
            ][$validate->sort];
        }

        // check if there is filter

        $filterList = [
            'color' => '',
            'countryOfMade' => '',
            'company' => '',
            'weight' => '',
            'expireDate' => '',
            'price' => '',
            'rate' => '',
        ];

        if (isset($validate->filter)) {
            $result = $result->map(function ($item, $key) {
            })->all();
        }

        // return final result
        return Resp::Success('تم', [
            'result' => $result,
            'productsPropertyList' => []
        ]);
    }
}
