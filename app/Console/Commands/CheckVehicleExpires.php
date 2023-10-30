<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\{
    AdministratorNotification,
    Vehicle
};

use Carbon\Carbon;

class CheckVehicleExpires extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:vehicle-expires';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check vehicle\'s road tax, insurance, permit expiry date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->timezone( 'Asia/Kuala_Lumpur' )->startOfDay();

        $vehicles = Vehicle::where( 'status', 10 )->get();

        foreach ( $vehicles as $vehicle ) {

            if ( $vehicle->road_tax_expiry_date ) {

                $roadTaxExpiryDate = Carbon::createFromFormat( 'Y-m-d', $vehicle->road_tax_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay();

                $diff = $today->diffInDays( $roadTaxExpiryDate, false );

                $this->info( $diff );

                if( $diff == 30 ) {

                    AdministratorNotification::create( [
                        'module_id' => $vehicle->id,
                        'system_title' => 'notification.vehicles_x_expiring_title',
                        'system_content' => 'notification.vehicles_x_expiring_content',
                        'meta_data' => json_encode( [
                            'plate' => $vehicle->license_plate,
                            'type' => 'notification.road_tax',
                        ] ),
                        'module' => 'App\Models\Vehicle',
                        'type' => 1,
                    ] );
                }
            }

            if ( $vehicle->insurance_expiry_date ) {

                $insuranceExpiryDate = Carbon::createFromFormat( 'Y-m-d', $vehicle->insurance_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay();

                $diff = $today->diffInDays( $insuranceExpiryDate, false );

                $this->info( $diff );

                if( $diff == 30 ) {

                    AdministratorNotification::create( [
                        'module_id' => $vehicle->id,
                        'system_title' => 'notification.vehicles_x_expiring_title',
                        'system_content' => 'notification.vehicles_x_expiring_content',
                        'meta_data' => json_encode( [
                            'plate' => $vehicle->license_plate,
                            'type' => 'notification.insurance',
                        ] ),
                        'module' => 'App\Models\Vehicle',
                        'type' => 1,
                    ] );
                }
            }

            if ( $vehicle->permit_expiry_date ) {

                $permitExpiryDate = Carbon::createFromFormat( 'Y-m-d', $vehicle->permit_expiry_date, 'Asia/Kuala_Lumpur' )->startOfDay();

                $diff = $today->diffInDays( $permitExpiryDate, false );

                $this->info( $diff );

                if( $diff == 30 ) {

                    AdministratorNotification::create( [
                        'module_id' => $vehicle->id,
                        'system_title' => 'notification.vehicles_x_expiring_title',
                        'system_content' => 'notification.vehicles_x_expiring_content',
                        'meta_data' => json_encode( [
                            'plate' => $vehicle->license_plate,
                            'type' => 'notification.permit',
                        ] ),
                        'module' => 'App\Models\Vehicle',
                        'type' => 1,
                    ] );
                }
            }
        }

        return 0;
    }
}
