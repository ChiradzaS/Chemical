<?php

namespace App\Http\Controllers;

use App\Models\ChemicalJobCard;
use App\Models\ChemicalJobCardItem;
use App\Models\ChemicalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChemicalJobCardController extends Controller
{
    // GET /chemicaljobcards/index?data=query
    public function index(Request $request)
    {
        $dataString = $request->query('data');
        $threeMonthsAgo = Carbon::now()->subMonths(2);

        $response = ChemicalJobCard::join('chemical_job_card_items', 'chemical_job_cards.id', '=', 'chemical_job_card_items.jobCardId')
            ->select(
                'chemical_job_cards.id as job_card_id',
                'chemical_job_cards.customerId as job_cards_customerId',
                'chemical_job_cards.productId as job_cards_productId',
                'chemical_job_cards.quantity as job_cards_quantity',
                'chemical_job_cards.batchCount as job_cards_batchCount',
                'chemical_job_cards.totalUnits as job_cards_totalUnits',
                'chemical_job_cards.barcode as job_cards_barcode',
                'chemical_job_cards.notes as job_cards_notes',
                'chemical_job_cards.stateId as job_cards_stateId',
                'chemical_job_cards.startDate as job_cards_startDate',
                'chemical_job_cards.created_at as job_cards_created_at',
                'chemical_job_card_items.id as jobcarditem_id',
                'chemical_job_card_items.processId as jobcarditem_processId',
                'chemical_job_card_items.productId as jobcarditem_productId',
                'chemical_job_card_items.quantity as jobcarditem_quantity',
                'chemical_job_card_items.outstanding as jobcarditem_outstanding',
                'chemical_job_card_items.unitId as jobcarditem_unitId',
                'chemical_job_card_items.stateId as jobcarditem_stateId'
            )
            ->groupBy(
                'chemical_job_cards.id',
                'chemical_job_cards.customerId',
                'chemical_job_cards.productId',
                'chemical_job_cards.quantity',
                'chemical_job_cards.batchCount',
                'chemical_job_cards.totalUnits',
                'chemical_job_cards.barcode',
                'chemical_job_cards.notes',
                'chemical_job_cards.stateId',
                'chemical_job_cards.startDate',
                'chemical_job_cards.created_at',
                'chemical_job_card_items.id',
                'chemical_job_card_items.processId',
                'chemical_job_card_items.productId',
                'chemical_job_card_items.quantity',
                'chemical_job_card_items.outstanding',
                'chemical_job_card_items.unitId',
                'chemical_job_card_items.stateId'
            )
            ->where('chemical_job_cards.created_at', '>=', $threeMonthsAgo)
            ->where('chemical_job_cards.stateId', '<>', 45)
            ->orderBy('chemical_job_cards.created_at', 'desc')
            ->get();

        return response()->json($response);
    }

    // GET /chemicaljobcards/store?data={...}
    public function store(Request $request)
    {
        $dataString  = $request->query('data');
        $allData     = json_decode(urldecode($dataString), true);

        $jobCardData = $allData['jobCard'] ?? null;
        $items       = $allData['items']   ?? [];

        if (!$jobCardData) {
            return response()->json(['error' => 'No job card data provided'], 422);
        }

        // Create job card
        $jobCard = new ChemicalJobCard;
        $jobCard->customerId         = $jobCardData['customerId']         ?? null;
        $jobCard->productId          = $jobCardData['productId']          ?? null;
        $jobCard->quantity           = $jobCardData['quantity']           ?? null;
        $jobCard->batchCount         = $jobCardData['batchCount']         ?? null;
        $jobCard->unitId             = $jobCardData['unitId']             ?? null;
        $jobCard->containerSizeId    = $jobCardData['containerSizeId']    ?? null;
        $jobCard->colourId           = $jobCardData['colourId']           ?? null;
        $jobCard->viscosityId        = $jobCardData['viscosityId']        ?? null;
        $jobCard->activeIngredientId = $jobCardData['activeIngredientId'] ?? null;
        $jobCard->fragranceId        = $jobCardData['fragranceId']        ?? null;
        $jobCard->bottleTypeId       = $jobCardData['bottleTypeId']       ?? null;
        $jobCard->weightPerUnit      = $jobCardData['weightPerUnit']      ?? null;
        $jobCard->totalUnits         = $jobCardData['totalUnits']         ?? null;
        $jobCard->barcode            = $jobCardData['barcode']            ?? null;
        $jobCard->notes              = $jobCardData['notes']              ?? null;
        $jobCard->startDate          = $jobCardData['startDate']          ?? now()->toDateString();
        $jobCard->stateId            = 61;
        $jobCard->save();

        // Create job card items
        foreach ($items as $item) {
            $jobCardItem = new ChemicalJobCardItem;
            $jobCardItem->jobCardId   = $jobCard->id;
            $jobCardItem->processId   = $item['processId']   ?? null;
            $jobCardItem->processName = $item['processName'] ?? null;
            $jobCardItem->productId   = $item['productId']   ?? null;
            $jobCardItem->quantity    = $item['quantity']    ?? null;
            $jobCardItem->outstanding = $item['quantity']    ?? null;
            $jobCardItem->unitId      = $item['unitId']      ?? null;
            $jobCardItem->barcode     = $jobCard->barcode;
            $jobCardItem->stateId     = 61;
            $jobCardItem->save();
        }

        return response()->json([
            'status'     => 'Chemical job card created',
            'jobCardId'  => $jobCard->id,
            'itemsCount' => count($items),
        ]);
    }

    // GET /chemicaljobcards/show?data={id}
    public function show(Request $request)
    {
        $data    = json_decode(urldecode($request->query('data')), true);
        $id      = $data['id'];

        $jobCard = ChemicalJobCard::findOrFail($id);
        $items   = ChemicalJobCardItem::where('jobCardId', $id)->get();

        return response()->json([
            'jobCard' => $jobCard,
            'items'   => $items,
        ]);
    }

    // GET /chemicaljobcards/destroy?data={...}
    public function destroy(Request $request)
    {
        $data      = json_decode(urldecode($request->query('data')), true);
        $jobCardId = $data['id'];

        try {
            DB::beginTransaction();
            DB::table('chemical_job_card_items')->where('jobCardId', $jobCardId)->delete();
            $deleted = DB::table('chemical_job_cards')->where('id', $jobCardId)->delete();
            if ($deleted === 0) {
                DB::rollBack();
                return response()->json(['success' => false], 404);
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Chemical job card delete error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}