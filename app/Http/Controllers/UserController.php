<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderItem;
use App\Models\Transaction;


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
    $order = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();

    // If no order is found, you can handle this gracefully
    if (!$order) {
        return redirect()->route('orders.index')->with('error', 'Order not found.');
    }

    // Next, retrieve the order items related to this order.
    // This is the part where you get all the items for the order.
    $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);

    // Retrieve the transaction for this order
    $transaction = Transaction::where('order_id', $order_id)->first();

    // Now, return the view and pass the variables to the view using compact
    return view('user.order-details', compact('order', 'orderItems', 'transaction'));
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
