<?php

namespace App\Http\Controllers;

use App\Account;

use App\AccountTransaction;
use App\BusinessLocation;
use App\ExpenseCategory;
use App\TaxRate;
use App\Transaction;
use App\ExpensePurchase;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Contact;
use App\Currency;
use App\Utils\CashRegisterUtil;

class ExpenseController extends Controller
{
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, CashRegisterUtil $cashRegisterUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
                            ->leftJoin('expense_categories AS esc', 'transactions.expense_sub_category_id', '=', 'esc.id')
                            ->join(
                                'business_locations AS bl',
                                'transactions.location_id',
                                '=',
                                'bl.id'
                            )
                            ->leftJoin('tax_rates as tr', 'transactions.tax_id', '=', 'tr.id')
                            ->leftJoin('users AS U', 'transactions.expense_for', '=', 'U.id')
                            ->leftJoin('users AS usr', 'transactions.created_by', '=', 'usr.id')
                            ->leftJoin('contacts AS c', 'transactions.contact_id', '=', 'c.id')
                            ->leftJoin(
                                'transaction_payments AS TP',
                                'transactions.id',
                                '=',
                                'TP.transaction_id'
                            )
                            ->where('transactions.business_id', $business_id)
                            ->whereIn('transactions.type', ['expense', 'expense_refund'])
                            ->select(
                                'transactions.id',
                                'transactions.document',
                                'transaction_date',
                                'transactions.created_at',
                                'ref_no',
                                'ec.name as category',
                                'esc.name as sub_category',
                                'payment_status',
                                'additional_notes',
                                'final_total',
                                'transactions.is_recurring',
                                'transactions.recur_interval',
                                'transactions.recur_interval_type',
                                'transactions.recur_repetitions',
                                'transactions.subscription_repeat_on',
                                'bl.name as location_name',
                                'bl.id as location_id',
                                'bl.currency_id as currency_id',
                                DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as expense_for"),
                                DB::raw("CONCAT(tr.name ,' (', tr.amount ,' )') as tax"),
                                DB::raw('SUM(TP.amount) as amount_paid'),
                                DB::raw("CONCAT(COALESCE(usr.surname, ''),' ',COALESCE(usr.first_name, ''),' ',COALESCE(usr.last_name,'')) as added_by"),
                                'transactions.recur_parent_id',
                                DB::raw("CONCAT(COALESCE(c.name, ''), ' - ', COALESCE(c.supplier_business_name, ''), '(', COALESCE(c.contact_id, ''), ')') as contact_name"),
                                'transactions.type'
                            )
                            ->with(['recurring_parent'])
                            ->groupBy('transactions.id');

            //Add condition for expense for,used in sales representative expense report & list of expense
            if (request()->has('expense_for')) {
                $expense_for = request()->get('expense_for');
                if (!empty($expense_for)) {
                    $expenses->where('transactions.expense_for', $expense_for);
                }
            }

            if (request()->has('contact_id')) {
                $contact_id = request()->get('contact_id');
                if (!empty($contact_id)) {
                    $expenses->where('transactions.contact_id', $contact_id);
                }
            }

