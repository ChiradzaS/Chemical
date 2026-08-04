<?php
namespace App\Imports;
use App\Models\Clocking;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
class ImportUser implements ToModel
{
	public function model(array $row)
	{
		
		

			$expectedHeader = "Staff Code";

			
				
			
		
			if (strpos($row[0], $expectedHeader) !== 0) {
				$clockingData = [
					'name' => $row[1],
					'date' => $row[2],
					'day' =>  strtolower($row[3]),
					'clockInTime' => $row[4],
					'clockOutTime' => $row[5],
					'shift' => $row[6],
				];

			
			
				if ($row[6] === 'night') {

					$clockingData['clockInTime'] = date('H:i', strtotime($row[4] . ' +12 hours'));
					$clockingData['clockOutTime'] = date('H:i', strtotime($row[5] . ' +12 hours'));
				}
			
				return new Clocking($clockingData);
			}
			
			
		
		
	

	}
	
}

