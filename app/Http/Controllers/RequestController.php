<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RequestItem;
use App\Contact;
use App\Unit;
use App\TaxRate;
use App\BusinessLocation;
use App\WeightUnit;
use App\ShippingWay;
use App\Business;
use App\CustomerRequest;
use App\Transaction;
use App\PurchaseLine;
use App\Currency;
use App\User;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use Yajra\DataTables\Facades\DataTables;
use DB;

class RequestController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil,TransactionUtil $transactionUtil,BusinessUtil $businessUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }
    public function editItem($id){
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        $requestItem = RequestItem::whereHas('request', function ($q) use ($business_id) {
            $q->where('business_id', $business_id);
        })
        ->where('id',$id)
        ->with([
            'request:id,request_reference,business_location_id', // Load request reference
            'request.contact', // Load request reference
            'product:id,name', // Load product name
            'variation:id,product_id,sub_sku',
            'variation.variation_location_details' // Load location details
        ])
        ->first();
        // dd($requestItem);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $units = WeightUnit::all();
        $taxes = TaxRate::where('business_id',$business_id)->get();
        $shippingways = ShippingWay::all();
        $getValues = $this->productUtil->getAvaliableQty($business_id,$requestItem->variation_id,$requestItem->request->business_location_id);
        // dd($taxes);
        $stock_details = $this->productUtil->getVariationStockDetails($business_id, $requestItem->variation_id, $requestItem->request->business_location_id); 
        $avaliabilityQty=$getValues['avaliabilityQty'];
        $currentStock=$getValues['stockOnHand'];
        $intransitQuantity=$getValues['intransitQuantity'];
        return view('sell.request.item_edit',compact('requestItem','suppliers','units','taxes','shippingways','business','avaliabilityQty'));
    }
    public function itemUpdate(Request $request,$id){
        // dd($request->all());
        $this->validate($request,[
            'supplier1'=>'required',
            'quantity1'=>'required',
            'price1'=>'required',
            'freight1'=>'required',
            'ecommerce_fees_percentage1'=>'required',
            'formula_price1'=>'required',
            'is_best_supplier1'=>'required',
            'supplier2'=>'sometimes',
            'quantity2'=>'sometimes',
            'price2'=>'sometimes',
            'freight2'=>'sometimes',
            'ecommerce_fees_percentage2'=>'sometimes',
            'formula_price2'=>'sometimes',
            'is_best_supplier2'=>'required',
            'supplier3'=>'sometimes',
            'quantity3'=>'sometimes',
            'price3'=>'sometimes',
            'freight3'=>'sometimes',
            'ecommerce_fees_percentage3'=>'sometimes',
            'formula_price3'=>'sometimes',
            'is_best_supplier3'=>'required',
            'supplier4'=>'sometimes',
            'quantity4'=>'sometimes',
            'price4'=>'sometimes',
            'freight4'=>'sometimes',
            'ecommerce_fees_percentage4'=>'sometimes',
            'formula_price4'=>'sometimes',
            'is_best_supplier4'=>'required',
            'product_link1'=>'sometimes',
            'product_link2'=>'sometimes',
            'product_link3'=>'sometimes',
            'product_link4'=>'sometimes',
            'weight_unit'=>'required',
            'destination_tax'=>'required',
            'item_notes'=>'required',
            'sell_price_wot'=>'required',
            'purchase_weight'=>'required',
            'shippingway'=>'required',
            'delivery_time'=>'required',
            'delivery_time1'=>'sometimes',
            'delivery_time2'=>'sometimes',
            'delivery_time3'=>'sometimes',
            'delivery_time4'=>'sometimes',
            'supply_ref'=>'required',
            'suggested_sell_price_USD_wot'=>'required',
            'est_fwd_freight'=>'required',
            'total_price'=>'required',
            'status'=>'required'
        ]);
        // dd($id);
        $requestItem=RequestItem::where('id',$id)->first();
        // dd($requestItem);
        if($requestItem){
            $requestItem->supplier1_id= $request->supplier1;
            $requestItem->quantity_supplier1= $request->quantity1;
            $requestItem->unit_price_supplier1= $request->price1;
            $requestItem->freight_supplier1= $request->freight1;
            $requestItem->ecom_fee_percentage_supplier1= $request->ecommerce_fees_percentage1;
            $requestItem->formula_price_supplier1= $request->formula_price1;
            $requestItem->is_best_supplier1= $request->is_best_supplier1;
            $requestItem->supplier2_id= $request->supplier2;
            $requestItem->quantity_supplier2= $request->quantity2;
            $requestItem->unit_price_supplier2= $request->price2;
            $requestItem->freight_supplier2= $request->freight2;
            $requestItem->product_link1= $request->product_link1;
            $requestItem->product_link2= $request->product_link2;
            $requestItem->product_link3= $request->product_link3;
            $requestItem->product_link4= $request->product_link4;
            $requestItem->ecom_fee_percentage_supplier2= $request->ecommerce_fees_percentage2;
            $requestItem->formula_price_supplier2= $request->formula_price2;
            $requestItem->is_best_supplier2= $request->is_best_supplier2;
            $requestItem->supplier3_id= $request->supplier3;
            $requestItem->quantity_supplier3= $request->quantity3;
            $requestItem->unit_price_supplier3= $request->price3;
            $requestItem->freight_supplier3= $request->freight3;
            $requestItem->ecom_fee_percentage_supplier3= $request->ecommerce_fees_percentage3;
            $requestItem->formula_price_supplier3= $request->formula_price3;
            $requestItem->is_best_supplier3= $request->is_best_supplier3;
            $requestItem->supplier4_id= $request->supplier4;
            $requestItem->quantity_supplier4= $request->quantity4;
            $requestItem->unit_price_supplier4= $request->price4;
            $requestItem->freight_supplier4= $request->freight4;
            $requestItem->ecom_fee_percentage_supplier4= $request->ecommerce_fees_percentage4;
            $requestItem->formula_price_supplier4= $request->formula_price4;
            $requestItem->is_best_supplier4= $request->is_best_supplier4;
            $requestItem->weight_unit= $request->weight_unit;
            $requestItem->destination_tax= $request->destination_tax;
            $requestItem->item_notes= $request->item_notes;
            $requestItem->sell_price_wot= $request->sell_price_wot;
            $requestItem->purchase_weight= $request->purchase_weight;
            $requestItem->shipping_way= $request->shippingway;
            $requestItem->delivery_time= $request->delivery_time;
            $requestItem->delivery_time1= $request->delivery_time1;
            $requestItem->delivery_time2= $request->delivery_time2;
            $requestItem->delivery_time3= $request->delivery_time3;
            $requestItem->delivery_time4= $request->delivery_time4;
            $requestItem->supply_ref= $request->supply_ref;
            $requestItem->suggested_sell_price_USD_wot= $request->suggested_sell_price_USD_wot;
            $requestItem->status=$request->status;
            $requestItem->est_fwd_freight=$request->est_fwd_freight;
            $requestItem->total_price=$request->total_price;
            $result=$requestItem->save();
            // dd($result);
        };
        return redirect()->route('request.items')->with(['success'=>'save success']);
        
    }
    public function rejectItem(Request $request,$id){
        $requestItem=RequestItem::where('id',$id)->first();
        if($requestItem){
            $requestItem->status="Rejected";
            $result=$requestItem->save();
        }else{
            return back()->with(['error'=>'Not Found']);
        }
        return back()->with(['success'=>'Save success']);
        
    }
    public function readyToDraftList(){
        try {
            // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            //     abort(403, 'Unauthorized action.');
            // }
            
            $business_id = request()->session()->get('user.business_id');
            // ->whereDoesntHave('items', function ($query) {
            //     $query->where('status', '!=', 'ReadyToDraft') // Exclude if status is NOT ReadyToDraft
            //           ->whereDoesntHave('variation.variation_location_details', function ($q) {
            //               $q->whereColumn('variation_location_details.qty_available', '<', 'request_items.quantity'); // Exclude if qty_available is LESS than requested quantity
            //           });
            // })
            $requestItems = CustomerRequest::where('business_id',$business_id)->whereIn('status',['Pending','ReadyToDraft','Stock'])
            ->with([
                'items.product:id,name,sku',
                'items.variation:id,product_id,sub_sku',
                'items.variation.variation_location_details',
                'contact'
            ])
            ->get()
            ->map(function ($request) {
                $editUrl = route('request.item.draft.edit', [$request->id]);
                $rejectUrl = route('request.quote.reject', [$request->id]);
        
                $actionBtn = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">
                                    ' . __("messages.actions") . '
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">';
        
                // if (auth()->user()->can("customer_request.update")) {
                    $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
                                     <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                // }
        
                // if (auth()->user()->can("customer_request.delete")) {
                    $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                                    <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                // }
        
                $actionBtn .= '</ul></div>';
        
                $request->action = $actionBtn;
        
                return $request;
            });
            $requestArray = [];
    
            foreach ($requestItems as $item) {
                $allItemsHaveStock = true; // Assume all items have enough stock
                $hasReadyToDraft = false;
            
                foreach ($item->items as $itemSingle) {
                    $variationDetails = $itemSingle->variation->variation_location_details
                        ->where('location_id', $item->business_location_id)
                        ->first();
            
                    // Check if item has enough stock
                    if (optional($variationDetails)->qty_available < $itemSingle->quantity) {
                        $allItemsHaveStock = false;
                    }
            
                    // Check if at least one item is ReadyToDraft
                    if ($itemSingle->status === "ReadyToDraft" || $itemSingle->status === "Stock" || $itemSingle->status ==="Supplier-Confirmed") {
                        $hasReadyToDraft = true;
                    }
                }
            
                // If all items have enough stock OR at least one item is ReadyToDraft
                if ($allItemsHaveStock || $hasReadyToDraft) {
                    CustomerRequest::where('id', $item->id)->update(['status' => 'Stock']);
                    $requestArray[] = $item; // Only add to results if the status was updated
                }
            }
            
            $getIds=collect($requestArray)->pluck('id');
            if (request()->ajax()) {
                $filteredItems = $requestArray;
                if (!empty(request()->customer_id)) {
                    $filteredItems = $filteredItems->where('request.contact_id', request()->customer_id);
                }
            
                if (!empty(request()->status)) {
                    $filteredItems = $filteredItems->where('request.status', request()->status);
                }
                if (!empty(request()->sku)) {
                    // dd(request()->sku);
                    $searchTerm=request()->sku;
                    $requests->whereHas('items', function ($query) use ($searchTerm) {
                        $query->whereHas('product', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('sku', $searchTerm);
                        });
                    });
                }
                if (!empty(request()->start_date) && !empty(request()->end_date)) {
                    $start = request()->start_date;
                    $end = request()->end_date;
                    $filteredItems = $filteredItems->whereBetween('request.created_at', [$start, $end]);
                }
                // dd($filteredItems);
                return DataTables::of($filteredItems)
                    
                    ->addIndexColumn() // Adds Sr#
                    ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                    ->addColumn('contact', fn($row) => $row->contact->supplier_business_name ?? $row->contact->name)
                    ->addColumn('ref_no', fn($row) => $row->request_reference)
                    ->addColumn('availability_status', fn($row) => '<span class="label bg-green">ReadyToDraft</span>')
                    ->addColumn('action', fn($row) => $row->action)
                    ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                    ->make(true);
            }
            // dd($requestArray);
            $requestItems=$requestItems;
            $business_locations = BusinessLocation::forDropdown($business_id);
            $suppliers = Contact::customersDropdown($business_id, false);
            $orderStatuses = $this->productUtil->orderStatuses();
            $status = request()->query('status');
           
            return view('sell.request.ready_to_draft')
                ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
        } catch (\Throwable $th) {
            throw $th;
        }


    }
    public function draftEdit($id){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');

        $request = CustomerRequest::where('id', $id)
        ->with([
            'contact:id,name',
            'items' => function ($query) {
                $query->leftJoin('contacts as supplier1', 'supplier1.id', '=', 'request_items.supplier1_id')
                    ->leftJoin('contacts as supplier2', 'supplier2.id', '=', 'request_items.supplier2_id')
                    ->leftJoin('contacts as supplier3', 'supplier3.id', '=', 'request_items.supplier3_id')
                    ->leftJoin('contacts as supplier4', 'supplier4.id', '=', 'request_items.supplier4_id')
                    ->addSelect([
                        'request_items.*',
                        \DB::raw("
                            CASE 
                                WHEN request_items.is_best_supplier1 = 1 THEN supplier1.supplier_business_name
                                WHEN request_items.is_best_supplier2 = 1 THEN supplier2.supplier_business_name
                                WHEN request_items.is_best_supplier3 = 1 THEN supplier3.supplier_business_name
                                WHEN request_items.is_best_supplier4 = 1 THEN supplier4.supplier_business_name
                                ELSE 'No Best Supplier'
                            END AS best_supplier_name
                        ")
                    ]);
            },
            'items.variation',
            'items.product',
            'items.variation.variation_location_details'
        ])
        ->first();
        $business_locations = BusinessLocation::forDropdown($business_id,false,true);
        $bl_attributes = $business_locations['attributes'];
        $orderStatuses = $this->productUtil->requestStatuses();
        $items=$request->items;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $units = WeightUnit::all();
        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();
        $shippingways = ShippingWay::all();
        $business = Business::where('id', $business_id)->first();
        $currency_exchange_rate = Currency::where('id', $business->currency_id)->first();
        $productUtil = $this->productUtil;
        return view('sell.request.draft_edit')
            ->with(compact('request','business_locations','orderStatuses','bl_attributes','items','currency_details','taxes','units','productUtil','business_id','currency_exchange_rate'));
    }
    public function draftUpdate(Request $request,$id){
        // dd($request->all());
        $CustomerRequest = CustomerRequest::find($id);
        if (!$CustomerRequest) {
            return back()->with(['error' => 'Not found']);
        }
        foreach ($request->itemId as $i => $itemId) {
            $total_subtotal_wd = 0;
            $total_subtotal_wd_tax = 0;
            $item = RequestItem::find($itemId);
            if (!$item) continue;

            $quantity = (int)$request->quantity[$i];
            $unit_price = (float)($request->sell_price_wot[$i]);
            // dd($unit_price);
            // --- Discount ---
            $discount = (float)($request->discount[$i] ?? 0);// 1
            $discountType = $request->discount_type[$i] ?? 'fixed';

            $unit_discount = $discountType === 'percentage'
                ? ($discount / 100) * $unit_price
                : $discount;

            $discounted_unit_price = $unit_price - $unit_discount;// 1.63-1= .63

            // --- Tax ---
            $tax_rate = (float)($request->tax_rate[$i] ?? 0); //0
            $unit_tax = ($tax_rate / 100) * $discounted_unit_price; //0
            $final_unit_price = $discounted_unit_price + $unit_tax;//.63

            // --- Totals for this item ---
            $subtotal_wd = round($discounted_unit_price * $quantity, 2);// .63*10=6.3
            $subtotal_wd_tax = round($final_unit_price * $quantity, 2);//.63*10 =6.3
            // --- Notes & Supply Ref ---
            $item_note = $request->item_notes[$i] ?? '';
            $supply_ref = $request->supply_ref[$i] ?? '';

            // --- Update Item ---
            $item->update([
                'discount' => $discount,
                'discount_type' => $discountType,
                'subtotal_wd' => $subtotal_wd,
                'subtotal_wd_tax' => $subtotal_wd_tax,
                'tax' => $tax_rate,
                'item_notes' => $item_note,
                'supply_ref' => $supply_ref,
                'status' => 'Draft',
                'sell_price_wot'=>$request->sell_price_wot[$i],
                'quantity'=>$quantity
            ]);
        }
        // --- Overall Discount ---
        if (!empty($request->overall_discount)) {
            $CustomerRequest->discount = $request->overall_discount;
            $CustomerRequest->discount_type = $request->overall_discount_type;
        }
    
        $CustomerRequest->status = "Draft";
        $CustomerRequest->save();
        return redirect()->route('request.item.draft.list');
    }
    public function draftList(){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = request()->session()->get('user.business_id');
        $requestItems = CustomerRequest::where(['business_id'=> $business_id,'status'=>'Draft'])
        ->with([
            'items.product:id,name',
            'items.variation:id,product_id,sub_sku',
            'items.variation.variation_location_details',
            'contact'
        ])
        ->get()
        ->map(function ($request) {
            $editUrl = route('draftQuoteEdit', [$request->id]);
            // $rejectUrl = route('request.item.reject', [$request->id]);
            // $printUrl=route('purchaseOrder.downloadPdf', [$request->id]);
    
            $actionBtn = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">
                                ' . __("messages.actions") . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
    
            // if (auth()->user()->can("customer_request.update")) {
                $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
            // }
    
            // if (auth()->user()->can("customer_request.delete")) {
            //     $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
            //                     <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
            //     $actionBtn.='<li><a href="' .$printUrl . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
            // }
            
            $actionBtn .= '</ul></div>';
    
            $request->action = $actionBtn;
    
            return $request;
        });
        if (request()->ajax()) {
            $filteredItems = $requestItems;
        
            if (!empty(request()->customer_id)) {
                $filteredItems = $filteredItems->where('contact_id', request()->customer_id);
            }
        
            if (!empty(request()->status)) {
                $filteredItems = $filteredItems->where('status', request()->status);
            }
        
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $filteredItems = $filteredItems->whereBetween('created_at', [$start, $end]);
            }
        
            return DataTables::of($filteredItems)
                
                ->addIndexColumn() // Adds Sr#
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('contact', fn($row) =>$row->contact->supplier_business_name ?? $row->contact->name)
                ->addColumn('ref_no', fn($row) => $row->request_reference)
                ->addColumn('availability_status', fn($row) => '<span class="label bg-green">Draft</span>')
                ->addColumn('action', fn($row) => $row->action)
                ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        $status = request()->query('status');
       
        return view('sell.request.draft_list')
            ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function edit($id){
        $business_id = request()->session()->get('user.business_id');
        $request = CustomerRequest::where('id', $id)
                ->with('contact:id,name','items','items.variation','items.variation.variation_location_details') // Load contact name
                ->first();
        $business_locations = BusinessLocation::forDropdown($business_id,false,true);
        $bl_attributes = $business_locations['attributes'];
        $orderStatuses = $this->productUtil->requestStatuses();
        $items=$request->items;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        
        return view('sell.request.edit')
            ->with(compact('request','business_locations','orderStatuses','bl_attributes','items','currency_details','business_id'));

    }
    public function update(Request $request,$id){
        $customerRequest=CustomerRequest::where('id', $id)->first();
        if(!$customerRequest){
            return back()->with(['error'=>'Customer Request not Found!']);
        }
        $customerRequest->request_reference=$request->ref_no?? $customerRequest->request_reference;
        $customerRequest->description=$request->description;
        $customerRequest->status=$request->status?? $customerRequest->status;
        $customerRequest->save();
        return redirect()->route('requests')->with(['success'=>'Update Success']);
    }
    public function draftQuoteEdit($id){
        $business_id = request()->session()->get('user.business_id');
        $request = CustomerRequest::where('id', $id)
                ->with([
                    'contact:id,name',
                    'items' => function ($query) {
                        $query->leftJoin('contacts as supplier1', 'supplier1.id', '=', 'request_items.supplier1_id')
                            ->leftJoin('contacts as supplier2', 'supplier2.id', '=', 'request_items.supplier2_id')
                            ->leftJoin('contacts as supplier3', 'supplier3.id', '=', 'request_items.supplier3_id')
                            ->leftJoin('contacts as supplier4', 'supplier4.id', '=', 'request_items.supplier4_id')
                            ->addSelect([
                                'request_items.*',
                                \DB::raw("
                                    CASE 
                                        WHEN request_items.is_best_supplier1 = 1 THEN supplier1.supplier_business_name
                                        WHEN request_items.is_best_supplier2 = 1 THEN supplier2.supplier_business_name
                                        WHEN request_items.is_best_supplier3 = 1 THEN supplier3.supplier_business_name
                                        WHEN request_items.is_best_supplier4 = 1 THEN supplier4.supplier_business_name
                                        ELSE 'No Best Supplier'
                                    END AS best_supplier_name
                                ")
                            ]);
                    },
                    'items.variation',
                    'items.variation.variation_location_details'
                ]) // Load contact name
                ->first();
        $business_locations = BusinessLocation::forDropdown($business_id,false,true);
        $bl_attributes = $business_locations['attributes'];
        $orderStatuses = $this->productUtil->requestStatuses();
        $items=$request->items;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $business = Business::where('id', $business_id)->first();
        $currency_exchange_rate = Currency::where('id', $business->currency_id)->first();
        $totalFinal = $request->items->sum('subtotal_wd_tax');
        $productUtil = $this->productUtil;

        $items = $request->items; // array of items
        $totalItemDiscount = 0;
        $totalItemTax = 0;
        $subtotalAfterItemDiscount = 0;
        $subtotalAfterDiscountAndTax = 0;

        foreach ($items as $item) {
            $unitPrice = $item->sell_price_wot ?? $item->variation->default_sell_price;
            $unitPrice = (float) $unitPrice;
            $quantity = (int) $item['quantity'];

            $discountType = $item['discount_type']; // "fixed" or "percentage"
            $discountValue = (float) $item['discount']; // per unit
            $taxPercentage = (float) $item['tax']; // %

            // --- Calculate discount per unit ---
            $unitDiscount = 0;
            if ($discountType === 'fixed') {
                $unitDiscount = $discountValue;
            } elseif ($discountType === 'percentage') {
                $unitDiscount = ($discountValue / 100) * $unitPrice;
            }

            $discountedUnitPrice = $unitPrice - $unitDiscount;

            // --- Tax on discounted unit price ---
            $unitTax = ($taxPercentage / 100) * $discountedUnitPrice;

            // --- Final unit price ---
            $finalUnitPrice = $discountedUnitPrice + $unitTax;

            // --- Totals for quantity ---
            $itemDiscount = $unitDiscount * $quantity;
            $itemTax = $unitTax * $quantity;
            $itemSubtotalAfterDiscount = $discountedUnitPrice * $quantity;
            $itemFinalTotal = $finalUnitPrice * $quantity;

            // --- Accumulate Totals ---
            $totalItemDiscount += $itemDiscount;
            $totalItemTax += $itemTax;
            $subtotalAfterItemDiscount += $itemSubtotalAfterDiscount;
            $subtotalAfterDiscountAndTax += $itemFinalTotal;
        }

        // --- Request-Level Discount ---
        $requestDiscountType = $request->discount_type ?? 'fixed';
        $requestDiscountValue = $request->discount ?? 0;
        $requestDiscount = 0;
        if ($requestDiscountType === 'fixed') {
            $requestDiscount = (float) $requestDiscountValue;
        } else{
            $requestDiscount = $subtotalAfterDiscountAndTax * ((float) $requestDiscountValue / 100);
        }
        // --- Final Total ---
        $finalTotal = $subtotalAfterItemDiscount - $requestDiscount + $totalItemTax;
        $requestTax=$request->tax?? 0;
        $totalRequestTax=0;
        if($requestTax != 0){
            $totalRequestTax = $finalTotal * ((float) $requestTax / 100);
        }
        $finalTotal=$finalTotal + $totalRequestTax;
        return view('sell.request.edit_quote')
            ->with(compact('request','business_id','productUtil','business_locations','orderStatuses','bl_attributes','items','currency_details','currency_exchange_rate','finalTotal'));
    }
    public function updateDraftquote(Request $request,$id){
        // dd($request->all());
        $CustomerRequest=CustomerRequest::where('id',$id)->first();
        if($CustomerRequest){
            for($i=0;$i < count($request->itemId); $i++){
                $item=RequestItem::where('id',$request->itemId[$i])->first();
                $item->item_notes=$request->item_notes[$i];
                $item->seller_note=$request->supply_ref[$i];
                $item->sell_line_note=$request->sell_line_note[$i];
                $item->sell_price_wot=$request->sell_price_wot[$i];
                $item->quantity=$request->quantity[$i];
                $item->save();
            }
            if(count($request->discount) > 0){
                for($i = 0; $i < count($request->discount); $i++){
                    if($request->discount[$i] != ""){
                        $item = RequestItem::where('id', $request->itemId[$i])->first();
            
                        $quantity = $item->quantity;
                        $unitPrice = (float) $item->sell_price_wot ?? $item->variation->default_sell_price;
            
                        $discount = (float)$request->discount[$i];
                        $discountType = $request->discount_type[$i];
            
                        // Apply discount per unit
                        if ($discountType === "percentage") {
                            $unitDiscount = ($discount / 100) * $unitPrice;
                        } else {
                            $unitDiscount = $discount; // fixed discount per unit
                        }
            
                        // Calculate discounted unit price and total
                        $discountedUnitPrice = (float) $unitPrice - (float) $unitDiscount;
                        $subtotal_wd = $discountedUnitPrice * $quantity;
                         // --- Tax ---
                        $tax_rate = (float)($request->tax_rate[$i] ?? 0); //0
                        $unit_tax = ($tax_rate / 100) * $discountedUnitPrice; //0
                        $final_unit_price = $discountedUnitPrice + $unit_tax;//.63

                        // --- Totals for this item ---
                        $subtotal_wd = round($discountedUnitPrice * $quantity, 2);// .63*10=6.3
                        $subtotal_wd_tax = round($final_unit_price * $quantity, 2);//.63*10 =6.3
            
                        // Update the item
                        RequestItem::where('id', $request->itemId[$i])->update([
                            'discount' => $request->discount[$i],
                            'discount_type' => $request->discount_type[$i],
                            'subtotal_wd' => $subtotal_wd,
                            'subtotal_wd_tax' => $subtotal_wd_tax,
                            'tax' => $tax_rate,
                            'status' => 'Quote'
                        ]);
                    }
                }
            }    
                    
            if($request->overall_discount !="0"){
                $CustomerRequest->discount=$request->overall_discount;
                $CustomerRequest->discount_type=$request->overall_discount_type;
                
            }
            if($request->overall_tax !="0"){
                $CustomerRequest->tax=$request->overall_tax;
                
            }
            // dd($CustomerRequest);
            $CustomerRequest->request_note = $request->globale_note;
            $CustomerRequest->status="Quote";
            $CustomerRequest->save();
        }
        else{
            return back()->with(['error'=>'Not found']);
        }
        return redirect()->route('request.item.draft.list');
    }
    public function listQuote(){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = request()->session()->get('user.business_id');
        $requestItems = CustomerRequest::where(['business_id'=> $business_id,'status'=>'Quote'])
        ->with([
            'items.product:id,name',
            'items.variation:id,product_id,sub_sku',
            'items.variation.variation_location_details',
            'contact'
        ])
        ->get()
        ->map(function ($request) {
            $editUrl = route('draftQuoteEdit', [$request->id]);
            $acceptUrl = route('request.quote.accept', [$request->id]);
            $rejectUrl = route('request.quote.reject', [$request->id]);
            $printUrl=route('request.quote.print', [$request->id]);
    
            $actionBtn = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">
                                ' . __("messages.actions") . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
    
            // if (auth()->user()->can("customer_request.update")) {
                $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
            // }
    
            // if (auth()->user()->can("customer_request.delete")) {
                $actionBtn .= '<li><a href="' . $acceptUrl . '" class="delete-request">
                                <i class="fas fa-check"></i> ' . __("request.accept") . '</a></li>';
                $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                                <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                $actionBtn.='<li><a href="' .$printUrl . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
            // }
            
            $actionBtn .= '</ul></div>';
    
            $request->action = $actionBtn;
    
            return $request;
        });
        if (request()->ajax()) {
            $filteredItems = $requestItems;
        
            if (!empty(request()->customer_id)) {
                $filteredItems = $filteredItems->where('contact_id', request()->customer_id);
            }
        
            if (!empty(request()->status)) {
                $filteredItems = $filteredItems->where('status', request()->status);
            }
        
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $filteredItems = $filteredItems->whereBetween('created_at', [$start, $end]);
            }
        
            return DataTables::of($filteredItems)
                
                ->addIndexColumn() // Adds Sr#
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('contact', fn($row) => $row->contact->supplier_business_name ?? $row->contact->name)
                ->addColumn('ref_no', fn($row) => $row->request_reference)
                ->addColumn('availability_status', fn($row) => '<span class="label bg-green">'.$row->status.'</span>')
                ->addColumn('action', fn($row) => $row->action)
                ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        $status = request()->query('status');
       
        return view('sell.request.quote_list')
            ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function listRejected(){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = request()->session()->get('user.business_id');
        $requestItems = CustomerRequest::where(['business_id'=> $business_id,'status'=>'RejectedQuote'])
        ->with([
            'items.product:id,name',
            'items.variation:id,product_id,sub_sku',
            'items.variation.variation_location_details',
            'contact'
        ])
        ->get()
        ->map(function ($request) {
            $editUrl = route('draftQuoteEdit', [$request->id]);
            $acceptUrl = route('request.quote.accept', [$request->id]);
            $rejectUrl = route('request.quote.reject', [$request->id]);
            $printUrl=route('request.quote.print', [$request->id]);
    
            $actionBtn = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">
                                ' . __("messages.actions") . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                            // if (auth()->user()->can("customer_request.view")) {
                                $actionBtn .= '<li><a href="' . action('PurchaseController@viewRequests', [$request->id]) . '">
                                            <i class="fas fa-eye"></i> ' . __("messages.view") . '</a></li>';
                            // }
            // if (auth()->user()->can("customer_request.update")) {
            //     $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
            //                      <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
            // }
    
            if (auth()->user()->can("customer_request.delete")) {
                // $actionBtn .= '<li><a href="' . $acceptUrl . '" class="delete-request">
                //                 <i class="fas fa-check"></i> ' . __("request.accept") . '</a></li>';
                // $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                //                 <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                // $actionBtn.='<li><a href="' .$printUrl . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
            }
            
            $actionBtn .= '</ul></div>';
    
            $request->action = $actionBtn;
    
            return $request;
        });
        if (request()->ajax()) {
            $filteredItems = $requestItems;
        
            if (!empty(request()->customer_id)) {
                $filteredItems = $filteredItems->where('contact_id', request()->customer_id);
            }
        
            if (!empty(request()->status)) {
                $filteredItems = $filteredItems->where('status', request()->status);
            }
        
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $filteredItems = $filteredItems->whereBetween('created_at', [$start, $end]);
            }
        
            return DataTables::of($filteredItems)
                
                ->addIndexColumn() // Adds Sr#
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('contact', fn($row) => $row->contact->supplier_business_name ?? $row->contact->name)
                ->addColumn('ref_no', fn($row) => $row->request_reference)
                ->addColumn('availability_status', fn($row) => '<span class="label bg-danger">'.$row->status.'</span>')
                ->addColumn('action', fn($row) => $row->action)
                ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        $status = request()->query('status');
       
        return view('sell.request.quote_reject')
            ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function acceptedQuote(){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = request()->session()->get('user.business_id');
        $requestItems = CustomerRequest::where(['business_id'=> $business_id,'status'=>'AcceptedQuote'])
        ->with([
            'items.product:id,name',
            'items.variation:id,product_id,sub_sku',
            'items.variation.variation_location_details',
            'contact'
        ])
        ->get()
        ->map(function ($request) {
            $editUrl = route('draftQuoteEdit', [$request->id]);
            $acceptUrl = route('request.quote.accept', [$request->id]);
            $rejectUrl = route('request.quote.reject', [$request->id]);
            $printUrl=route('request.quote.print', [$request->id]);
            // $printUrl='#';
    
            $actionBtn = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">
                                ' . __("messages.actions") . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
    
            // if (auth()->user()->can("customer_request.update")) {
            //     $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
            //                      <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
            // }
    
            // if (auth()->user()->can("customer_request.delete")) {
                // $actionBtn .= '<li><a href="' . $acceptUrl . '" class="delete-request">
                //                 <i class="fas fa-check"></i> ' . __("request.accept") . '</a></li>';
                // $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                //                 <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                $actionBtn.='<li><a href="' .$printUrl . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
            // }
            
            $actionBtn .= '</ul></div>';
    
            $request->action = $actionBtn;
    
            return $request;
        });
        if (request()->ajax()) {
            $filteredItems = $requestItems;
        
            if (!empty(request()->customer_id)) {
                $filteredItems = $filteredItems->where('contact_id', request()->customer_id);
            }
        
            if (!empty(request()->status)) {
                $filteredItems = $filteredItems->where('status', request()->status);
            }
        
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $filteredItems = $filteredItems->whereBetween('created_at', [$start, $end]);
            }
        
            return DataTables::of($filteredItems)
                
                ->addIndexColumn() // Adds Sr#
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('contact', fn($row) => $row->contact->supplier_business_name ?? $row->contact->name)
                ->addColumn('ref_no', fn($row) => $row->request_reference)
                ->addColumn('availability_status', fn($row) => '<span class="label bg-green">'.$row->status.'</span>')
                ->addColumn('action', fn($row) => $row->action)
                ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        $status = request()->query('status');
        //    dd('wq');
        return view('sell.request.list_quote_accept')
            ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function acceptQuote($id){
        $business_id = request()->session()->get('user.business_id');
        $request = CustomerRequest::where('id', $id)
                ->with([
                    'contact:id,name',
                    'items' => function ($query) {
                        $query->leftJoin('contacts as supplier1', 'supplier1.id', '=', 'request_items.supplier1_id')
                            ->leftJoin('contacts as supplier2', 'supplier2.id', '=', 'request_items.supplier2_id')
                            ->leftJoin('contacts as supplier3', 'supplier3.id', '=', 'request_items.supplier3_id')
                            ->leftJoin('contacts as supplier4', 'supplier4.id', '=', 'request_items.supplier4_id')
                            ->addSelect([
                                'request_items.*',
                                \DB::raw("
                                    CASE 
                                        WHEN request_items.is_best_supplier1 = 1 THEN supplier1.supplier_business_name
                                        WHEN request_items.is_best_supplier2 = 1 THEN supplier2.supplier_business_name
                                        WHEN request_items.is_best_supplier3 = 1 THEN supplier3.supplier_business_name
                                        WHEN request_items.is_best_supplier4 = 1 THEN supplier4.supplier_business_name
                                        ELSE 'No Best Supplier'
                                    END AS best_supplier_name
                                ")
                            ]);
                    },
                    'items.variation',
                    'items.variation.variation_location_details'
                ]) // Load contact name
                ->first();
        $business_locations = BusinessLocation::forDropdown($business_id,false,true);
        $bl_attributes = $business_locations['attributes'];
        $orderStatuses = $this->productUtil->requestStatuses();
        $items=$request->items;
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        
        return view('sell.request.quote_accept')
            ->with(compact('request','business_locations','orderStatuses','bl_attributes','items','currency_details','business_id'));
    }
    public function accepteQuoteForm(Request $request,$id){
        $CustomerRequest=CustomerRequest::where('id',$id)->first();
        if($CustomerRequest){
            for($i=0;$i < count($request->itemId); $i++){
                $item=RequestItem::where('id',$request->itemId[$i])->first();
                $item->accepted_qty=$request->accepted_qty[$i];
                // $item->backorder_qty=$request->backorder_qty[$i];
                // $item->invoice_qty=$request->invoice_qty[$i];
                $item->po_number=$request->po_number[$i];
                $item->status='AcceptedQuote';
                $item->save();
            }
            $CustomerRequest->request_note = $request->globale_note;
            $CustomerRequest->status="AcceptedQuote";
            $CustomerRequest->save();
        }
        else{
            return back()->with(['error'=>'Not found']);
        }
        return redirect()->route('request.list.quote.accept');
    }
    public function listDisputed(){
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $business_id = request()->session()->get('user.business_id');
        $requestItems = CustomerRequest::where(['business_id'=> $business_id,'status'=>'DisputeQuote'])
        ->with([
            'items.product:id,name',
            'items.variation:id,product_id,sub_sku',
            'items.variation.variation_location_details',
            'contact'
        ])
        ->get()
        ->map(function ($request) {
            $editUrl = route('draftQuoteEdit', [$request->id]);
            $acceptUrl = route('request.quote.accept', [$request->id]);
            $rejectUrl = route('request.quote.reject', [$request->id]);
            $printUrl=route('request.quote.print', [$request->id]);
    
            $actionBtn = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">
                                ' . __("messages.actions") . '
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
    
            // if (auth()->user()->can("customer_request.update")) {
                $actionBtn .= '<li><a href="' . $editUrl . '" class="edit-request">
                                 <i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
            // }
    
            // if (auth()->user()->can("customer_request.delete")) {
                // $actionBtn .= '<li><a href="' . $acceptUrl . '" class="delete-request">
                //                 <i class="fas fa-check"></i> ' . __("request.accept") . '</a></li>';
                // $actionBtn .= '<li><a href="' . $rejectUrl . '" class="delete-request">
                //                 <i class="fas fa-times"></i> ' . __("messages.reject") . '</a></li>';
                // $actionBtn.='<li><a href="' .$printUrl . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __("lang_v1.download_pdf") . '</a></li>';
            // }
            
            $actionBtn .= '</ul></div>';
    
            $request->action = $actionBtn;
    
            return $request;
        });
        if (request()->ajax()) {
            $filteredItems = $requestItems;
        
            if (!empty(request()->customer_id)) {
                $filteredItems = $filteredItems->where('contact_id', request()->customer_id);
            }
        
            if (!empty(request()->status)) {
                $filteredItems = $filteredItems->where('status', request()->status);
            }
        
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $filteredItems = $filteredItems->whereBetween('created_at', [$start, $end]);
            }
        
            return DataTables::of($filteredItems)
                
                ->addIndexColumn() // Adds Sr#
                ->addColumn('date', fn($row) => $row->created_at ?? 'N/A')
                ->addColumn('contact', fn($row) => $row->contact->name ?? 'N/A')
                ->addColumn('ref_no', fn($row) => $row->request_reference)
                ->addColumn('availability_status', fn($row) => '<span class="label bg-green">'.$row->status.'</span>')
                ->addColumn('action', fn($row) => $row->action)
                ->rawColumns(['availability_status', 'action']) // Allow HTML rendering
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::customersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        $status = request()->query('status');
       
        return view('sell.request.quote_dispute')
            ->with(compact('requestItems','business_locations', 'suppliers', 'orderStatuses','status'));
    }
    public function rejectQuote($id){
        $CustomerRequest=CustomerRequest::with('items')->where('id',$id)->first();
        if($CustomerRequest){
            foreach($CustomerRequest->items as $item){
                $item->status='RejectedQuote';
                $item->save();
            }
            $CustomerRequest->status="RejectedQuote";
            $CustomerRequest->save();
        }
        else{
            return back()->with(['error'=>'Not found']);
        }
        return redirect()->route('request.quote.listRejected');
    }
    /**
     * download pdf for given purchase order
     *
     */
    // public function printQuote($id)
    // {   

    //     if (!((auth()->user()->can("purchase_order.view_all") || auth()->user()->can("purchase_order.view_own")))) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');
    //     $business = Business::where('id',$business_id)->first();
    //     $taxes = TaxRate::where('business_id', $business_id)
    //         ->get();
    //     $request = CustomerRequest::where('id', $id)
    //         ->with([
    //             'contact:id,name',
    //             'items' => function ($query) {
    //                 $query->leftJoin('contacts as supplier1', 'supplier1.id', '=', 'request_items.supplier1_id')
    //                     ->leftJoin('contacts as supplier2', 'supplier2.id', '=', 'request_items.supplier2_id')
    //                     ->leftJoin('contacts as supplier3', 'supplier3.id', '=', 'request_items.supplier3_id')
    //                     ->leftJoin('contacts as supplier4', 'supplier4.id', '=', 'request_items.supplier4_id')
    //                     ->addSelect([
    //                         'request_items.*',
    //                         \DB::raw("
    //                             CASE 
    //                                 WHEN request_items.is_best_supplier1 = 1 THEN supplier1.supplier_business_name
    //                                 WHEN request_items.is_best_supplier2 = 1 THEN supplier2.supplier_business_name
    //                                 WHEN request_items.is_best_supplier3 = 1 THEN supplier3.supplier_business_name
    //                                 WHEN request_items.is_best_supplier4 = 1 THEN supplier4.supplier_business_name
    //                                 ELSE 'No Best Supplier'
    //                             END AS best_supplier_name
    //                         ")
    //                     ]);
    //             },
    //             'items.variation',
    //             'items.variation.variation_location_details','items.product','items.product.brand','items.product.category'
    //         ]) // Load contact name
    //     ->first();
    //     $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id,$request->business_location_id);

    //     // dd($purchase);
    //     if(!$request) {
    //         return "not found";
    //     }
    //     $location_details = BusinessLocation::find($request->business_location_id);
    //     $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_details->invoice_layout_id);

    //     //Logo
    //     $logo = $invoice_layout->show_logo != 0 && !empty($invoice_layout->logo) && file_exists(public_path('uploads/invoice_logos/' . $invoice_layout->logo)) ? asset('uploads/invoice_logos/' . $invoice_layout->logo) : false;

    //     $word_format = $invoice_layout->common_settings['num_to_word_format'] ? $invoice_layout->common_settings['num_to_word_format'] : 'international';
    //     // $total_in_words = $this->transactionUtil->numToWord($purchase->final_total, null, $word_format);
    //     $total_in_words = $this->transactionUtil->numToWord($request->items->sum('total_price'), null, $word_format);

    //     // $custom_labels = json_decode(session('business.custom_labels'), true);

    //     // $last_purchase = Transaction::where('purchase_order_ids', 'like', '%"' . $purchase->id . '"%')->orderBy('transaction_date', 'desc')->first();
    //     //Generate pdf
    //     // return view('sell.request.download_pdf')
    //     //->with(compact('purchase', 'invoice_layout', 'location_details', 'logo', 'total_in_words', 'custom_labels', 'taxes', 'last_purchase','currency_details'));
    //     $body = view('sell.request.download_pdf')
    //                 ->with(compact('request', 'invoice_layout', 'location_details', 'logo', 'total_in_words', 'taxes','currency_details','business'))
    //                 ->render();

    //     $mpdf = new \Mpdf\Mpdf(['tempDir' => public_path('uploads/temp'), 
    //                 'mode' => 'utf-8', 
    //                 'autoScriptToLang' => true,
    //                 'autoLangToFont' => true,
    //                 'autoVietnamese' => true,
    //                 'autoArabic' => true,
    //                 'margin_top' => 8,
    //                 'margin_bottom' => 8,
    //                 'format' => 'A4'
    //             ]);
    //     // dd($mpdf);
    //     $mpdf->useSubstitutions=true;
    //     $mpdf->SetWatermarkText($business->name, 0.1);
    //     $mpdf->showWatermarkText = true;
    //     $mpdf->SetTitle('PO-'.$request->request_reference.'.pdf');
    //     $mpdf->WriteHTML($body);
    //     $mpdf->Output('PO-'.$request->request_reference.'.pdf', 'I');
    // }
    public function printQuote($id) {
        $printer_type = null;
        $is_package_slip = false;
        $from_pos_screen = true;
        $invoice_layout_id = null;
        $is_delivery_note = false;
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];
        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->get();
        $request = CustomerRequest::where('id', $id)
            ->with([
                'contact:id,name',
                'items' => function ($query) {
                    $query->leftJoin('contacts as supplier1', 'supplier1.id', '=', 'request_items.supplier1_id')
                        ->leftJoin('contacts as supplier2', 'supplier2.id', '=', 'request_items.supplier2_id')
                        ->leftJoin('contacts as supplier3', 'supplier3.id', '=', 'request_items.supplier3_id')
                        ->leftJoin('contacts as supplier4', 'supplier4.id', '=', 'request_items.supplier4_id')
                        ->addSelect([
                            'request_items.*',
                            \DB::raw("
                                CASE 
                                    WHEN request_items.is_best_supplier1 = 1 THEN supplier1.supplier_business_name
                                    WHEN request_items.is_best_supplier2 = 1 THEN supplier2.supplier_business_name
                                    WHEN request_items.is_best_supplier3 = 1 THEN supplier3.supplier_business_name
                                    WHEN request_items.is_best_supplier4 = 1 THEN supplier4.supplier_business_name
                                    ELSE 'No Best Supplier'
                                END AS best_supplier_name
                            ")
                        ]);
                },
                'items.variation','items.variation.media',
                'items.variation.variation_location_details','items.product','items.product.brand','items.product.category'
            ]) // Load contact name
        ->first();
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id,$request->business_location_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($request->business_location_id);
        $currency_details = Currency::where('id', $location_details->currency_id)->first();

        $output['is_enabled'] = true;

        $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;
        $business_details->currency_symbol = $currency_details->symbol;
        $business_details->exchange_rate = $currency_details->rate;
        $receipt_details = $this->transactionUtil->getRequestReceiptDetails($request, $request->business_location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

        $receipt_details->currency = $currency_details;

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details'))->render();
            return $output;
        }

        if ($is_delivery_note) {
            $output['html_content'] = view('sale_pos.receipts.delivery_note', compact('receipt_details'))->render();
            return $output;
        }

        $output['print_title'] = $receipt_details->invoice_no;

        $totalItemDiscount = 0;
        $subtotalAfterItemDiscount = 0;
        $totalItemTax = 0;
        $subtotalAfterDiscountAndTax =0;

        // Sample request structure
        $items = $request->items; // array of items
        $totalItemDiscount = 0;
        $totalItemTax = 0;
        $subtotalAfterItemDiscount = 0;
        $subtotalAfterDiscountAndTax = 0;

        foreach ($items as $item) {
            $unitPrice = $item->sell_price_wot ?? $item->variation->default_sell_price;
            $unitPrice = (float) $unitPrice;
            $quantity = (int) $item['quantity'];

            $discountType = $item['discount_type']; // "fixed" or "percentage"
            $discountValue = (float) $item['discount']; // per unit
            $taxPercentage = (float) $item['tax']; // %

            // --- Calculate discount per unit ---
            $unitDiscount = 0;
            if ($discountType === 'fixed') {
                $unitDiscount = $discountValue;
            } elseif ($discountType === 'percentage') {
                $unitDiscount = ($discountValue / 100) * $unitPrice;
            }

            $discountedUnitPrice = $unitPrice - $unitDiscount;

            // --- Tax on discounted unit price ---
            $unitTax = ($taxPercentage / 100) * $discountedUnitPrice;

            // --- Final unit price ---
            $finalUnitPrice = $discountedUnitPrice + $unitTax;

            // --- Totals for quantity ---
            $itemDiscount = $unitDiscount * $quantity;
            $itemTax = $unitTax * $quantity;
            $itemSubtotalAfterDiscount = $discountedUnitPrice * $quantity;
            $itemFinalTotal = $finalUnitPrice * $quantity;

            // --- Accumulate Totals ---
            $totalItemDiscount += $itemDiscount;
            $totalItemTax += $itemTax;
            $subtotalAfterItemDiscount += $itemSubtotalAfterDiscount;
            $subtotalAfterDiscountAndTax += $itemFinalTotal;
        }

        // --- Request-Level Discount ---
        $requestDiscountType = $request->discount_type ?? 'fixed';
        $requestDiscountValue = $request->discount ?? 0;
        $requestDiscount = 0;
        if ($requestDiscountType === 'fixed') {
            $requestDiscount = (float) $requestDiscountValue;
        } else{
            $requestDiscount = $subtotalAfterDiscountAndTax * ((float) $requestDiscountValue / 100);
        }
        // --- Final Total ---
        $finalTotal = $subtotalAfterItemDiscount - $requestDiscount + $totalItemTax;
        $requestTax=$request->tax?? 0;
        $totalRequestTax=0;
        if($requestTax != 0){
            $totalRequestTax = $finalTotal * ((float) $requestTax / 100);
        }
        $finalTotal=$finalTotal + $totalRequestTax;
        // dd($receipt_details->design);
        $requestDiscountValue=$requestDiscount;
        $invoice_no = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $location_details->id);
        $layout = 'sale_pos.receipts.request';
        $output['html_content'] = view($layout, compact('receipt_details','request','totalItemDiscount',
        'subtotalAfterItemDiscount','totalItemTax','requestDiscountValue',
        'finalTotal','subtotalAfterDiscountAndTax','business_details','totalRequestTax','invoice_no'))->render();
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            // dd($receipt_details->design);
            // $invoice_no = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $location_details->id);
            // $layout = 'sale_pos.receipts.request';
            // $output['html_content'] = view($layout, compact('receipt_details','request','totalItemDiscount',
            // 'subtotalAfterItemDiscount','totalItemTax','requestDiscountValue',
            // 'finalTotal','subtotalAfterDiscountAndTax','business_details','totalRequestTax'))->render();
        }
        
        $receipt=$output;
        $title=$invoice_no;
        return view('sale_pos.partials.show_quote')
        ->with(compact('receipt', 'title'));
    }


    // Add these methods to your RequestController

    /**
     * Get company users for assignment dropdown
     */
    public function getCompanyUsers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get all users belonging to the same business/company
        $users = User::whereHas('business_users', function($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })->select('id', 'first_name', 'last_name', 'username')
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->last_name) ?: $user->username
            ];
        });

        return response()->json($users);
    }

    /**
     * Assign user to request item
     */
    public function assignUser(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:request_items,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $business_id = $request->session()->get('user.business_id');
        
        // Find the request item and verify it belongs to the current business
        $requestItem = RequestItem::whereHas('request', function($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })->findOrFail($request->item_id);

        // Verify the user belongs to the same business
        $user = User::whereHas('business_users', function($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })->findOrFail($request->user_id);

        // Update the assignment
        $requestItem->assigned_to = $request->user_id;
        $requestItem->save();

        return response()->json([
            'success' => true,
            'message' => 'User assigned successfully',
            'assigned_user' => [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->last_name) ?: $user->username
            ]
        ]);
    }

    /**
     * Get pending quantities summary by assigned users
     */
    public function getPendingQtyByUsers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        // Get pending quantities grouped by assigned users
        $pendingStats = RequestItem::whereHas('request', function($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })
        ->where('status', 'Pending') // Adjust status as needed
        ->with('assignedUser')
        ->get()
        ->groupBy('assigned_to')
        ->map(function($items, $userId) {
            $user = $items->first()->assignedUser;
            return [
                'user_id' => $userId,
                'user_name' => $user ? (trim($user->first_name . ' ' . $user->last_name) ?: $user->username) : 'Unassigned',
                'total_quantity' => $items->sum('quantity'),
                'items_count' => $items->count()
            ];
        });

        return response()->json($pendingStats->values());
    }

    /**
     * Get filtered request items (for AJAX)
     */
    public function getFilteredItems(Request $request, $requestId)
    {
        $business_id = $request->session()->get('user.business_id');


        // Fetch the customer request with relationships
        $customerRequest = CustomerRequest::where('business_id', $business_id)
            ->with('contact:id,name', 'items', 'items.variation', 'items.variation.variation_location_details', 'items.assignedUser')
            ->firstOrFail();

        $items = $customerRequest->items;

        
        // Apply filters only if input is not empty

        $assignedUser = $request->input('assigned_user');
        $status = $request->input('status');

        if ($assignedUser !== null && $assignedUser !== '') {
            if ($assignedUser === 'unassigned') {
                $items = $items->whereNull('assigned_to');
            } else {
                $items = $items->where('assigned_to', $assignedUser);
            }
        }

        if ($status !== null && $status !== '') {
            $items = $items->where('status', $status);
        }


        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $productUtil=$this->productUtil;

        
        // Return filtered items as HTML
        $html = view('sell.request.partials.items_table_rows', compact('request', 'items', 'productUtil', 'business_id'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $items->count()
        ]);
    }
}
