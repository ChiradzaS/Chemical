<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TbDelivery;
use App\Models\TbDeliveryItem;
use Carbon\Carbon;

class ChemicalDeliveryController extends Controller
{
    // ── GET /chemicaldeliveries/store?data={...} ──────────────────────────────
public function store(Request $request)
{
    $dataString = $request->query('data');
    $payload    = json_decode(urldecode($dataString), true);

    $deliveryData = [
        'customerId' => $payload['customerId'] ?? null,
        'address'    => $payload['address']    ?? null,
        'reference'  => $payload['reference']  ?? null,
        'notes'      => $payload['notes']      ?? null,
    ];
    $items   = $payload['items']   ?? [];
    $docType = $payload['docType'] ?? 'invoice';
    $totExcl = $payload['totExcl'] ?? 0;
    $totVat  = $payload['totVat']  ?? 0;

    if (empty($deliveryData['customerId']) || empty($items)) {
        return response()->json(['error' => 'No data provided'], 422);
    }

    try {
        DB::beginTransaction();

        // ── 1. Invoice ────────────────────────────────────────────────────
        $invoiceId = DB::table('invoices')->insertGetId([
            'reference'     => $deliveryData['reference']  ?? null,
            'customerId'    => $deliveryData['customerId'] ?? null,
            'totalValue'    => $totExcl,
            'totalVat'      => $totVat,
            'totalDiscount' => 0,
            'stateId'       => 61,
            'other'         => $deliveryData['notes']      ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // ── 2. Invoice items ──────────────────────────────────────────────
        // NOTE: stock deduction + stocks_trans logging is now handled by the
        // trg_invoice_items_after_insert DB trigger on the invoice_items
        // table — it fires automatically for every row inserted here, so
        // there's no PHP-side stock code left in this method.
        foreach ($items as $item) {
            DB::table('invoice_items')->insert([
                'invoicesId'  => $invoiceId,
                'productId'   => $item['productId'],
                'unitId'      => $item['containerId'],
                'taxId'       => 0,
                'quantity'    => $item['quantity'],
                'price'       => $item['unitPrice'],
                'totalPrice'  => $item['total'],
                'VatType'     => $item['vatApplicable'] ? 1 : 0,
                'vatAmnt'     => $item['vatAmount'],
                'Discount'    => 0,
                'stateId'     => 61,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $deliveryId = null;

        // ── 3. Delivery note (only if docType = 'both') ───────────────────
        // A delivery note is just a record that piggybacks on the invoice
        // that was just created above — it never touches stock. Stock is
        // handled entirely by the DB trigger on invoice_items, keyed off
        // the invoice created in step 1.
        if ($docType === 'both') {
            $delivery = new TbDelivery;
            $delivery->reference  = $deliveryData['reference']  ?? null;
            $delivery->customerId = $deliveryData['customerId'] ?? null;
            $delivery->addressId  = $deliveryData['address']    ?? null;
            $delivery->invoiceNo  = $invoiceId;
            $delivery->vehicleReg = null;
            $delivery->driver     = null;
            $delivery->save();

            $deliveryId = $delivery->id;

            foreach ($items as $item) {
                $deliveryItem = new TbDeliveryItem;
                $deliveryItem->deliveryId = $deliveryId;
                $deliveryItem->productId  = $item['productId'];
                $deliveryItem->quantity   = $item['quantity'];
                $deliveryItem->unitId     = $item['containerId'];
                $deliveryItem->save();
            }
        }

        DB::commit();

        // ── 4. Print depending on what was saved ───────────────────────────
        if ($docType === 'both') {
            return redirect()->route('printBoth', [
                'invoiceId'  => $invoiceId,
                'deliveryId' => $deliveryId,
            ]);
        }

        return redirect()->route('printInvoice', ['id' => $invoiceId]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('ChemicalDelivery store error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    // ── GET /chemicaldeliveries/index?list=1 ─────────────────────────────────
    // Left-join so invoice-only records (no delivery note) still appear.
    // One row per invoice_item — React groups by invoice_id.
        public function index(Request $request)
        {
            $rows = DB::table('invoices')
                ->leftJoin('tb_deliveries', 'tb_deliveries.invoiceNo', '=', 'invoices.id')
                ->join('invoice_items', 'invoice_items.invoicesId', '=', 'invoices.id')
                ->select(
                    // invoice header
                    'invoices.id           as invoice_id',
                    'invoices.reference    as invoice_reference',
                    'invoices.customerId   as invoice_customerId',
                    'invoices.totalValue   as invoice_totalValue',
                    'invoices.totalVat     as invoice_totalVat',
                    DB::raw('(invoices.totalValue + COALESCE(invoices.totalVat, 0)) as invoice_totIncl'),
                    'invoices.other        as invoice_notes',
                    'invoices.created_at   as invoice_created_at',
                    // delivery header (null when invoice-only)
                    'tb_deliveries.id      as delivery_id',
                    'tb_deliveries.addressId as delivery_address',
                    // item columns
                    'invoice_items.productId  as item_productId',
                    'invoice_items.unitId     as item_containerId',
                    'invoice_items.quantity   as item_quantity',
                    'invoice_items.price      as item_unitPrice',
                    'invoice_items.VatType    as item_vatApplicable',
                    'invoice_items.vatAmnt    as item_vatAmount',
                    'invoice_items.totalPrice as item_total'
                )
                ->orderBy('invoices.created_at', 'desc')
                ->get();

            return response()->json($rows);
        }

    // ── GET /chemicaldeliveries/destroy?data={...} ────────────────────────────
    // Deletes invoice + invoice_items + delivery + delivery_items atomically,
    // and reverses the stock deduction + logs a reversing stocks_trans entry.
    public function destroy(Request $request)
    {
        try {
            $data       = json_decode(urldecode($request->query('data')), true);
            $invoiceId  = $data['invoiceId']  ?? null;
            $deliveryId = $data['deliveryId'] ?? null;

            if (!$invoiceId) {
                return response()->json(['success' => false, 'error' => 'No invoiceId'], 422);
            }

            DB::beginTransaction();

            // ── Fetch invoice items BEFORE deleting them — needed to reverse stock ──
            $invoiceItems = DB::table('invoice_items')
                ->where('invoicesId', $invoiceId)
                ->get(['productId', 'quantity']);

            // ── Reverse stock deduction + log reversing transaction ─────────────
            foreach ($invoiceItems as $item) {
                $stock = DB::table('stocks')
                    ->where('productId', $item->productId)
                    ->first();

                if ($stock) {
                    $restoredQnt = $stock->qnt + $item->quantity;

                    DB::table('stocks')
                        ->where('productId', $item->productId)
                        ->update([
                            'prvqnt'     => $stock->qnt,
                            'qnt'        => $restoredQnt,
                            'updated_at' => now(),
                        ]);

                    // Positive qnt = stock back in (reversal of the original -qnt sale)
                    DB::table('stocks_trans')->insert([
                        'stockId'    => $stock->id,
                        'docId'      => $invoiceId,
                        'docType'    => 214,           // 103 = chemical invoice reversal / delete
                        'qnt'        => $item->quantity,
                        'userId'     => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Remove delivery first (FK order)
            if ($deliveryId) {
                DB::table('tb_delivery_items')->where('deliveryId', $deliveryId)->delete();
                DB::table('tb_deliveries')->where('id', $deliveryId)->delete();
            }

            DB::table('invoice_items')->where('invoicesId', $invoiceId)->delete();
            $deleted = DB::table('invoices')->where('id', $invoiceId)->delete();

            if ($deleted === 0) {
                DB::rollBack();
                return response()->json(['success' => false], 404);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ChemicalDelivery destroy error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── GET /chemicaldeliveries/show?data={id} ────────────────────────────────
    public function show(Request $request)
    {
        $data       = json_decode(urldecode($request->query('data')), true);
        $deliveryId = is_array($data) ? ($data['id'] ?? null) : $data;

        $delivery = TbDelivery::find($deliveryId);
        if (!$delivery) return response()->json(['error' => 'Not found'], 404);

        $items        = TbDeliveryItem::where('deliveryId', $deliveryId)->get();
        $invoice      = DB::table('invoices')->find($delivery->invoiceNo);
        $invoiceItems = $invoice
            ? DB::table('invoice_items')->where('invoicesId', $invoice->id)->get()
            : [];

        return response()->json([
            'delivery'     => $delivery,
            'items'        => $items,
            'invoice'      => $invoice,
            'invoiceItems' => $invoiceItems,
        ]);
    }

    // ── GET /chemicaldeliverylist ─────────────────────────────────────────────
    public function listView()
    {
        return view('chemicaldeliverylist', [
            
            'chemicalProducts' => DB::table('chemical_products')->orderBy('name')->get(),
         
        ]);
    }

    // ── GET /chemicaldeliveries/create ────────────────────────────────────────
    public function createView()
    {
        return view('chemicaldeliveries', [

         
        ]);
    }
}