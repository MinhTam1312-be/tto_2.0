<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        // Validate ảnh
        $request->validate([
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        // Lấy tên file gốc
        $originalName = $request->file('image')->getClientOriginalName();

        // Lưu ảnh vào storage/app/public/images với tên gốc
        $path = $request->file('image')->storeAs('public/app/images', $originalName);

        // Trả lại URL của ảnh đã upload
        $url = Storage::url($path);

        return back()->with('success', 'Ảnh được lưu thành công <3')->with('image', $url);
    }
}