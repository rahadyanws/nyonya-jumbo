<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;

Route::get('/', function () {
    // Ambil produk yang statusnya 'Available' saja
    // Urutkan terbaru, dan group by kategori (opsional, tapi lebih rapi jika di-sort)
    $products = Product::where('is_available', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get();
        
    // Ambil list kategori unik untuk filter tab (jika nanti mau dipakai)
    $categories = $products->pluck('category')->unique();

    return view('welcome', compact('products', 'categories'));
});