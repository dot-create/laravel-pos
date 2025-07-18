<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Currency;

class CurrencyController extends Controller {
    public function index()
    {
        // if (!auth()->user()->can('currency.view')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $currencies = Currency::where('business_id', $business_id)->get();

            return Datatables::of($currencies)
                    ->addColumn(
                        'action',
                        '<a href="{{action(\'CurrencyController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_customer_group_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;'
                    )
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('currency.index');
    }
    
    public function create() {
        return view('currency.create');
    }
    
    public function edit($id) {
        $currency = Currency::findOrFail($id);
        return view('currency.edit', compact('currency'));
    }
    
    public function store(Request $request) {
        $request->validate([
            'country' => 'required', 
            'currency' => 'required',
            'symbol' => 'required',
            'code' => 'required',
            'rate' => 'required',
        ]);
        $business_id = request()->session()->get('user.business_id');
        $currency = new Currency();
        $currency->business_id = $business_id;
        $currency->country = $request->country;
        $currency->currency = $request->currency;
        $currency->symbol = $request->symbol;
        $currency->code = $request->code;
        $currency->rate = $request->rate;
        
        $currency->save();
        $output = ['success' => 1,
                        'msg' => __("Currency added successfully")
                    ];
        return redirect()->route('currencies.index')->with('status', $output);
    }
    
    public function destroy($id) {
        return $id;
    }
    
    public function update(Request $request, $id) {
        $currency = Currency::findOrFail($id);
        $request->validate([
            'country' => 'required', 
            'currency' => 'required',
            'symbol' => 'required',
            'code' => 'required',
            'rate' => 'required',
        ]);

        $currency->country = $request->country;
        $currency->currency = $request->currency;
        $currency->symbol = $request->symbol;
        $currency->code = $request->code;
        $currency->rate = $request->rate;
        
        $currency->save();
       $output = ['success' => 1,
                        'msg' => __("Currency Updated successfully")
                    ];
        return redirect()->route('currencies.index')->with('status', $output);
        
    }
}