<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

use App\Models\Product;

use function PHPUnit\Framework\isEmpty;

class OrderController extends Controller
{
    //User : create new order
    public function createOrder(Request $request){
        $request->validate([
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'restaurant_id' => 'required|integer|exists:users,id',
            'shipping_cost' => 'required|integer',
        ]);
    
        if (!is_array($request->order_items)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid order items format'
            ], 400);
        }
    
        $totalPrice = 0;
    
        foreach($request->order_items as $item){
            $product = Product::find($item['product_id']);
            $totalPrice += $product->price * $item['quantity'];
        }
    
        $totalBill = $totalPrice + $request->shipping_cost;
    
        $user = $request->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $shippingAddress = $user->address;
        $data['shipping_address'] = $shippingAddress;
        $shippingLatlong = $user->latlong;
        $data['shipping_latlong'] = $shippingLatlong;
        $data['status'] = 'pending';
        $data['total_bill'] = $totalBill;
        $data['total_price'] = $totalPrice; 
    
        $order = Order::create($data);
    
        foreach ($request->order_items as $item){
            $product = Product::find($item['product_id']);
            $orderItem = new OrderItem([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
    
            $order->orderItems()->save($orderItem);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ]);
    }
    
    


    // update purchase status
    public function updatePurchaseStatus (Request $request, $id){
        $request->validate([
            'status' => 'required|string|in:pending,processing,complated',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'massage' => 'Order status updated successfully',
            'data' => $order,
        ]);
    }

    // order history
    public function orderHistory (Request $request){
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json([
            'status' => 'success',
            'massage' => 'get all order history',
            'data' => $orders,
        ]);
    }

      // cancel order 
      public function cancelOrder (Request $request, $id){
        $order = Order::find($id);
        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'status' => 'success',
            'massage' => 'Order cancelled successfully',
            'data' => $order,
        ]);
    }

    // get orders by status for restaurant
    public function getOrderByStatus(Request $request){
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);
    
        $user = $request->user();
        $orders = Order::where('restaurant_id', $user->id)
                       ->where('status', $request->status)
                       ->get();
    
        if ($orders->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No orders found with the specified status',
                'data' => [],
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'get all orders by status',
            'data' => $orders,
        ]);
    }
    
    
    

    // update order status for restaurant
    public function updateOrderStatus (Request $request, $id){
        $request->validate([
            'status' => 'required|string|in:pending,processing,complated,cancelled,ready_for_delivey,prepared',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'massage' => 'order status updated successfully',
            'data' => $order,
        ]);
    }

    // get order by status for driver
    public function getOrderByStatusDriver (Request $request){
        $request->validate([
            'status' => 'required|string|in:pending,processing,complated,cancelled,on_the_way,delivered',
        ]);

        $user = $request->user();
        $order = Order::where('driver_id', $user->id)
        ->where('status', $request->status)
        ->get();

        if($order->isEmpty()){
            return response()->json([
                'status' => 'success',
                'massage' => 'No orders found with the specified status',
                'data' => [],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'massage' => 'get all orders by status',
            'data' => $order,
        ]);
    }

    // get order status ready for delivery
    public function getOrderStatusReadyForDelivery (Request $request){

        $orders = Order::with('restaurant')->where('status', 'ready_for_delivery')->get();
        return response()->json([
            'status' => 'success',
            'massage' => 'get all orders by status ready for delivery',
            'data' => $orders,
        ]);
    }

    // update order status for driver
    public function updateOrderStatusDriver (Request $request, $id){
        $request->validate([
            'status' => 'required|string|in:pending,processing,complated,cancelled,on_the_way,delivered',
        ]);

        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'massage' => 'order status updated successfully',
            'data' => $order,
        ]);
    }
}
