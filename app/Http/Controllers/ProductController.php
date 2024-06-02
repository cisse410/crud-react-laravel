<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::select('id', 'title', 'description', 'image')->get();
    }


    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'description' => ['required'],
            'image' => ['required', 'image:image'],
            // axios, react-bootstrap, bootstrap, react-router-dom, sweetalert,
        ]);
        try {
            $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
            Storage::putFileAs('product/image', $request->image, $imageName);
            Product::create($request->post() + ['image' => $imageName]);
            return response()->json([
                'message' => 'Produit cree avec succes',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur : " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => ['required'],
            'description' => ['required'],
            'image' => ['required|image'],
        ]);
        try {
            $product->fill($request->post())->update();
            if ($request->hasFile('image')) {
                if ($product->image) {
                    $exist = Storage::exists('product/image/{$product->image}');
                    if ($exist) {
                        Storage::delete('product/image/{$product->image}');
                    }
                }
                $imageName = Str::random() . '.' . $request->image->getClientOriginalExtension();
                Storage::putFileAs('product/image', $request->image, $imageName);
                $product->image = $imageName;
                $product->save();
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur : " . $e->getMessage(),
            ]);
        }
        return response()->json([
            'message' => 'Produit modifie avec succes',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @param \App\Models\Product $product
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                $exist = Storage::exists('product/image/{$product->image}');
                if ($exist) {
                    Storage::delete('product/image/{$product->image}');
                }
            }
            $product->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur : " . $e->getMessage(),
            ]);
        }
        return response()->json([
            'message' => 'Produit supprime avec succes',
        ]);
    }
}