<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Model\Payment\Currency;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $currency;
    protected $price;
    protected $period;
    protected $product;

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('admin');
        $plan = new Plan();
        $this->plan = $plan;
        $subscription = new Subscription();
        $this->subscription = $subscription;
        $currency = new Currency();
        $this->currency = $currency;
        $price = new PlanPrice();
        $this->price = $price;
        $period = new Period();
        $this->period = $period;
        $product = new Product();
        $this->product = $product;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        $currency = $this->currency->pluck('name', 'code')->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();

        return view('themes.default1.product.plan.index', compact('currency', 'periods', 'products'));
    }

    /**
     * Get plans for chumper datatable.
     */
    public function getPlans()
    {
        $new_plan = Plan::select('id', 'name', 'days', 'product')->get();

        return\ DataTables::of($new_plan)
                        ->addColumn('checkbox', function ($model) {
                            return "<input type='checkbox' class='plan_checkbox' value=".$model->id.' name=select[] id=check>';
                        })
                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        ->addColumn('days', function ($model) {
                            $months = $model->days / 30;

                            return round($months);
                        })
                        ->addColumn('product', function ($model) {
                            $productid = $model->product;
                            $product = $this->product->where('id', $productid)->first();
                            $response = '';
                            if ($product) {
                                $response = $product->name;
                            }

                            return ucfirst($response);
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('plans/'.$model->id.'/edit')." class='btn btn-sm btn-primary btn-xs'><i class='fa fa-edit' style='color:white;'> </i>&nbsp;&nbsp;Edit</a>";
                        })
                        ->rawColumns(['checkbox', 'name', 'days', 'product', 'action'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $currency = $this->currency->pluck('name', 'code')->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();

        return view('themes.default1.product.plan.create', compact('currency', 'periods', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'        => 'required',
            'days'        => 'required|numeric',
            'add_price.*' => 'required',
            'product'     => 'required',
        ]);

        $this->plan->fill($request->input())->save();
        $add_prices = $request->input('add_price');
        $renew_prices = $request->input('renew_price');
        $product = $request->input('product');
        if (count($add_prices) > 0) {
            foreach ($add_prices as $key => $price) {
                $renew_price = '';
                if (array_key_exists($key, $renew_prices)) {
                    $renew_price = $renew_prices[$key];
                }
                $this->price->create([
                    'plan_id'     => $this->plan->id,
                    'currency'    => $key,
                    'add_price'   => $price,
                    'renew_price' => $renew_price,
                    'product'     => $product,
                ]);
            }
        }

        return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $plan = $this->plan->where('id', $id)->first();
        $currency = $this->currency->pluck('name', 'code')->toArray();
        $add_price = $this->price->where('plan_id', $id)->pluck('add_price', 'currency')->toArray();
        $renew_price = $this->price->where('plan_id', $id)->pluck('renew_price', 'currency')->toArray();
        $periods = $this->period->pluck('name', 'days')->toArray();
        $products = $this->product->pluck('name', 'id')->toArray();

        return view('themes.default1.product.plan.edit', compact('plan', 'currency', 'add_price', 'renew_price', 'periods', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name'        => 'required',
            'add_price.*' => 'required',
            'product'     => 'required',
        ]);
        $plan = $this->plan->where('id', $id)->first();
        $plan->fill($request->input())->save();
        $add_prices = $request->input('add_price');
        $renew_prices = $request->input('renew_price');
        $product = $request->input('product');
        $period = $request->input('days');

        if (count($add_prices) > 0) {
            $price = $this->price->where('plan_id', $id)->get();

            if (count($price) > 0) {
                foreach ($price as $delete) {
                    $delete->delete();
                }
            }
            foreach ($add_prices as $key => $price) {
                $renew_price = '';
                if (array_key_exists($key, $renew_prices)) {
                    $renew_price = $renew_prices[$key];
                }
                $this->price->create([
                    'plan_id'     => $plan->id,
                    'currency'    => $key,
                    'add_price'   => $price,
                    'renew_price' => $renew_price,
                    'product'     => $product,
                ]);
            }
        }

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('select');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $plan = $this->plan->where('id', $id)->first();
                if ($plan) {
                    $plan->delete();
                } else {
                    echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                    //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                }
            }
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
        } else {
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".\Lang::get('message.alert').'!</b> '.\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            //echo \Lang::get('message.select-a-row');
        }
    }
}
