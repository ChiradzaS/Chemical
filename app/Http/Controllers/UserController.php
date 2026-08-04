<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Barcode\Barcode;
use Hash;
use Auth;

class UserController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index()
{
$data['users'] = User::orderBy('id','desc')->paginate(1000);
return view('users.index', $data);
}
/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
return view('users.create');
}
/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:4',
    ]);

    $user = new User();
    $user->name             = $request->name;
    $user->email            = $request->email;
    $user->password         = Hash::make($request->password);
    $user->other            = $request->password;
    $user->userId           = Auth::id();
    $user->save();

    return redirect()->route('users.index')
        ->with('success', 'A new User has been created successfully.');
}
/**
* Display the specified resource.
*
* @param  \App\user  $user
* @return \Illuminate\Http\Response
*/
public function show(User $user)
{
return view('users.index',compact('user'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\User  $user
* @return \Illuminate\Http\Response
*/
public function edit(User $user)
{
return view('users.edit',compact('user'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\user  $user
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$user = User::find($id);
$user->remember_token =  Barcode::uniqidReal();
$user->name = $request->name;
$user->email = $request->email;
$user->password = $request->password;
$user->save();
return redirect()->route('users.index')
->with('success','You have successfully updated details '. $request->name);
}
/**
* Remove the specified resource from storage.
*
* @param  \App\User  $user
* @return \Illuminate\Http\Response
*/
public function destroy(User $user)
{
$user->delete();
return redirect()->route('users.index')
->with('success','A User has been deleted successfully');
}

}
