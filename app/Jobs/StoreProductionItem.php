<?php

namespace App\Jobs;

use App\Models\Productionitem;
use App\Models\JobcardItem;
use App\Models\Chemicaljobcarditem;
use App\Models\Production;
use App\Models\SerialNo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoreProductionItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        try {

            $production = Production::find($this->data['productionId']);

            if (!$production) {
                \Log::error('Production not found', [
                    'productionId' => $this->data['productionId'],
                    'timestamp' => now()
                ]);
                return;
            }

            // Create the production item
            $productionItem = Productionitem::create([

                'productionId'    => $this->data['productionId'],
                'jobcarditemId'   => $this->data['jobcarditemId'],
                'other'           => 'none',
                'productId'       => $this->data['productId'],
                'userId'          => $production->userId, // from production
                'weight'          => $this->data['weight'],
                'processId'       => 212,
                'qnt'             => $this->data['qnt'] ?? 1,
                'unitId'          => $this->data['unitId'],
                'machineId'       => $production->machineryId, // from production
                'tms'             => now()->format('H:i:s'),
                'employeeId'      => $production->userId, // from production
                'shiftId'         => $production->shiftId ?? 31, // default shift if not set
                'wpProduct'       => $this->data['wpProduct'],
                'weightState'     => $this->data['weightState'],
                'tempId'          => $this->data['tempId'] ?? 0,
                'serialNo'        => $this->data['SerialNo'],
                'unique_code'     => $this->data['code'],
                'rollId'          => $this->data['rollId'] ?? 0,
                'weight_per_bale' => $this->data['weight_per_bale'],
                'dateCreated'     => $this->data['dateCreated'],

            ]);

            // ── Stock addition + transaction log ──────────────────────────
            $stock = DB::table('stocks')
                ->where('productId', $productionItem->productId)
                ->first();

            if ($stock) {
                $newQnt = $stock->qnt + $productionItem->qnt;

                // Add production output to stock balance
                DB::table('stocks')
                    ->where('productId', $productionItem->productId)
                    ->update([
                        'prvqnt'     => $stock->qnt,
                        'qnt'        => $newQnt,
                        'updated_at' => now(),
                    ]);

                // Log to stocks_trans — positive qnt = stock in
                DB::table('stocks_trans')->insert([
                    'stockId'    => $stock->id,
                    'docId'      => $productionItem->id,
                    'docType'    => 105,          // 105 = production item
                    'qnt'        => $productionItem->qnt,
                    'userId'     => $productionItem->userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                \Log::warning('No stock row found for product', [
                    'productId' => $productionItem->productId,
                    'productionItemId' => $productionItem->id,
                ]);
            }

            // ── Jobcard qnt deduction ────────────────────────────────────
            try {

                if (!empty($this->data['jobcarditemId'])) {

                    $jobcardItem = Chemicaljobcarditem::find($this->data['jobcarditemId']);

                    if (!$jobcardItem) {
                        \Log::warning('JobcardItem or related Jobcard not found', [
                            'jobcarditemId' => $this->data['jobcarditemId']
                        ]);
                    } else {

                        $newQnt = $jobcardItem->quantity - $productionItem->qnt;
                        $jobcardItem->quantity = $newQnt;
                        $jobcardItem->save();

                        \Log::info('Jobcard item qnt updated', [
                            'jobcarditemId' => $jobcardItem->id,
                            'qntProduced'   => $productionItem->qnt,
                            'newQnt'        => $jobcardItem->quantity,
                        ]);
                    }
                }

            } catch (\Exception $e) {
                \Log::error('Error processing jobcard: ' . $e->getMessage(), [
                    'exception'     => $e,
                    'jobcarditemId' => $this->data['jobcarditemId'] ?? null
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to store production item', [
                'exception' => $e->getMessage(),
                'data'      => $this->data
            ]);

            throw $e; // Rethrow to trigger job failure handling
        }
    }
}