<?php

use App\Events\DefaultEmailEvent;
use App\Events\PlaceOrderEmailEvent;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function authUser()
{
    return DB::table('users')->where('users.id', \auth()->id())
        ->join('wallets', 'users.id', '=', 'wallets.user_id')
        ->select('users.id', 'users.full_name', 'users.phone', 'users.type', 'users.status', 'wallets.balance')
        ->first();
}
/**
 * Building a function upload single image
 */
function uploadImage($image, $path, $existing_image_path = null, $height = null, $width = null, $extension = 'webp')
{
    // Delete existing image if the path is provided and the file exists
    if (!empty($existing_image_path) && Illuminate\Support\Facades\File::exists(public_path().'/'.$existing_image_path)) {
        Illuminate\Support\Facades\File::delete(public_path().'/'.$existing_image_path);
    }

    // Get the original image name without the extension
    $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    // Create a new image name with the original name, current timestamp, and desired extension
    $image_name = $originalName.'-'.time().'.'.$extension;

    // Process the image
    $query = Image::make($image->getRealPath())->encode($extension, 60);
    if (!empty($height) && !empty($width)) {
        $query->resize($width, $height);
    }

    // Save the processed image to the specified path
    $query->save(public_path($path.$image_name));

    // Return the path of the uploaded image
    return $path.$image_name;
}

/**
 * Building a function upload single image
 */
function uploadImageWithOriginal($image, $path, $existing_image_path = null)
{
    if (!empty($existing_image_path) && Illuminate\Support\Facades\File::exists(public_path().'/'.$existing_image_path)) {
        Illuminate\Support\Facades\File::delete(public_path().'/'.$existing_image_path);
    }
    $image_name = time().'-'.$image->getClientOriginalName();
    $image->move(public_path($path), $image_name);

    return $path.$image_name;
}

/**
 * Delete image from storage
 */
function deleteImage($existing_image_path = null)
{
    $return_value = false;
    if (!empty($existing_image_path) && Illuminate\Support\Facades\File::exists(public_path().'/'.$existing_image_path)) {
        $return_value = Illuminate\Support\Facades\File::delete(public_path().'/'.$existing_image_path);
    }
    return $return_value;
}

/**
 * Return settings by key
 * @param $option
 * @return string
 */
function get_settings($option)
{
    $setting = DB::table('settings')->where('option', $option)->first();

    if (isset($setting->value)) {
        return $setting->value;
    }else {
        return '';
    }
}

/**
 * Return all active currencies
 * @return Collection
 */
function getAllActiveCurrencies()
{
    return DB::table('currencies')->where('status', 1)->select('id', 'symbol')->get();
}

/**
 * Return attributes
 * @return mixed
 */
function getAttributes()
{
    return \App\Models\Attribute::where('status', 1)->select('id', 'name', 'slug')->orderBy('name')->get();
}

/**
 * Generate combination of attribute item id
 */
function generateAllArrayCombinations($arrays) {
    $result = [[]];
    foreach ($arrays as $key => $array) {
        $append = [];
        foreach ($result as $item) {
            foreach ($array as $element) {
                $append[] = array_merge($item, [$element]);
            }
        }
        $result = $append;
    }

    return $result;
}


/**
 * Get common settings at once
 * @return Collection
 */
function commonSettings()
{
    return DB::table('settings')
        ->where('option', 'app_name')
        ->orWhere('option', 'address')
        ->orWhere('option', 'phone')
        ->orWhere('option', 'email')
        ->orWhere('option', 'logo')
        ->orWhere('option', 'favicon')
        ->orWhere('option', 'facebook_link')
        ->orWhere('option', 'linkedin_link')
        ->orWhere('option', 'instagram_link')
        ->orWhere('option', 'youtube_link')
        ->orWhere('option', 'meta_description')
        ->orWhere('option', 'og_image')
        ->orWhere('option', 'favicon')
        ->orWhere('option', 'footer_image')
        ->orWhere('option', 'currency_symbol')
        ->pluck('value','option');
}

/**
 * Return all active ctegories
 */
function allActiveCategories()
{
    return Category::where('status', 1)->whereHas('products')
        ->select('id','name', 'slug')->get();
}

/**
 * Return cart quantity
 */
function getCartQty()
{
    $user_id = auth()->id();
    if (!empty($user_id)) {
        $total_qty = DB::table('carts')->where('user_id', $user_id)->sum('quantity');
    }else {
        $total_qty = DB::table('carts')->where('ip', session('ip'))->sum('quantity');
    }
    return $total_qty;
}

