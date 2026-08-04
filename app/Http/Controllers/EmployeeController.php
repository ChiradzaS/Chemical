<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Employeejob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View; 
use DB;


class EmployeeController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index()
{
  
  //   if (Auth::check() == false) {
  //       //return redirect("login")->withSuccess('Login details are not valid');
  //       return redirect("login")->withSuccess('Login details are not valid');
       
  //  }

  $data['employees'] = Employee::orderBy('id','asc')->paginate(100);
  return view('employees.index', $data);
}
/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
  
  $usersWithoutDetails = DB::select("

  SELECT userId, name
  FROM user_details
  WHERE  userId NOT IN (
  SELECT userId
  FROM employees
) and userTypeId = 46  

");

    //  echo "<pre>";
    // print_r($outsta);
    //  exit;

View::share('usersWithoutDetails',$usersWithoutDetails);

return view('employees.create');

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
'name' => 'required',
]);
$employee = new Employee;
$employee->name = $request->name;
$employee->surname = $request->surname;
$employee->dateOfBirth = $request->dateOfBirth;
$employee->startOfJob = $request->startOfJob;
$employee->nationality = $request->nationality;
$employee->identityNo= $request->identityNo;
$employee->initials= $request->initials;
$employee->nickName = $request->nickName;
$employee->uniqueIdentifiableName = $request->uniqueIdentifiableName;
$employee->documentNo = $request->documentNo;
$employee->documentType = $request->documentType;
$employee->documentTypeId = $request->documentTypeId;
$employee->healthComments = $request->healthComments;
$employee->jobId = $request->jobId;
$employee->userId = $request->userId;
$employee->contactNo = $request->contactNo;
$employee->postalAddress = $request->postalAddress;
$employee->cellPhoneNo = $request->cellPhoneNo;
$employee->gender = $request->gender;
$employee->password = $request->password;
$employee->physicalAddress = $request->physicalAddress;
$employee->dateOftermination = $request->dateOftermination;
$employee->other = $request->other;
$employee->userId = Auth::id();
$employee->save();

// $jobtype = new Employee;
// $jobtype->employeeId = $employee->id;
// $jobtype->role = $employee->jobId;
// $jobtype->save();

return redirect()->route('employees.index')
->with('success','A new Employee has been created successfully.');
}
/**
* Display the specified resource.
*
* @param  \App\employee  $employee
* @return \Illuminate\Http\Response
*/
public function show(Employee $employee)
{
return view('employees.show',compact('employee'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\Employee  $employee
* @return \Illuminate\Http\Response
*/
public function edit(Employee $employee)
{
return view('employees.edit',compact('employee'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\employee  $employee
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'name' => 'required',
]);
$employee = Employee::find($id);
$employee->name = $request->name;
$employee->surname = $request->surname;
$employee->dateOfBirth = $request->dateOfBirth;
$employee->startOfJob = $request->startOfJob;
$employee->nationality = $request->nationality;
$employee->identityNo= $request->identityNo;
$employee->initials= $request->initials;
$employee->nickName = $request->nickName;
$employee->jobId = $request->jobId;
$employee->uniqueIdentifiableName = $request->uniqueIdentifiableName;
$employee->documentNo = $request->documentNo;
$employee->documentType = $request->documentType;
$employee->userId = $request->userId;
$employee->password = $request->password;
$employee->documentTypeId = $request->documentTypeId;
$employee->healthComments = $request->healthComments;
$employee->contactNo = $request->contactNo;
$employee->postalAddress = $request->postalAddress;
$employee->cellPhoneNo = $request->cellPhoneNo;
$employee->gender = $request->gender;
$employee->physicalAddress = $request->physicalAddress;
$employee->dateOftermination = $request->dateOftermination;
$employee->other = $request->other;
$employee->save();

// $jobtype = new Employeejob;
// $jobtype->employeeId = $id;
// $jobtype->role = $employee->jobId;
// $jobtype->save();


return redirect()->route('employees.index')
->with('success','Employee details have been successfully updated');
}
/**
* Remove the specified resource from storage.
*
* @param  \App\Employee  $employee
* @return \Illuminate\Http\Response
*/
public function destroy(Employee $employee)
{
$employee->delete();
return redirect()->route('employees.index')
->with('success','A Employee has been deleted successfully');
}

}
