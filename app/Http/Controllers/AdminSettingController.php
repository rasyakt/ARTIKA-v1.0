<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'enable_camera'      => Setting::get('enable_camera', true),
            'receipt_paper_size' => Setting::get('receipt_paper_size', '58mm'),
            'store_name'         => Setting::get('store_name', 'ARTIKA POS'),
            'store_phone'        => Setting::get('store_phone', '(021) 1234567'),
            'store_email'        => Setting::get('store_email', 'hello@artikapos.com'),
            'store_address'      => Setting::get('store_address', 'Jl. Raya Utama No. 123, Kota Anda'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $input = $request->except('_token');

        // Handle checkboxes (boolean settings)
        $checkboxKeys = ['enable_camera', 'cashier_enable_product_photos'];
        foreach ($checkboxKeys as $key) {
            $value = isset($input[$key]) ? '1' : '0';
            Setting::set($key, $value);
        }

        // Handle text/select/standard settings
        $standardKeys = [
            'receipt_paper_size',
            'store_name',
            'store_phone',
            'store_email',
            'store_address'
        ];
        
        foreach ($standardKeys as $key) {
            if (isset($input[$key])) {
                Setting::set($key, $input[$key]);
            }
        }

        return redirect()->route('admin.settings')->with('success', __('admin.settings_updated') ?? 'Settings updated successfully.');
    }
}
