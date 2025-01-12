<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','Desc')->paginate(10);
        return view('user.orders',compact('orders'));
    }

    public function order_details($order_id)
{
    $order = Order::find($order_id);
    $orderitems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
    $transaction = Transaction::where('order_id', $order_id)->first();  // Fetch the transaction

    // Ensure that the transaction exists and pass it to the view
    if ($transaction) {
        return view('admin.order-details', compact('order', 'orderitems', 'transaction'));
    }

    // Handle case if transaction is not found
    return redirect()->route('admin.orders')->with('error', 'Transaction not found for this order.');
}

        public function order_cancel(Request $request)
{
    $order = Order::find($request->order_id);
    $order->status = "canceled";
    $order->canceled_date = Carbon::now();
    $order->save();
    return back()->with("status", "Order has been cancelled successfully!");
}
}
