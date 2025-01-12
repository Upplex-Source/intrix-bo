<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Validator,
    Storage,
};

use Helper;

use App\Models\{
    VendingMachine,
    VendingMachineStock,
    FileManager,
};


use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VendingMachineStockService
{

    public static function createVendingMachineStock( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
            'froyos' => json_decode($request->froyos, true),
            'syrups' => json_decode($request->syrups, true),
            'toppings' => json_decode($request->toppings, true),
        ] );

        $validator = Validator::make($request->all(), [

            'id' => ['required'],
            'froyos' => ['nullable', 'array'],
            'froyos.*.froyo_id' => ['required', 'exists:froyos,id'], // Correct key for froyo_id
            'froyos.*.quantity' => ['required', 'min:1'], // Quantity must be at least 1 for each froyo
        
            'syrups' => ['nullable', 'array'], // Syrup items, if they exist
            'syrups.*.syrup_id' => ['required', 'exists:syrups,id'], // Correct key for syrup_id
            'syrups.*.quantity' => ['required', 'min:1'], // Quantity must be at least 1 for each syrup
        
            'toppings' => ['nullable', 'array'], // Topping items, if they exist
            'toppings.*.topping_id' => ['required', 'exists:toppings,id'], // Correct key for topping_id
            'toppings.*.quantity' => ['required', 'min:1'], // Quantity must be at least 1 for each topping
        ]);

        $attributeName = [
            'froyos.*.froyo_id' => __('template.froyos'),
            'froyos.*.quantity' => __('template.quantity'),
            'syrups.*.syrup_id' => __('template.syrups'),
            'syrups.*.quantity' => __('template.quantity'),
            'toppings.*.topping_id' => __('template.toppings'),
            'toppings.*.quantity' => __('template.quantity'),
        ];
        
        foreach ($attributeName as $key => $aName) {
            $attributeName[$key] = strtolower($aName);
        }
        
        $validator->setAttributeNames($attributeName)->validate();
        DB::beginTransaction();

        try {

            // Handle
            $froyos = $request->froyos;
            $syrups = $request->syrups;
            $toppings = $request->toppings;

            // First, delete records that are not in the arrays
                // For Froyo
            VendingMachineStock::where('vending_machine_id', $request->id)
                ->whereNotIn('froyo_id', array_column($froyos, 'froyo_id'))
                ->whereNotNull('froyo_id') // Ensures we are checking froyo_id and not null
                ->delete();

            // For Syrup
            VendingMachineStock::where('vending_machine_id', $request->id)
                ->whereNotIn('syrup_id', array_column($syrups, 'syrup_id'))
                ->whereNotNull('syrup_id') // Ensures we are checking syrup_id and not null
                ->delete();

            // For Topping
            VendingMachineStock::where('vending_machine_id', $request->id)
                ->whereNotIn('topping_id', array_column($toppings, 'topping_id'))
                ->whereNotNull('topping_id') // Ensures we are checking topping_id and not null
                ->delete();

            foreach( $froyos as $froyo ){

                // Attempt to find an existing record for the syrup
                $existingStock = VendingMachineStock::where('vending_machine_id', $request->id)
                    ->where('froyo_id', $froyo['froyo_id'])
                    ->first();

                // If an existing record is found, set old_quantity to the current quantity in the database
                $oldQuantity = $existingStock ? $existingStock->quantity : $froyo['quantity'];

                $vendingmMachineStockCreate = VendingMachineStock::updateOrCreate(
                    [
                        'vending_machine_id' => $request->id,
                        'froyo_id' => $froyo['froyo_id'], // Froyo ID for matching
                    ],
                    [
                        'syrup_id' => null, // Handle null for syrups
                        'topping_id' => null, // Handle null for toppings
                        'quantity' => $froyo['quantity'],
                        'old_quantity' => $oldQuantity, // Set old_quantity to the current quantity
                        'last_stock_check' => now(), // Set today's date
                        'status' => 10, // Adjust status if necessary
                    ]
                );
            }

            foreach ($syrups as $syrup) {

                // Attempt to find an existing record for the syrup
                $existingStock = VendingMachineStock::where('vending_machine_id', $request->id)
                    ->where('syrup_id', $syrup['syrup_id'])
                    ->first();

                // If an existing record is found, set old_quantity to the current quantity in the database
                $oldQuantity = $existingStock ? $existingStock->quantity : $syrup['quantity'];

                // Find or create record for syrups
                $vendingmachineCreate = VendingMachineStock::updateOrCreate(
                    [
                        'vending_machine_id' => $request->id,
                        'syrup_id' => $syrup['syrup_id'], // Syrup ID for matching
                    ],
                    [
                        'froyo_id' => null, // Handle null for froyo
                        'topping_id' => null, // Handle null for topping
                        'quantity' => $syrup['quantity'],
                        'old_quantity' => $oldQuantity, // Set old_quantity to the current quantity
                        'last_stock_check' => now(), // Set today's date
                        'status' => 10, // Adjust status if necessary
                    ]
                );
            }
            
            foreach ($toppings as $topping) {

                // Attempt to find an existing record for the syrup
                $existingStock = VendingMachineStock::where('vending_machine_id', $request->id)
                    ->where('topping_id', $topping['topping_id'])
                    ->first();

                // If an existing record is found, set old_quantity to the current quantity in the database
                $oldQuantity = $existingStock ? $existingStock->quantity : $syrup['quantity'];

                // Find or create record for toppings
                $vendingmachineCreate = VendingMachineStock::updateOrCreate(
                    [
                        'vending_machine_id' => $request->id,
                        'topping_id' => $topping['topping_id'], // Topping ID for matching
                    ],
                    [
                        'froyo_id' => null, // Handle null for froyo
                        'syrup_id' => null, // Handle null for syrup
                        'quantity' => $topping['quantity'],
                        'old_quantity' => $oldQuantity, // Set old_quantity to the current quantity
                        'last_stock_check' => now(), // Set today's date
                        'status' => 10, // Adjust status if necessary
                    ]
                );
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.new_x_created', [ 'title' => Str::singular( __( 'template.vending_machine_stocks' ) ) ] ),
        ] );
    }
    
    public static function updateVendingMachineStock($vendingMachineId, $orderMetas)
    {
        DB::beginTransaction();
    
        try {
            foreach ($orderMetas as $orderMeta) {
                $froyoStocks = json_decode($orderMeta->froyos, true) ?? [];
                $syrupStocks = json_decode($orderMeta->syrups, true) ?? [];
                $toppingStocks = json_decode($orderMeta->toppings, true) ?? [];
    
                // Process each type of stock
                self::processStockUpdates($vendingMachineId, $froyoStocks, 'froyo_id');
                self::processStockUpdates($vendingMachineId, $syrupStocks, 'syrup_id');
                self::processStockUpdates($vendingMachineId, $toppingStocks, 'topping_id');
            }
    
            DB::commit();
    
            return response()->json([
                'message' => __('template.x_updated', ['title' => Str::singular(__('template.vending_machine_stocks'))]),
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
    
            return response()->json([
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500);
        }
    }
    
    private static function processStockUpdates($vendingMachineId, array $stocks, $column)
    {
        foreach ($stocks as $stockId) {
            $vendingMachineStock = VendingMachineStock::where('vending_machine_id', $vendingMachineId)
                ->where($column, $stockId)
                ->first();
    
            if ($vendingMachineStock) {
                $vendingMachineStock->old_quantity -= $vendingMachineStock->quantity;
                $vendingMachineStock->quantity -= 1;
    
                // Prevent negative stock values
                if ($vendingMachineStock->quantity < 0) {
                    throw new \Exception("Stock quantity for $column: $stockId cannot be negative.");
                }
    
                $vendingMachineStock->save();
            } else {
                throw new \Exception("Stock not found for $column: $stockId.");
            }
        }
    }    

    public static function allVendingMachineStocks( $request ) {

        // $vendingmachines = VendingMachineStock::select( 'vending_machine_stocks.*');
        $vendingmachines = VendingMachine::select( 'vending_machines.*');

        $filterObject = self::filter( $request, $vendingmachines );
        $vendingmachine = $filterObject['model'];
        $filter = $filterObject['filter'];

        if ( $request->input( 'order.0.column' ) != 0 ) {
            $dir = $request->input( 'order.0.dir' );
            switch ( $request->input( 'order.0.column' ) ) {
                case 2:
                    $vendingmachine->orderBy( 'vending_machines.created_at', $dir );
                    break;
                case 2:
                    $vendingmachine->orderBy( 'vending_machines.title', $dir );
                    break;
                case 3:
                    $vendingmachine->orderBy( 'vending_machines.description', $dir );
                    break;
            }
        }

        $vendingmachineCount = $vendingmachine->count();

        $limit = $request->length;
        $offset = $request->start;

        $vendingmachines = $vendingmachine->skip( $offset )->take( $limit )->get();

        if ( $vendingmachines ) {

            $vendingmachines->each(function ($vendingMachine) {
                $vendingMachine->froyo_stock = VendingMachineStock::getFroyoStock($vendingMachine->id);
                $vendingMachine->syrup_stock = VendingMachineStock::getSyrupStock($vendingMachine->id);
                $vendingMachine->topping_stock = VendingMachineStock::getToppingStock($vendingMachine->id);
                $vendingMachine->append(['encrypted_id', 'image_path']);
            });
        }

        $totalRecord = VendingMachineStock::count();

        $data = [
            'vending_machine_stocks' => $vendingmachines,
            'draw' => $request->draw,
            'recordsFiltered' => $filter ? $vendingmachineCount : $totalRecord,
            'recordsTotal' => $totalRecord,
        ];

        return response()->json( $data );
              
    }

    private static function filter( $request, $model ) {

        $filter = false;

        if ( !empty( $request->title ) ) {
            $model->where( 'vending_machine_stocks.title', 'LIKE', '%' . $request->title . '%' );
            $filter = true;
        }

        if ( !empty( $request->id ) ) {
            $model->where( 'vending_machine_stocks.id', '!=', Helper::decode($request->id) );
            $filter = true;
        }

        if (!empty($request->parent_vending_machine_stock)) {
            $model->whereHas('parent', function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->parent_vending_machine_stock . '%');
            });
            $filter = true;
        }

        if ( !empty( $request->status ) ) {
            $model->where( 'status', $request->status );
            $filter = true;
        }

        if ( !empty( $request->custom_search ) ) {
            $model->where( 'title', 'LIKE', '%' . $request->custom_search . '%' );
            $filter = true;
        }
        
        return [
            'filter' => $filter,
            'model' => $model,
        ];
    }

    public static function oneVendingMachineStock( $request ) {

        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        $vendingmachine = VendingMachine::with(['stocks.syrup','stocks.froyo','stocks.topping'])->find( $request->id );

        // $vendingmachine->append( ['encrypted_id','image_path'] );
        
        return response()->json( $vendingmachine );
    }

    public static function deleteVendingMachineStock( $request ){
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );
        
        $validator = Validator::make( $request->all(), [
            'id' => [ 'required' ],
        ] );
            
        $attributeName = [
            'id' => __( 'vending_machine_stock.id' ),
        ];
            
        foreach( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();

        DB::beginTransaction();

        try {
            VendingMachineStock::find($request->id)->delete($request->id);
            
            DB::commit();
        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_deleted', [ 'title' => Str::singular( __( 'template.vending_machine_stocks' ) ) ] ),
        ] );
    }

    public static function updateVendingMachineStockStatus( $request ) {
        
        $request->merge( [
            'id' => Helper::decode( $request->id ),
        ] );

        DB::beginTransaction();

        try {

            $updateVendingMachineStock = VendingMachineStock::find( $request->id );
            $updateVendingMachineStock->status = $updateVendingMachineStock->status == 10 ? 20 : 10;

            $updateVendingMachineStock->save();
            DB::commit();

            return response()->json( [
                'data' => [
                    'vending_machine_stock' => $updateVendingMachineStock,
                    'message_key' => 'update_vending_machine_stock_success',
                ]
            ] );

        } catch ( \Throwable $th ) {

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
                'message_key' => 'create_vending_machine_stock_failed',
            ], 500 );
        }
    }

    public static function removeVendingMachineStockGalleryImage( $request ) {

        $updateFarm = VendingMachineStock::find( Helper::decode($request->id) );
        $updateFarm->image = null;
        $updateFarm->save();

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'farm.galleries' ) ) ] ),
        ] );
    }
}