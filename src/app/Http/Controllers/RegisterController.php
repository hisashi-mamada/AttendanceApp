//<?php

//namespace App\Http\Controllers;

//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
//use App\Http\Requests\RegisterRequest;

//class RegisterController extends Controller
//{
//    public function create()
//    {
//        return view('items.register');
//    }

//    public function store(RegisterRequest $request)
//    {
//        $validated = $request->validated();

//        User::create([
//            'name' => $validated['name'],
//            'email' => $validated['email'],
//            'password' => bcrypt($validated['password']),
//        ]);

//        Auth::attempt($request->only('email', 'password'));

//        return redirect()->route('attendance');
//    }
//}
