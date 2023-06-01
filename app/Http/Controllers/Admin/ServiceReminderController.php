<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    ServiceService,
    ServiceReminderService,
    VehicleService,
};

class ServiceReminderController extends Controller
{
    public function index() {

        $this->data['header']['title'] = __( 'template.service_reminders' );
        $this->data['content'] = 'admin.service_reminder.index';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.service_reminders' ),
                'class' => 'active',
            ],
        ];

        return view( 'admin.main' )->with( $this->data );
    }

    public function add( Request $request ) {

        $this->data['header']['title'] = __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.service_reminders' ) ) ] );
        $this->data['content'] = 'admin.service_reminder.add';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.service_reminder.index' ),
                'text' => __( 'template.service_reminders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.add_x', [ 'title' => \Str::singular( __( 'template.service_reminders' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['vehicles'] = VehicleService::get();
        $this->data['data']['services'] = ServiceService::get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function edit( Request $request ) {

        $this->data['header']['title'] = __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.service_reminders' ) ) ] );
        $this->data['content'] = 'admin.service_reminder.edit';
        $this->data['breadcrumb'] = [
            [
                'url' => route( 'admin.dashboard' ),
                'text' => __( 'template.dashboard' ),
                'class' => '',
            ],
            [
                'url' => route( 'admin.service_reminder.index' ),
                'text' => __( 'template.service_reminders' ),
                'class' => '',
            ],
            [
                'url' => '',
                'text' => __( 'template.edit_x', [ 'title' => \Str::singular( __( 'template.service_reminders' ) ) ] ),
                'class' => 'active',
            ],
        ];
        $this->data['data']['vehicles'] = VehicleService::get();
        $this->data['data']['services'] = ServiceService::get();

        return view( 'admin.main' )->with( $this->data );
    }

    public function allServiceReminders( Request $request ) {

        return ServiceReminderService::allServiceReminders( $request );
    }

    public function oneServiceReminder( Request $request ) {

        return ServiceReminderService::oneServiceReminder( $request );
    }

    public function createServiceReminder( Request $request ) {

        return ServiceReminderService::createServiceReminder( $request );
    }

    public function updateServiceReminder( Request $request ) {

        return ServiceReminderService::updateServiceReminder( $request );
    }
}
