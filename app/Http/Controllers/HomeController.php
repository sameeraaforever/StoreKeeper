<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getUsers(){
       $model = User::query();

        return DataTables::of($model)  // <-- use of() instead of eloquent()
            ->addColumn('action', function ($user) {
                return '<a href="/users/' . $user->id . '/edit" class="btn btn-sm btn-primary">Edit</a>';
            })
        ->toJson();
    }
}