function emailAndNotificationForOrder($order_id, $request, $user = null)
{
    $mail_data['order'] = Order::with([
        'customer:id,full_name,phone,email,address,city,post_code,country',
        'shipping:id,order_id,full_name,email,phone,address,city,post_code,country',
        'billing:id,order_id,full_name,email,phone,address,city,post_code,country',
        'shippingMethod:id,name,deliver_in',
        'details' => function ($query) {
            $query->with([
                'product:id,name,code',
                'variationWithAttributes:id'
            ])->select('id', 'order_id', 'product_id', 'variation_id', 'quantity', 'sale_price', 'vat_percentage', 'vat_amount', 'sub_total');
        },
        'payment:id,order_id,total_amount,paid_amount,due_amount,vat_amount,coupon_discount,shipping_charge,full_free_shipping,status'
    ])->join('billings', 'billings.order_id', '=', 'orders.id')
        ->where('orders.id', $order_id)
        ->select('orders.id', 'orders.user_id', 'orders.order_number', 'orders.payment_method', 'orders.shipping_method_id',
            'orders.order_total', 'orders.ship_date', 'orders.delivery_date', 'orders.status',
            DB::raw('DATE_FORMAT(orders.created_at,"%d %M, %Y") AS order_date'), 'orders.order_note'
        )->first();

    $settings = DB::table('settings')->whereIn('option', ['currency_symbol', 'app_name', 'address'])
        ->select('option', 'value')->get();

    $mail_data['app_name'] = $settings->firstWhere('option', 'app_name')->value ?? config('app.name');
    $mail_data['currency'] = $settings->firstWhere('option', 'currency_symbol')->value ?? '$';
    $mail_data['address'] = $settings->firstWhere('option', 'address')->value ?? '';

    if (empty($user)) {
        $mail_data['route_value'] = route('customer.public-order-track', ['billing_email' => $request['b_email'], 'order_number' => $mail_data['order']['order_number']]);
    }else {
        $mail_data['route_value'] = route('customer.orders.track', $mail_data['order']->order_number);
    }

    $mail_data['to_email'] = $user->email ?? $request['b_email'];
    $mail_data['receiver_name'] = $user->full_name ?? $request['b_full_name'];
    $mail_data['subject'] = 'Successfully place order';
    $mail_data['body'] = 'We received an order from you. Your order track number is - '.$mail_data['order']['order_number'].'.
    You can see order track details by log in to your '.$mail_data['app_name'].' customer dashboard or order track link - '.
        '<a target="_black" href="'.$mail_data['route_value'].'">click here</a>'.' & we are attaching your order invoice here';

    event(new PlaceOrderEmailEvent($mail_data));
//    return view('mail.place-order-email', compact('mail_data'));
    /** Send notification to admin */
    $admin = DB::table('users')->where('type', 'administrator')->first('id');
    $notification = [
        'to_user_type' => 'admin',
        'to_user_id' => $admin->id ?? '',
        'type' => 'place-order',
        'subject_id' => $mail_data['order']['id'],
        'title' => 'New order placed',
        'message' => 'A new order placed by customer - '.$mail_data['receiver_name'].', order number - #'.$mail_data['order']['order_number'].', please take farther action for this order.',
        'action_route' => ['route' => 'backend.orders.show', 'route_value'=> $mail_data['order']['id'], 'name' => 'order'],
        'created_by_user_type' => 'customer',
        'created_by_id' => $user->id ?? '',
    ];
    Notification::create($notification);
}