            //Add condition for location,used in sales representative expense report & list of expense
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $expenses->where('transactions.location_id', $location_id);
                }
            }

            //Add condition for expense category, used in list of expense,
            if (request()->has('expense_category_id')) {
                $expense_category_id = request()->get('expense_category_id');
                if (!empty($expense_category_id)) {
                    $expenses->where('transactions.expense_category_id', $expense_category_id);
                }
            }

            //Add condition for expense sub category, used in list of expense,
            if (request()->has('expense_sub_category_id')) {
                $expense_sub_category_id = request()->get('expense_sub_category_id');
                if (!empty($expense_sub_category_id)) {
                    $expenses->where('transactions.expense_sub_category_id', $expense_sub_category_id);
                }
            }

            //Add condition for start and end date filter, uses in sales representative expense report & list of expense
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $expenses->whereDate('transaction_date', '>=', $start)
                        ->whereDate('transaction_date', '<=', $end);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $expenses->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for payment status for the list of expense
            if (request()->has('payment_status')) {
                $payment_status = request()->get('payment_status');
                if (!empty($payment_status)) {
                    $expenses->where('transactions.payment_status', $payment_status);
                }
            }

            $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
            if (!$is_admin && !auth()->user()->can('all_expense.access')) {
                $user_id = auth()->user()->id;
                $expenses->where(function ($query) use ($user_id) {
                        $query->where('transactions.created_by', $user_id)
                        ->orWhere('transactions.expense_for', $user_id);
                    });
            }

            // Add payment date filter
            if (!empty(request()->start_payment_date) && !empty(request()->end_payment_date)) {
                $start = request()->start_payment_date;
                $end = request()->end_payment_date;
                $expenses->whereHas('payment_lines', function($q) use ($start, $end) {
                    $q->whereDate('paid_on', '>=', $start)
                    ->whereDate('paid_on', '<=', $end);
                });
            }
            
            return Datatables::of($expenses)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false"> @lang("messages.actions")<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                        </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                    @if(auth()->user()->can("expense.edit"))
                        <li><a href="{{action(\'ExpenseController@edit\', [$id])}}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                        <li><a href="{{ action(\'ExpenseController@create\', ["d" => $id]) }}"><i class="fa fa-copy"></i> Duplicate Expense </a></li>
                    @endif
                    @if($document)
                        <li><a href="{{ url(\'uploads/documents/\' . $document)}}" 
                        download=""><i class="fa fa-download" aria-hidden="true"></i> @lang("purchase.download_document")</a></li>
                        @if(isFileImage($document))
                            <li><a href="#" data-href="{{ url(\'uploads/documents/\' . $document)}}" class="view_uploaded_document"><i class="fas fa-file-image" aria-hidden="true"></i>@lang("lang_v1.view_document")</a></li>
                        @endif
                    @endif
                    @if(auth()->user()->can("expense.delete"))
                        <li>
                        <a href="#" data-href="{{action(\'ExpenseController@destroy\', [$id])}}" class="delete_expense"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                    @endif
                    <li class="divider"></li> 
                    @if($payment_status != "paid")
                        <li><a href="{{action("TransactionPaymentController@addPayment", [$id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang("purchase.add_payment")</a></li>
                    @endif
                    <li><a href="{{action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i> @lang("purchase.view_payments")</a></li>
                    </ul></div>'
                )
                ->removeColumn('id')
                ->editColumn( 'final_total', function($row) {
                    // return json_encode($row->created_at);
                    $currency = Currency::where("id", $row->currency_id)->first();
                    $html = '<span class="display_currency final-total" data-currency_symbol="true" data-currency="'.$currency->symbol.'" data-orig-value="';
                    if($row->type=="expense_refund"){
                        $html .= -1 * $row->final_total;
                    }else{
                        $html .= $row->final_total;
                    } 
                    $html .= '">';
                    if($row->type=="expense_refund") {
                        $html .= "-";
                    }
                    $dateToCheck = $row->created_at;
                    $compareDate = "2023-09-07";
                    // $final_total =  strtotime($dateToCheck) > strtotime($compareDate) ? number_format($row->final_total / $currency->rate, 2) : number_format($row->final_total, 2);
                    // $final_total =  number_format($row->final_total / $currency->rate, 2); CBY Bilal

                    $final_total =  strtotime($dateToCheck) > strtotime($compareDate) ? number_format($row->final_total , 2) : number_format($row->final_total, 2);
                    $final_total =  number_format($row->final_total , 2);
                    
                    $html .= $currency->symbol . " " . $final_total ."</span>";
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal payment-status" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span></a>'
                )
                ->addColumn('payment_due', function ($row) {
                    $currency = Currency::where("id", $row->currency_id)->first();
                    $dateToCheck = $row->created_at;
                    $compareDate = "2023-09-07";
                    $due =  strtotime($dateToCheck) > strtotime($compareDate) ? ($row->final_total) - $row->amount_paid : $row->final_total - $row->amount_paid;
                    // $due =  strtotime($dateToCheck) > strtotime($compareDate) ? ($row->final_total / $currency->rate) - $row->amount_paid : $row->final_total - $row->amount_paid; CBY Bilal
                    // $due = ($row->final_total / $currency->rate) - $row->amount_paid;
                    if ($row->type == 'expense_refund') {
                        $due = -1 * $due;
                    }
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '" data-currency="'.$currency->symbol.'">' . $currency->symbol . " " . number_format($due, 2) . '</span>';
                })
                ->addColumn('recur_details', function($row){
                    $details = '<small>';
                    if ($row->is_recurring == 1) {
                        $type = $row->recur_interval == 1 ? Str::singular(__('lang_v1.' . $row->recur_interval_type)) : __('lang_v1.' . $row->recur_interval_type);
                        $recur_interval = $row->recur_interval . $type;
                        
                        $details .= __('lang_v1.recur_interval') . ': ' . $recur_interval; 
                        if (!empty($row->recur_repetitions)) {
                            $details .= ', ' .__('lang_v1.no_of_repetitions') . ': ' . $row->recur_repetitions; 
                        }
                        if ($row->recur_interval_type == 'months' && !empty($row->subscription_repeat_on)) {
                            $details .= '<br><small class="text-muted">' . 
                            __('lang_v1.repeat_on') . ': ' . str_ordinal($row->subscription_repeat_on) ;
                        }
                    } elseif (!empty($row->recur_parent_id)) {
                        $details .= __('lang_v1.recurred_from') . ': ' . $row->recurring_parent->ref_no;
                    }
                    $details .= '</small>';
                    return $details;
                })
                ->editColumn('ref_no', function($row){
                    $ref_no = $row->ref_no;
                    if (!empty($row->is_recurring)) {
                        $ref_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $ref_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.generated_recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if ($row->type == 'expense_refund') {
                        $ref_no .= ' &nbsp;<small class="label bg-gray">' . __('lang_v1.refund') . '</small>';
                    }

                    return $ref_no;
                })
                ->rawColumns(['final_total', 'action', 'payment_status', 'payment_due', 'ref_no', 'recur_details'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->whereNull('parent_id')
                            ->pluck('name', 'id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $contacts = Contact::contactDropdown($business_id, false, false);

        $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->whereNotNull('parent_id')
                        ->pluck('name', 'id')
                        ->toArray();

        return view('expense.index')
            ->with(compact('categories', 'business_locations', 'users', 'contacts', 'sub_categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $expense_categories = ExpenseCategory::where('business_id', $business_id)
                                ->whereNull('parent_id')
                                ->pluck('name', 'id');

        $users = User::forDropdown($business_id, true, true);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        
        $payment_line = $this->dummyPaymentLine;

        $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        //Duplicate Expense
        $duplicate_expense = null;
        $sub_categories=[];
        if (!empty(request()->input('d'))) {
            $duplicate_expense = Transaction::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_expense->ref_no .= ' (copy)';
            $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_expense->expense_category_id)
                        ->select(['name', 'id'])
                        ->pluck('name', 'id');
        }
        // dd($expense_categories,$sub_categories);

        $tax_types = [
            'percentage' => __('lang_v1.percentage'),
            'fixed' => __('lang_v1.fixed')
        ];

        if (request()->ajax()) {
            return view('expense.add_expense_modal')
                ->with(compact('expense_categories', 'business_locations', 'users', 'taxes', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts'));
        }

        return view('expense.create')
            ->with(compact('duplicate_expense','sub_categories','expense_categories', 'business_locations', 'users', 'taxes', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts', 'tax_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // dd($request->all());
            $business_id = $request->session()->get('user.business_id');
            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
            }
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            // Validate tax type
            $request->validate([
                'tax_type' => 'required|in:percentage,fixed',
                'tax_value' => 'required|numeric'
            ]);

            // Calculate tax based on type
            $tax_amount = 0;
            if ($request->tax_type === 'percentage') {
                $tax_amount = ($request->final_total * $request->tax_value) / 100;
            } else {
                $tax_amount = $request->tax_value;
            }

            // Add tax to expense
            $expense_data['tax_amount'] = $tax_amount;
            $expense_data['tax_type'] = $request->tax_type;
            $expense_data['tax_value'] = $request->tax_value;


            $business_location = BusinessLocation::where('id', $request->location_id)->first();
            $currency_details = Currency::where('id', $business_location->currency_id)->first();
            // dd($request->sub_total);
            $request['exchange_rate'] = $currency_details->rate;
            
            $user_id = $request->session()->get('user.id');
            DB::beginTransaction();
            $expense = $this->transactionUtil->createExpense($request, $business_id, $user_id);
            // dd($expense);
            if(isset($request->is_purchase) && $request->is_purchase==1){
                $expense->update(['is_purchase'=>1]);

                foreach ($request->purchases as $key => $purchaseId) {
                    ExpensePurchase::create([
                        "expense_id" => $expense->id,
                        "purchase_id" => $purchaseId,
                        "total" => $request->sub_total[$key] ,
                    ]);
                }
                
            }
            if (request()->ajax()) {
                $payments = !empty($request->input('payment')) ? $request->input('payment') : [];
                $sellPayment = $this->cashRegisterUtil->addSellPayments($expense, $payments);
            }
            // return $sellPayment;
            $this->transactionUtil->activityLog($expense, 'added');

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('expense.expense_add_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        if (request()->ajax()) {
            return $output;
        }

        return redirect('expenses')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $expense_categories = ExpenseCategory::where('business_id', $business_id)
                                ->whereNull('parent_id')
                                ->pluck('name', 'id');
        $expense = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->first();
        $purchases = ExpensePurchase::with('transaction.contact')->where('expense_id',$id)->get();
        $business_location = BusinessLocation::where('id', $expense->location_id)->first();
        $currency_details = Currency::where('id', $business_location->currency_id)->first();
        $users = User::forDropdown($business_id, true, true);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Sub-category
        $sub_categories = [];

        if (!empty($expense->expense_category_id)) {
            $sub_categories = ExpenseCategory::where('business_id', $business_id)
                        ->where('parent_id', $expense->expense_category_id)
                        ->pluck('name', 'id')
                        ->toArray();
        }

        $tax_types = [
            'percentage' => __('lang_v1.percentage'),
            'fixed' => __('lang_v1.fixed')
        ];
        
        return view('expense.edit')
            ->with(compact('expense', 'expense_categories', 'business_locations', 'users', 'taxes', 'contacts', 'sub_categories','currency_details','purchases', 'tax_types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);
            
            $business_location = BusinessLocation::where('id', $request->location_id)->first();
            $currency_details = Currency::where('id', $business_location->currency_id)->first();
            $request['exchange_rate'] = $currency_details->rate;
            $business_id = $request->session()->get('user.business_id');
            
            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('ExpenseController@index'));
            }

            $expense = $this->transactionUtil->updateExpense($request, $id, $business_id);
            // dd( $request->sub_total);
            if(isset($request->is_purchase) && $request->is_purchase==1){
                $expense->update(['is_purchase'=>1]);
                ExpensePurchase::whereNotIn('purchase_id',$request->purchases)->where('expense_id',$id)->delete();

                foreach ($request->purchases as $key => $purchaseId) {
                    $isExist = ExpensePurchase::where([
                        "expense_id" => $id,
                        "purchase_id" => $purchaseId
                    ])->first();
                    if($isExist!=null){
                        ExpensePurchase::where([
                            "expense_id" => $id,
                            "purchase_id" => $purchaseId,
                        ])->update([
                            "total" => $request->sub_total[$key] ,
                        ]);
                    }else{
                        ExpensePurchase::create([
                            "expense_id" => $id,
                            "purchase_id" => $purchaseId,
                            "total" => $request->sub_total[$key] ,
                        ]);
                    }
                   
                }
            }else{
                $expense->update(['is_purchase'=>0]);
                ExpensePurchase::where([
                    "expense_id" => $expense->id,
                ])->delete();
            }
            $this->transactionUtil->activityLog($expense, 'edited');

            $output = ['success' => 1,
                            'msg' => __('expense.expense_update_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('expenses')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('expense.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $expense = Transaction::where('business_id', $business_id)
                                        ->where(function($q) {
                                            $q->where('type', 'expense')
                                                ->orWhere('type', 'expense_refund');
                                        })
                                        ->where('id', $id)
                                        ->first();
                $expense->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $expense->id)->delete();

                $output = ['success' => true,
                            'msg' => __("expense.expense_delete_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }
    
    public function getCurrency($id) {
        $business_location = BusinessLocation::where('id', $id)->first();
        if($business_location) {
            $currency = Currency::where('id', $business_location->currency_id)->first();
            
            $business_id = request()->session()->get('user.business_id');
            $accounts = Account::forDropdown($business_id,true,false,true,$currency->id);
            return response()->json([
                'success' => true,
                'currency' => $currency,
                'accounts' => $accounts,
            ]);
        } else {
             return response()->json([
                'success' => false
            ]);
        }
    }

    public function getPurchases()
    {
      
            $term = request()->term;
            $location_id = request()->location_id;


            if (empty($term)) {
                return json_encode([]);
            }
            if (empty($location_id)) {
                return json_encode([]);
            }
            $select=[
                "id",
                "ref_no as text",
                "contact_id",
                "location_id",
                "total_before_tax",
                "tax_amount",
                "final_total",
            ];
            // $purchases= Transaction::with('contact')->select($select)->where(['type'=>'purchase','status'=>'received','location_id'=>$location_id])
            $purchases= Transaction::with('contact')->select($select)->where(['type'=>'purchase','location_id'=>$location_id])
            ->where('ref_no','LIKE','%'.$term.'%')->get();

            return $purchases;
            
            return json_encode($result);
    }
}














