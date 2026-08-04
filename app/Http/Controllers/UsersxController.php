<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportUser;
use App\Exports\ExportUser;
use App\Models\User;
use App\Models\Clocking;

class UsersxController extends Controller
{

	public function list(){


	
	

		$data['clockings'] = Clocking::groupBy('name','id','date','clockInTime','shift','clockOutTime','day','created_at','updated_at')->paginate(100);
	




									


									   return view('clocking',$data);
		

		}

	public function importView(Request $request){
		return view('importFile');
	}

	public function import(Request $request){
		Excel::import(new ImportUser, 
					$request->file('file')->store('files'));

		$data['clockings'] = Clocking::groupBy('name','id','date','clockInTime','shift','clockOutTime','day','created_at','updated_at')->paginate(200);
		return view('clocking',$data);
	}

	

	public function exportUsers(Request $request){
		return Excel::download(new ExportUser, 'users.xlsx');
	}
}
?>