function welcomeEmailForCustomer($data)
{
    $app_name = get_settings('app_name') ?? config('app.name');
    $route_value = route('login');
    $mail_data = [
    'to_email' => $data['email'],
    'receiver_name' => $data['full_name'],
    'subject' => 'Welcome to '.$app_name,
    'cc_array' => null,
    'attachment_paths' => null
    ];
    $mail_data['body'] = '
    <style>
        .content-body {
            padding: 0 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        .content-body h1 {
            color: #333333;
        }
        .content-body p {
            margin-bottom: 20px;
        }
        .content-body .button {
            display: inline-block;
            padding: 5px 40px;
            font-size: 14px;
            color: #ffffff;
            background-color: #3c454b;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
    <div class="content-body">
        <p>Thank you for joining ' .$app_name.'. We are thrilled to have you on board. Our platform is designed to provide you with the best experience possible.</p>
        <p>To get started, please sign in using your credentials. You can access your account dashboard by clicking the button below:</p>
        <p><a href="'.$route_value.'" target="_blank" class="button">Sign In</a></p>
        <p>If you have any questions or need assistance, please do not hesitate to contact our support team.</p>
        <p>We look forward to serving you.</p>
    </div>
    ';
    event(new DefaultEmailEvent($mail_data));
}

function notifications()
{
    $data['notifications'] = \Illuminate\Support\Facades\DB::table('notifications')->where('to_user_type', 'admin')
        ->select('id','title','message','action_route', 'read_at',
            DB::raw('DATE_FORMAT(created_at,"%d %M, %Y %h:%i %p") AS created_at_time')
        )->orderBy('id', 'desc')->take(15)->get();

    $data['unread_notification_count'] = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('to_user_type', 'admin')->where('read_at', null)->count('id');

    return $data;
}

/**
 * Populate cart data in cart page
*/
function populateCartDataWithHtml($carts, $coupon_code = null)
{
    $sub_total = 0;
    $vat_amount = 0;
    $currency = '$';
    if (!empty($carts) && count($carts) > 0) {
        $rows = '';
        $currency = get_settings('currency_symbol');
        foreach ($carts as $cart) {
            /** Image calculation */
            $product_image = isset($cart->product->main_image) ? asset($cart->product->main_image) : asset('blank-thumbnail.jpg');
            if (isset($cart->variation->galleryImage->image)) {
                $product_image = isset($cart->variation->galleryImage->image) ? asset($cart->variation->galleryImage->image) : asset('blank-thumbnail.jpg');
            }
            $product_name = $cart->product->name ?? 'N/A';

            /** Price calculation */
            $price = $cart->variation->sell_price ?? ($cart->product->inventory->sell_price ?? 0);
            // Multi-buying calculation
            $multi_buying_items = $cart->product->multiBuying ?? [];
            if (count($multi_buying_items) > 0) {
                $multi_buying_items = $multi_buying_items->sortByDesc('qty')->where('qty', '<=', $cart['quantity'])->first(); // get only those items where quantity under cart quantity
                if (isset($multi_buying_items->qty) && $cart['quantity'] >= $multi_buying_items->qty) {
                    $price = $multi_buying_items->sell_price;
                }
            }
            $price_element = '<span>'.$currency.$price.'</span>';
            if ($cart->product->inventory->unit_price > $cart->product->inventory->sell_price && !isset($cart->variation->sell_price)) {
                $price_element .= '<br><span class="text-decoration-line-through text-muted">'.$currency.$cart->product->inventory->unit_price.'</span>';
            }
            if (isset($cart->variation->sell_price) && $cart->variation->unit_price > $cart->variation->sell_price) {
                $price_element .= '<br><span class="text-decoration-line-through text-muted">'.$currency.$cart->variation->unit_price.'</span>';
            }

            /** Start variation element */
            $variation_details = '';
            $variation_element = '';
            if (isset($cart->variation->variationItems) && !empty($cart->variation->variationItems)) {
                foreach ($cart->variation->variationItems as $key => $variation_item) {
                    if ($key > 0) {
                        $variation_details .= ', ';
                    }
                    $variation_details .= $variation_item->attribute.' - '.$variation_item->attribute_item;
                }
            }
            if (!empty($variation_details)) {
                $variation_element = '<p>'.$variation_details.'</p>';
            }

            $vat_percentage = $cart->product->inventory->vat_percentage ?? 0;
            $this_product_vat_amount = ($price * $cart->quantity/100) * $vat_percentage;
            $vat_amount += $this_product_vat_amount;

            /** Start for order summary calculation */
            $sub_total += $price * $cart->quantity;
            /** End for order summary calculation */

            /** Product details page link */
            $product_details_route = route('product-details', [$cart->product->slug]);
            if (!empty($cart->variation)) {
                $product_details_route = route('product-details', [$cart->product->slug, 'svi' => $cart->variation->id, 'svd' => $variation_details]);
            }

            $rows .= '<tr>
                       <td class="product-thumbnail">
                            <a href="'.$product_details_route.'">
                                <img src="'.$product_image.'" alt="Image" class="img-fluid">
                           </a>
                       </td>
                       <td class="product-name">
                            <a href="'.$product_details_route.'" class="text-decoration-none">
                               <h2 class="h5 text-black">'.$product_name.'</h2>
                               '.$variation_element.'
                           </a>
                       </td>
                       <td>'.$price_element.'</td>
                       <td>
                           <div class="input-group mb-3 d-flex align-items-center quantity-container" style="max-width: 120px;">
                               <div class="input-group-prepend">
                                   <button class="btn btn-outline-black decrease" type="button" onclick="minusBtnOnclick('.$cart->product->max_buying_qty.', '.$cart->id.')">&minus;</button>
                               </div>
                               <input type="text" class="form-control text-center quantity-amount" id="cart_buying_qty'.$cart->id.'" value="'.$cart->quantity.'" placeholder="" aria-label="Example text with button addon" aria-describedby="button-addon1">
                               <div class="input-group-append">
                                   <button class="btn btn-outline-black increase" type="button" onclick="plusBtnOnclick('.$cart->product->max_buying_qty.', '.$cart->id.')">&plus;</button>
                               </div>
                           </div>
                       </td>
                       <td>'.number_format($vat_percentage , 2, '.', '').'%</td>
                       <td>'.$currency. number_format((float)$price * $cart->quantity , 2, '.', '').'</td>
                       <td><a href="#" class="btn btn-black btn-sm" onclick="deleteCart('.$cart->id.')">X</a></td>
                    </tr>';
        }
        $data['carts'] = '<table class="table">
                       <thead>
                            <tr>
                                <th class="product-thumbnail">Image</th>
                                <th class="product-name">Product</th>
                                <th class="product-price">Price</th>
                                <th class="product-quantity">Quantity</th>
                                <th class="product-quantity">Vat %</th>
                                <th class="product-total">Total</th>
                                <th class="product-remove">Remove</th>
                            </tr>
                       </thead>
                       <tbody>'.$rows.'</tbody>
                  </table>';
    }else{
        $data['carts'] = '<table class="table">
                       <thead>
                            <tr>
                                <th class="product-thumbnail">Cart empty</th>
                            </tr>
                       </thead>
                       <tbody>
                            <tr></tr>
                       </tbody>
                  </table>';
    }

    /** Order summary code */
    $data['order_summary']['sub_total'] = $sub_total;
    $data['order_summary']['coupon_discount'] = 0;
    $data['order_summary']['coupon_code'] = null;
    $data['status'] = true;
    if (!empty($coupon_code)) {
        $data = getCouponDiscount($sub_total, $coupon_code, $data);
    }
    $data['order_summary']['vat_amount'] = $vat_amount;
    $data['order_summary']['total'] = $sub_total + $vat_amount - $data['order_summary']['coupon_discount'];
    $data['order_summary']['currency'] = $currency;

    return $data;
}

function getCouponDiscount($sub_total, $coupon_code, $data, $user_phone = null)
{
    $now = Carbon::now()->toDateTimeString();

    /** Apply promo codes on total amount / if user auth / if promo code not expired */
    $coupon = DB::table('coupons')->where('coupons.code', $coupon_code)->where('coupons.status', 1)
        ->where('coupons.active_date', '<=', $now)
        ->where('coupons.expire_date', '>=', $now)
        ->select( 'min_order_amount', 'total_order_limit', 'per_user_limit', 'discount_type', 'discount_amount')
        ->first();

    /**
     * Condition note
     * 0. Check coupon exists or not
     * 2. Min order amount must be grater then order sub-total
     * 3. Check total order limit
     * 4. Per user order limit check
     */
    if(!$coupon) {
        $data['status'] = false;
        $data['message'] = 'Coupon does\'t exists!';
        return $data;
    }
    if(isset($coupon->min_order_amount) && $sub_total < $coupon->min_order_amount) {
        $data['status'] = false;
        $data['message'] = 'Minimum order amount is '. $coupon->min_order_amount;
        return $data;
    }
    $total_ordered_for_this_coupon = DB::table('customer_coupon_infos')->where('code', $coupon_code)->count('id');
    if(isset($coupon->total_order_limit) && ($coupon->total_order_limit <= 0 || $total_ordered_for_this_coupon >= $coupon->total_order_limit)) {
        $data['status'] = false;
        $data['message'] = 'Coupon order limit exceeded';
        return $data;
    }

    /** Start Check user availability for this coupon - 1. no auth user 2. auth user */
    $user_id = auth()->id();
    if (empty($user_id)) {
        $query = DB::table('customer_coupon_infos')->where('code', $coupon_code);
        if (empty($user_phone)) {
            $query->where('ip', session('ip'));
        }
        if (!empty($user_phone)) {
            $query->where('phone', $user_phone);
        }
        $customer_coupon_used = $query->count('id');
    }
    if (!empty($user_id)) {
        $customer_coupon_used = DB::table('customer_coupon_infos')->where('user_id', $user_id)
            ->where('code', $coupon_code)->count('id');
    }
    if(isset($coupon->per_user_limit) && $customer_coupon_used >= $coupon->per_user_limit) {
        $data['status'] = false;
        $data['message'] = 'Coupon user limit exceeded';
        return $data;
    }
    /** End Check user availability for this coupon - 1. no auth user 2. auth user */

    /** Discount amount calculate */
    if (isset($coupon->discount_type) && isset($coupon->discount_amount)) {
        if($coupon->discount_type == 0) {
            $data['order_summary']['coupon_discount'] = $coupon->discount_amount;
        }
        if($coupon->discount_type == 1) {
            $data['order_summary']['coupon_discount'] = (float) sprintf('%0.2f', $sub_total * ($coupon->discount_amount / 100));
        }
        $data['order_summary']['coupon_code'] = $coupon_code;
        $data['status'] = true;
        $data['message'] = 'Coupon applied';
    }

    return $data;
}


/**
 * Checkout page - order summary
 */
function getOrderSummary($carts, $coupon_code = null, $selected_shipping_id = null, $apply_wallet_amount = null)
{
//    dd($selected_shipping_id);
    $sub_total = 0;
    $vat_amount = 0;
    $data['shipping_elements'] = null;
    $currency = get_settings('currency_symbol') ?? '$';
    $data['order_summary']['currency'] = $currency;
    $data['order_summary']['shipping_method_charge'] = 0;
    $data['order_summary']['shipping_charge'] = 0;
    $data['order_summary']['shipping_method_id'] = null;
    if (!empty($carts) && count($carts) > 0) {
        $rows = '';
        foreach ($carts as $cart) {
            $product_name = $cart->product->name ?? 'N/A';

            /** Price calculation */
            $price = $cart->variation->sell_price ?? ($cart->product->inventory->sell_price ?? 0);
            // Multi-buying calculation
            $multi_buying_items = $cart->product->multiBuying ?? [];
            if (count($multi_buying_items) > 0) {
                $multi_buying_items = $multi_buying_items->sortByDesc('qty')->where('qty', '<=', $cart['quantity'])->first(); // get only those items where quantity under cart quantity
                if (isset($multi_buying_items->qty) && $cart['quantity'] >= $multi_buying_items->qty) {
                    $price = $multi_buying_items->sell_price;
                }
            }

            /** Start variation element */
            $variation_details = '';
            $variation_element = '';
            if (isset($cart->variation->variationItems) && !empty($cart->variation->variationItems)) {
                foreach ($cart->variation->variationItems as $key => $variation_item) {
                    if ($key > 0) {
                        $variation_details .= ', ';
                    }
                    $variation_details .= $variation_item->attribute.' - '.$variation_item->attribute_item;
                }
            }
            if (!empty($variation_details)) {
                $variation_element = '<br><span>'.$variation_details.'</span>';
            }

            $vat_percentage = $cart->product->inventory->vat_percentage ?? 0;
            $this_product_vat_amount = ($price * $cart->quantity/100) * $vat_percentage;
            $vat_amount += $this_product_vat_amount;

            /** Start for order summary calculation */
            $sub_total += $price * $cart->quantity;
            /** End for order summary calculation */

            $rows .= '<tr>
                         <td>
                         '.$product_name.' <strong class="mx-2">x</strong> '.$cart->quantity.'
                         '.$variation_element.' (VAT '.number_format($vat_percentage, 2, '.', ',').'%)
                         </td>
                         <td class="text-end">'.$currency. number_format((float)$price * $cart->quantity , 2, '.', '').'</td>
                      </tr>';
        }
        $data['summary'] = '<table class="table site-block-order-table mb-5">
                                <thead>
                                    <th>Product</th>
                                    <th class="text-end">Total</th>
                                </thead>
                                <tbody>
                                    '.$rows.'
                                    <tr>
                                        <td class="text-black">Cart Subtotal</td>
                                        <td class="text-black text-end" id="order_summary_sub_total">$0.00</td>
                                    </tr>
                                    <tr class="d-none" id="order_summary_coupon_container">
                                        <td class="text-black">Coupon Discount</td>
                                        <td class="text-black text-end" id="order_summary_coupon_discount">$0.00</td>
                                    </tr>
                                    <tr id="order_summary_coupon_container">
                                        <td class="text-black" id="order_summary_shipping_charge_title">Shipping Charge</td>
                                        <td class="text-black text-end" id="order_summary_shipping_charge">$0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-black">VAT</td>
                                        <td class="text-black text-end" id="order_summary_vat_amount">$0.00</td>
                                    </tr>
                                    <tr class="d-none" id="order_summary_wallet_container">
                                        <td class="text-black">Wallet Amount</td>
                                        <td class="text-black text-end" id="order_summary_wallet_amount">$0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-black font-weight-bold"><strong>Order Total</strong></td>
                                        <td class="text-black font-weight-bold text-end"><strong id="order_summary_total">$0.00</strong></td>
                                    </tr>
                                </tbody>
                            </table>';
    }else{
        $data['summary'] = '<table class="table">
                       <thead>
                            <tr>
                                <th class="product-thumbnail">No data found! <a href="'.route('products').'" >Continue Shopping</a></th>
                            </tr>
                       </thead>
                       <tbody>
                            <tr></tr>
                       </tbody>
                  </table>';
    }

    /** Order summary code */
    $data['order_summary']['sub_total'] = $sub_total;
    $data['order_summary']['vat_amount'] = $vat_amount;
    $data['order_summary']['coupon_discount'] = 0;
    $data['order_summary']['coupon_code'] = null;
    $data['status'] = true;
    if (!empty($coupon_code)) {
        $data = getCouponDiscount($sub_total, $coupon_code, $data);
    }
    /** Shipping charge calculation for selected one */
    $data = shippingChargeCalculate($data, $carts, $currency, $selected_shipping_id);

    $data['order_summary']['total'] = $sub_total + $vat_amount - $data['order_summary']['coupon_discount'] + $data['order_summary']['shipping_charge'];

    $wallet_amount = 0;
    if (!empty(\auth()->id()) && $apply_wallet_amount > 0) {
        $data = applyWalletCredit($data, $apply_wallet_amount);
        $wallet_amount = $data['order_summary']['applied_wallet_amount'] ?? 0;
    }

    $data['order_summary']['total'] = $data['order_summary']['total'] - $wallet_amount;

    return $data;
}

function shippingChargeCalculate($data, $carts, $currency, $selected_shipping_id = null)
{
    $weight_charge = 0;
    $data['order_summary']['is_all_product_free_shipping']  = true;
    $data['order_summary']['free_shipping_products_weight_charge'] = 0;
    $data['order_summary']['full_free_shipping'] = false;
    $free_shipping_over = get_settings('free_shipping_over');

    /** Weight Charge Calculation */
    foreach ($carts as $cart) {
        if (isset($cart->product->shippingWeight->amount) && !empty($cart->product->shippingWeight->amount)) {
            $weight_charge += $cart->product->shippingWeight->amount * $cart->quantity;
            if ($cart->product->free_shipping) { // calculate what weight amount should be free.
                $data['order_summary']['free_shipping_products_weight_charge'] += $cart->product->shippingWeight->amount * $cart->quantity;
            }
            if (!$cart->product->free_shipping) {
                $data['order_summary']['is_all_product_free_shipping'] = false;
            }
        }
    }

    /** Method Charge Calculation */
    $shipping_methods = DB::table('shipping_methods')->select('id', 'name', 'deliver_in', 'charge')->orderBy('charge')->get();
    foreach ($shipping_methods as $key => $shipping_method) {
        $checked = '';
        $method_charge = $shipping_method->charge;
        if ($key == 0 && !$selected_shipping_id) {
            $checked = 'checked';
            $data['order_summary']['shipping_method_charge'] = $method_charge;
            $data['order_summary']['shipping_charge'] = $method_charge + $weight_charge;
            $data['order_summary']['shipping_method_id'] = $shipping_method->id;
        }
        if ($selected_shipping_id && $selected_shipping_id == $shipping_method->id) {
            $checked = 'checked';
            $data['order_summary']['shipping_method_charge'] = $method_charge;
            $data['order_summary']['shipping_charge'] = $method_charge + $weight_charge;
            $data['order_summary']['shipping_method_id'] = $shipping_method->id;
        }
        $data['shipping_elements'] .= '<div class="form-check mb-2">
                                    <label>
                                        <input class="form-check-input mt-2 shipping_method_item" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="'.$shipping_method->id.'" '.$checked.' onclick="selectThisShippingMethod('.$shipping_method->id.')">
                                        <span>'.$shipping_method->name.'</span><br> Charge <span>'.$currency.$method_charge + $weight_charge.'</span><br> Estimated delivery Time: <span>'.$shipping_method->deliver_in.'</span>
                                    </label>
                                </div>';
    }

    /** if only one product in cart and witch in free shipping product */
    if ($data['order_summary']['is_all_product_free_shipping']) {
        $data['order_summary']['shipping_charge'] = 0;
    }

    /** if free shipping product and no free shipping product both in cart */
    if (!$data['order_summary']['is_all_product_free_shipping'] && $data['order_summary']['free_shipping_products_weight_charge'] > 0) {
        $data['order_summary']['shipping_charge'] = $data['order_summary']['shipping_charge'] - $data['order_summary']['free_shipping_products_weight_charge'];
    }

    /** if sub-total is greater than settings free shipping over */
    if ($data['order_summary']['sub_total'] >= $free_shipping_over) {
        $data['order_summary']['full_free_shipping'] = true;
        $data['order_summary']['shipping_charge'] = 0;
    }

    return $data;
}

function applyWalletCredit($data, $apply_wallet_amount)
{
    if ($apply_wallet_amount > $data['order_summary']['total']) {
        $data['status'] = false;
        $data['message'] = 'Wallet amount can not be greater than the order subtotal!';
        return $data;
    }
    $wallet = DB::table('wallets')->where('user_id', auth()->id())->where('status', 1)->first('balance');
    if(!$wallet) {
        $data['status'] = false;
        $data['message'] = 'Wallet does\'t exists!';
        return $data;
    }
    if($wallet->balance < $apply_wallet_amount) {
        $data['status'] = false;
        $data['message'] = 'Wallet amount can not be greater than balance!';
        return $data;
    }
    if($wallet->balance < $apply_wallet_amount) {
        $data['status'] = false;
        $data['message'] = 'Amount can not be greater than balance!';
        return $data;
    }
    $data['order_summary']['applied_wallet_amount'] = $apply_wallet_amount;
    return $data;
}

function getCartsForOrder($user)
{
    $query = Cart::with([
        'product:id,shipping_weight_id',
        'product.shippingWeight:id,amount',
        'product.inventory:id,product_id,unit_price,sell_price,stock_qty,vat_percentage',
        'product.multiBuying:id,product_id,qty,sell_price',
        'variation' => function ($q) {
            $q->select('id', 'stock_qty', 'unit_price', 'sell_price');
        },
    ]);
    /** User auth checked */
    if (!empty($user->id)) {
        $query->where('user_id', $user->id);
    }
    if (empty($user->id)) {
        $query->where('user_id', null)->where('ip', session('ip'));
    }
    $carts = $query->select('id', 'product_id', 'variation_id', 'quantity')->get();

    /** Stock check when cart refresh and cart delete if stock out */
    if (!empty($carts)) {
        foreach ($carts as $key => $cart) {
            if (isset($cart->variation->stock_qty) && $cart->variation->stock_qty <= 0) {
                $cart->delete();
                $carts->forget($key);
            }
            if (!empty($cart) && isset($cart->product->inventory->stock_qty) && $cart->product->inventory->stock_qty <= 0) {
                $cart->delete();
                $carts->forget($key);
            }
        }
    }

    return $carts;
}

function createCustomerAccount($request)
{
    $user = User::where('email', $request['b_email'])->orWhere('phone', $request['b_phone'])->first();
    if (!$user) {
        $data = [
            'full_name' => $request['b_full_name'],
            'email' => $request['b_email'],
            'phone' => $request['b_phone'],
            'address' => $request['b_address'],
            'password' => Hash::make($request['account_password']),
            'status' => 1,
            'type' => 'customer',
            'created_at' => now(),
        ];
        $user_id = DB::table('users')->insertGetId($data);
        $user = User::where('id', $user_id)->first();
    }

    // for this order: clear these users old carts and only transfer ip cart to user_id
    DB::table('carts')->where('user_id', $user->id)->delete();
    DB::table('carts')->where('user_id', null)->where('ip', session('ip'))
        ->update(['user_id' => $user->id, 'ip' => null]);

    return $user;
}

//-------------------------------------------------- Start Home Section Load -------------------------------------------------

function parseFlashSaleHomeSection($flash_sale_products)
{
    $cards = '';
    foreach ($flash_sale_products as $key => $item) {
        $price_section = '';
        if ($item->sell_price >= $item->unit_price) {
            $price_section = '<strong class="product-price">'.$item->unit_price.'</strong>';
        }
        if ($item->sell_price < $item->unit_price) {
            $price_section = '<strong class="product-price" style="margin-right: 6px">'.$item->sell_price.'</strong>
                <span class="text-decoration-line-through text-muted">'.$item->unit_price.'</span>';
        }
        $button = '';
        if ($item->stock_qty > 0) {
            $button = '<button class="add-cart-button" onclick="addCart('.$item->id.')">Add to cart</button>';
        }
        if ($item->stock_qty <= 0) {
            $button = '<button class="add-cart-button text-danger">Out of stock</button>';
        }

        $cards .= '<div class="col-12 col-md-4 col-lg-3 mb-5">
                    <a class="product-item" href="'.route('product-details', $item->slug).'">
                        <img src="'.asset($item->main_image).'" alt="image-404" class="img-fluid product-thumbnail">
                        <h3 class="product-title">'.$item->name.'</h3>
                        '.$price_section.'
                    </a>
                    '.$button.'
                </div>';
    }

    return '<div class="container-co-section product-section p-0 mt-5">
    <div class="container">
        <div class="mb-4 d-flex justify-content-between">
            <h2 class="section-title border-bottom">Flash Sale</h2>
            <a href="'.route('products', ['flash-sale' => 1]).'">See more..</a>
        </div>
        <div class="row mt-3">

            <!-- Product loop -->
            '.$cards.'
            <!-- End Product loop -->

        </div>
    </div>
</div>';
}

function parseRecentArrivalHomeSection($recent_arrival_products)
{
    $cards = '';
    $currency = get_settings('currency_symbol');
    foreach ($recent_arrival_products as $key => $item) {
        $price_section = '';
        if ($item->sell_price >= $item->unit_price) {
            $price_section = '<strong class="product-price">'.$currency.$item->unit_price.'</strong>';
        }
        if ($item->sell_price < $item->unit_price) {
            $price_section = '<strong class="product-price" style="margin-right: 6px">'.$currency.$item->sell_price.'</strong>
                <span class="text-decoration-line-through text-muted">'.$currency.$item->unit_price.'</span>';
        }
        $button = '';
        if ($item->stock_qty > 0) {
            $button = '<button class="add-cart-button" onclick="addCart('.$item->id.')">Add to cart</button>';
        }
        if ($item->stock_qty <= 0) {
            $button = '<button class="add-cart-button text-danger">Out of stock</button>';
        }

        $cards .= '<div class="col-12 col-md-4 col-lg-3 mb-5">
                    <a class="product-item" href="'.route('product-details', $item->slug).'">
                        <img src="'.asset($item->main_image).'" alt="image-404" class="img-fluid product-thumbnail">
                        <h3 class="product-title">'.$item->name.'</h3>
                        '.$price_section.'
                    </a>
                    '.$button.'
                </div>';
    }

    return '<div class="container-co-section product-section p-0">
    <div class="container">
        <div class="mb-4 d-flex justify-content-between">
            <h2 class="section-title border-bottom">Build Your Cart</h2>
            <a href="'.route('products').'">See more..</a>
        </div>
        <div class="row mt-3">

            <!-- Product loop -->
            '.$cards.'
            <!-- End Product loop -->

        </div>
    </div>
</div>';
}

function parseWhyChooseSection($settings)
{
    return '<div class="why-choose-section">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-6">
                <h2 class="section-title">' . ($settings['why_choose_us_title'] ?? 'Why Choose Us') . '</h2>
                <p>' . ($settings['why_choose_us_description'] ?? 'Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique.') . '</p>

                <div class="row my-5 pb-5">
                    <div class="col-6 col-md-6">
                        <div class="feature">
                            <div class="icon">
                                <img src="' . ($settings['why_choose_us_shipping_image'] ?? asset('frontend/images/truck.svg')) . '" alt="Image" class="img-fluid">
                            </div>
                            <h3>' . ($settings['why_choose_us_shipping_title'] ?? 'Fast & Free Shipping') . '</h3>
                            <p>' . ($settings['why_choose_us_shipping_des'] ?? 'Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique.') . '</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-6">
                        <div class="feature">
                            <div class="icon">
                                <img src="' . ($settings['why_choose_us_shop_image'] ?? asset('frontend/images/bag.svg')) . '" alt="Image" class="img-fluid">
                            </div>
                            <h3>' . ($settings['why_choose_us_shop_title'] ?? 'Easy to Shop') . '</h3>
                            <p>' . ($settings['why_choose_us_shop_des'] ?? 'Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique.') . '</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-6">
                        <div class="feature">
                            <div class="icon">
                                <img src="' . ($settings['why_choose_us_support_image'] ?? asset('frontend/images/support.svg')) . '" alt="Image" class="img-fluid">
                            </div>
                            <h3>' . ($settings['why_choose_us_support_title'] ?? '24/7 Support') . '</h3>
                            <p>' . ($settings['why_choose_us_support_des'] ?? 'Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique.') . '</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-6">
                        <div class="feature">
                            <div class="icon">
                                <img src="' . ($settings['why_choose_us_return_image'] ?? asset('frontend/images/return.svg')) . '" alt="Image" class="img-fluid">
                            </div>
                            <h3>' . ($settings['why_choose_us_return_title'] ?? 'Hassle Free Returns') . '</h3>
                            <p>' . ($settings['why_choose_us_return_des'] ?? 'Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique.') . '</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="img-wrap">
                    <img src="' . ($settings['why_choose_us_image'] ?? asset('frontend/images/why-choose-us-img.jpg')) . '" alt="Image" class="img-fluid">
                </div>
            </div>

        </div>
    </div>
</div>
';
}

function parseWeHelpSection($settings)
{
    return '<div class="we-help-section">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <div class="imgs-grid">
                    <div class="grid grid-1"><img src="'. (isset($settings['we_help_you_img_one']) ? asset($settings['we_help_you_img_one']) : asset('frontend/images/img-grid-1.jpg')) .'" alt="Softstarz.com"></div>
                    <div class="grid grid-2"><img src="'. (isset($settings['we_help_you_img_two']) ? asset($settings['we_help_you_img_two']) : asset('frontend/images/img-grid-2.jpg')) .'" alt="Softstarz.com"></div>
                    <div class="grid grid-3"><img src="'. (isset($settings['we_help_you_img_three']) ? asset($settings['we_help_you_img_three']) : asset('frontend/images/img-grid-3.jpg')) .'" alt="Softstarz.com"></div>
                </div>
            </div>
            <div class="col-lg-5 ps-lg-5">
                <h2 class="section-title mb-4">'. ($settings['we_help_you_title'] ?? 'We Help You Make Modern Interior Design') .'</h2>
                '. (isset($settings['we_help_you_des']) ? '<p>'.$settings['we_help_you_des'].'</p>' : '<p>Donec facilisis quam ut purus rutrum lobortis. Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique. Pellentesque habitant morbi tristique senectus et netus et malesuada</p>') .'

                <ul class="list-unstyled custom-list my-4">
                    <li>'. ($settings['we_help_you_point_one'] ?? 'Donec vitae odio quis nisl dapibus malesuada') .'</li>
                    <li>'. ($settings['we_help_you_point_two'] ?? 'Donec vitae odio quis nisl dapibus malesuada') .'</li>
                    <li>'. ($settings['we_help_you_point_three'] ?? 'Donec vitae odio quis nisl dapibus malesuada') .'</li>
                    <li>'. ($settings['we_help_you_point_four'] ?? 'Donec vitae odio quis nisl dapibus malesuada') .'</li>
                </ul>
                <p><a href="'. route('about-us') .'" class="btn">Explore</a></p>
            </div>
        </div>
    </div>
</div>';
}

function parseFreeShippingProductsHomeSection($free_shipping_products)
{
    $cards = '';
    $currency = get_settings('currency_symbol');
    foreach ($free_shipping_products as $key => $item) {
        $price_section = '';
        if ($item->sell_price >= $item->unit_price) {
            $price_section = '<strong class="product-price">'.$currency.$item->unit_price.'</strong>';
        }
        if ($item->sell_price < $item->unit_price) {
            $price_section = '<strong class="product-price" style="margin-right: 6px">'.$currency.$item->sell_price.'</strong>
                <span class="text-decoration-line-through text-muted">'.$currency.$item->unit_price.'</span>';
        }

        $cards .= '<div class="col-12 col-md-6 col-lg-4 mb-4 mb-lg-0">
                    <div class="product-item-sm d-flex">
                        <div class="thumbnail">
                            <img src="'.asset($item->main_image).'" alt="Image" class="img-fluid height-width-100-percent">
                        </div>
                        <div class="pt-3">
                        <h3 class="product-title">'.$item->name.'</h3>
                            '.$price_section.'
                            <p>Enjoy free shipping, '. config('app.name').' offering free delivery for this product</p>
                            <p><a href="'.route('product-details', $item->slug).'">Read More</a></p>
                        </div>
                    </div>
                </div>';
    }

    return '<div class="popular-product">
    <div class="container">
        <div class="row">

            <!-- Product loop -->
            '.$cards.'
            <!-- End Product loop -->

        </div>
    </div>
</div>';
}

function parseTestimonialSection($testimonials)
{
    $cards = '';
    if (!empty($testimonials)) {
        foreach ($testimonials as $testimonial) {
            $review = $testimonial->review ?? 'Not found!';
            $image = isset($testimonial->image) ? asset($testimonial->image) : asset('frontend/images/person-1.png');
            $name = $testimonial->name ?? 'Maria Jones';
            $designation = $testimonial->designation ?? 'CEO, Co-Founder';
            $organization = $testimonial->organization_name ?? 'XYZ Inc.';

            $cards .= '<div class="item">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 mx-auto">
                                    <div class="testimonial-block text-center">
                                        <blockquote class="mb-5">
                                            <p>&ldquo;' . $review . '&rdquo;</p>
                                        </blockquote>
                                        <div class="author-info">
                                            <div class="author-pic">
                                                <img class="img-fluid height-width-100-percent" src="' . $image . '" alt="Maria Jones">
                                            </div>
                                            <h3 class="font-weight-bold">' . $name . '</h3>
                                            <span class="position d-block mb-3">' . $designation . ', ' . $organization . '</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
        }
    } else {
        // Handle case when no testimonials are available
        $cards .=  '<div class="item">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8 mx-auto">

                                            <div class="testimonial-block text-center">
                                                <blockquote class="mb-5">
                                                    <p>&ldquo;Donec facilisis quam ut purus rutrum lobortis. Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Integer convallis volutpat dui quis scelerisque.&rdquo;</p>
                                                </blockquote>

                                                <div class="author-info">
                                                    <div class="author-pic">
                                                        <img src="frontend/images/person-1.png" alt="Maria Jones" class="img-fluid">
                                                    </div>
                                                    <h3 class="font-weight-bold">Maria Jones</h3>
                                                    <span class="position d-block mb-3">CEO, Co-Founder, XYZ Inc.</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- END item -->
                                <div class="item">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8 mx-auto">

                                            <div class="testimonial-block text-center">
                                                <blockquote class="mb-5">
                                                    <p>&ldquo;Donec facilisis quam ut purus rutrum lobortis. Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Integer convallis volutpat dui quis scelerisque.&rdquo;</p>
                                                </blockquote>

                                                <div class="author-info">
                                                    <div class="author-pic">
                                                        <img src="frontend/images/person-1.png" alt="Maria Jones" class="img-fluid">
                                                    </div>
                                                    <h3 class="font-weight-bold">Maria Jones</h3>
                                                    <span class="position d-block mb-3">CEO, Co-Founder, XYZ Inc.</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- END item -->
                                <div class="item">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8 mx-auto">

                                            <div class="testimonial-block text-center">
                                                <blockquote class="mb-5">
                                                    <p>&ldquo;Donec facilisis quam ut purus rutrum lobortis. Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Integer convallis volutpat dui quis scelerisque.&rdquo;</p>
                                                </blockquote>

                                                <div class="author-info">
                                                    <div class="author-pic">
                                                        <img src="frontend/images/person-1.png" alt="Maria Jones" class="img-fluid">
                                                    </div>
                                                    <h3 class="font-weight-bold">Maria Jones</h3>
                                                    <span class="position d-block mb-3">CEO, Co-Founder, XYZ Inc.</span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- END item -->';
    }

    return '<div class="testimonial-section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 mx-auto text-center">
                            <h2 class="section-title">Testimonials</h2>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="testimonial-slider-wrap text-center">
                                <div id="testimonial-nav">
                                    <span class="prev" data-controls="prev"><span class="fa fa-chevron-left"></span></span>
                                    <span class="next" data-controls="next"><span class="fa fa-chevron-right"></span></span>
                                </div>

                                <div class="testimonial-slider">' . $cards . '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
}

function parseBlogHomeSection($blogs)
{
    $cards = '';
    if (!empty($blogs)) {
        foreach ($blogs as $key => $item) {
            $image = isset($item->image) ? asset($item->image) : asset('frontend/images/person-1.png');
            $title = $item->title ?? 'First Time Home Owner Ideas';
            $author = $item->author_name ?? 'Kristin Watson';
            $created_date = $blog->created_date ?? 'Dec 19, 2021';

            $cards .= '<div class="col-12 col-sm-6 col-md-4 mb-4 mb-md-0">
                    <div class="post-entry">
                        <a href="' .route('get-blog', $item->slug).'" class="post-thumbnail">
                            <img src="'.$image.'" alt="Image" class="img-fluid height-width-100-percent">
                        </a>
                        <div class="post-content-entry">
                            <h3><a href="' .route('get-blog', $item->slug).'" class="one-line-dotted">'.$title.'</a></h3>
                            <div class="meta">
                                <span>by <a href="' .route('get-blog', $item->slug).'">'.$author.'</a></span> <span>on <a href="#">'.$created_date.'</a></span>
                            </div>
                        </div>
                    </div>
                </div>';
        }
    }else {
        $cards = '<div class="col-12 col-sm-6 col-md-4 mb-4 mb-md-0">
                <div class="post-entry">
                    <a href="#" class="post-thumbnail"><img src="frontend/images/post-1.jpg" alt="Image" class="img-fluid"></a>
                    <div class="post-content-entry">
                        <h3><a href="#">First Time Home Owner Ideas</a></h3>
                        <div class="meta">
                            <span>by <a href="#">Kristin Watson</a></span> <span>on <a href="#">Dec 19, 2021</a></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 mb-4 mb-md-0">
                <div class="post-entry">
                    <a href="#" class="post-thumbnail"><img src="frontend/images/post-2.jpg" alt="Image" class="img-fluid"></a>
                    <div class="post-content-entry">
                        <h3><a href="#">How To Keep Your Softstarzture Clean</a></h3>
                        <div class="meta">
                            <span>by <a href="#">Robert Fox</a></span> <span>on <a href="#">Dec 15, 2021</a></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 mb-4 mb-md-0">
                <div class="post-entry">
                    <a href="#" class="post-thumbnail"><img src="frontend/images/post-3.jpg" alt="Image" class="img-fluid"></a>
                    <div class="post-content-entry">
                        <h3><a href="#">Small Space Softstarzture Apartment Ideas</a></h3>
                        <div class="meta">
                            <span>by <a href="#">Kristin Watson</a></span> <span>on <a href="#">Dec 12, 2021</a></span>
                        </div>
                    </div>
                </div>
            </div>';
    }

    return '<div class="blog-section">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-6">
                <h2 class="section-title">Recent Blog</h2>
            </div>
            <div class="col-md-6 text-start text-md-end">
                <a href="'.route('blogs').'" class="more">View All Posts</a>
            </div>
        </div>

        <div class="row">
            '.$cards.'
        </div>
    </div>
</div>';
}

?>
