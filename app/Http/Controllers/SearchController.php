<?php

namespace App\Http\Controllers;

use App\Events\SearchKeysEvent;
use App\Helper\ResponseMessage as Resp;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(SearchRequest $request)
    {

        $validate = (object) $request->validated();

        $result = \App\Models\Product::with('category', 'store', 'product_sizes', 'additional_description')->search($validate->search)->limit(50)->get();

        // if there is a result
        if ($result->isEmpty()) {
            return Resp::Error('لا توجد نائج', $result);
        }

        // save the key word to search_keys database
        event(new SearchKeysEvent($validate->search, $result[0]->product_id));

        // check if there is sort
        if (isset($validate->sort)) {
            $result = [
                'lowerPrice' => collect($result->sortBy('price')->values()->all()),
                'higherPrice' => collect($result->sortByDesc('price')->values()->all()),
                'newFirst' => collect($result->sortBy('created_at')->values()->all()),
            ][$validate->sort];
        }

        // list of filter properties
        $productsPropertyList = [
            'color' => $result->whereNotNull('additional_description.color')->values()->pluck('additional_description.color'),
            'countryOfMade' => $result->whereNotNull('additional_description.countryOfMade')->values()->pluck('additional_description.countryOfMade'),
            'company' => $result->whereNotNull('additional_description.company')->values()->pluck('additional_description.company'),
            'weight' => $result->whereNotNull('additional_description.weight')->values()->pluck('additional_description.weight'),
            'expireDate' => $result->whereNotNull('additional_description.expireDate')->values()->pluck('additional_description.expireDate'),
            'price' => ['from' => $result->min('price'), "to" => $result->max('price')],
            'rate' => collect($result->whereNotNull('rate.*.rate')->pluck('rate.*.rate'))->filter(function ($value, $key) {
                return !empty($value);
            })->collapse(),
        ];

        // check if there is filter
        if (isset($validate->filter)) {

            foreach ($result as $key => $value) {

                if (isset($validate->filter['color'])) {

                    $result = collect($result->filter(function ($value, $key) use ($validate) {
                        if ($value->additional_description) {
                            return Str::contains($value->additional_description->color, $validate->filter['color']);
                        }
                    })->all());
                }

                if (isset($validate->filter['countryOfMade'])) {
                    $result = collect($result->where('additional_description.countryOfMade', $validate->filter['countryOfMade'])->all());
                }

                if (isset($validate->filter['company'])) {
                    $result = collect($result->where('additional_description.company', $validate->filter['company'])->all());
                }

                if (isset($validate->filter['weight'])) {
                    $result = collect($result->where('additional_description.weight', $validate->filter['weight'])->all());
                }

                if (isset($validate->filter['expireDate'])) {
                    $result = collect($result->where('additional_description.expireDate', $validate->filter['expireDate'])->all());
                }

                if (isset($validate->filter['price'])) {
                    $result = collect($result->whereBetween('price', [$validate->filter['price']['from'], $validate->filter['price']['to']])->all());
                }

                if (isset($validate->filter['rate'])) {
                    $result = collect($result->where('rate.0.rate', $validate->filter['rate'])->all());
                }
            }
        }

        // return final result
        return Resp::Success('تم', [
            'productsPropertyList' => $productsPropertyList,
            'result' => $result->values()->all()
        ]);
    }
}
