<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Models\InvoiceEvent;
use App\Models\Session;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;


class invoiceController extends Controller
{

    public function getInvoice(Request $request)
    {
        return response()->json(array('success' => true,
            'status_code' => 200,
            'message' => 'retrieved Successfully',
        ));
    }


    public function createInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "START"       => "required",
            "END"         => "required",
            "CUSTOMER_ID" => "required",
        ]);

        if ($validator->fails()) {
            $msg = $validator->errors()->first();
            return response()->json(['message' => $msg], 400);
        }


        $startDate = date('Y-m-d', strtotime($request->get('START')));
        $endDate =  date('Y-m-d', strtotime($request->get('END')));
        $customerID = $request->get('CUSTOMER_ID');


        $registrationUserCollection = $this->getRegistrationUserCollection($startDate, $endDate, $customerID);
        $activationSessionsCollection = $this->getActivationSessionsCollection($startDate, $endDate, $customerID);
        $appointmentSessionsCollection = $this->getAppointmentSessionsCollection($startDate, $endDate, $customerID);

      
        if ( ($registrationUserCollection->count() or $activationSessionsCollection->count()  or $appointmentSessionsCollection->count()) == 0)
        return "empty events";


        $groupedCollection = $this->mixCollection($registrationUserCollection, $activationSessionsCollection, $appointmentSessionsCollection);

        $eventsWithoutFrequency = $this->removeFrequencyEvents($groupedCollection, $startDate);

       
        if($eventsWithoutFrequency->count() < 0)
            return "no events";

        $InvoicePrice = $eventsWithoutFrequency->sum('price');

        $invoice = Invoice::create([
            'customer_id' => $customerID,
            'price' => $InvoicePrice
        ]);
        

        $eventsWithoutFrequency->each(function ($item) use ($invoice){
            InvoiceEvent::create([
                'user_id' => $item['user_id'],
                'invoice_id' => $invoice['id'],
                'event_type' => $item['event_type'],
                'event_date' => $item['event_date'],
                'price' => $item['price'],
            ]);
        });

        return $invoice['id'];
    }


    public function getRegistrationUserCollection($startDate, $endDate, $customerID){

        $users = User::whereBetween('created_at', [$startDate, $endDate])
        ->where('customer_id', $customerID)
        ->get();

        return new Collection($users);
    }

    public function getActivationSessionsCollection($startDate, $endDate, $customerID){

        $activationSessions = Session::select('sessions.*')
        ->join('users', 'sessions.user_id', '=', 'users.id')
        ->where('users.customer_id', $customerID)
        ->whereBetween('activated', [$startDate, $endDate])
        ->get();

        return new Collection($activationSessions);
    }

    public function getAppointmentSessionsCollection($startDate, $endDate, $customerID){

        $appointmentSessions = Session::select('sessions.*')
        ->join('users', 'sessions.user_id', '=', 'users.id')
        ->where('users.customer_id', $customerID)
        ->whereBetween('appointment', [$startDate, $endDate])
        ->get();

        return new Collection($appointmentSessions);
    }

    public function mixCollection($registrationUserCollection, $activationSessionsCollection, $appointmentSessionsCollection){

        $eventCollection = new Collection();

           // Merge the three collections based on a specific property (e.g., 'id') into the new structured collection
           $registrationUserCollection->each(function ($item) use ($eventCollection) {
            $eventCollection->push([
                'user_id' => $item->id,
                'event_type' => "registration",
                'event_date' => date($item->created_at),
                'price' => 50,
            ]);
        });

        $activationSessionsCollection->each(function ($item) use ($eventCollection) {
            $eventCollection->push([
                'user_id' => $item->user_id,
                'event_type' => "activation",
                'event_date' => $item->activated,
                'price' => 100,
            ]);      
        });

        $appointmentSessionsCollection->each(function ($item) use ($eventCollection) {
            $eventCollection->push([
                'user_id' => $item->user_id,
                'event_type' => "appointment",
                'event_date' => $item->appointment,
                'price' => 200,
                // Add more properties from $collection3 as needed
            ]);
            
        });

        $groupedCollection = $eventCollection->groupBy('user_id');
        
        return $groupedCollection;
    }



    public function removeFrequencyEvents($groupedCollection, $startDate){

        $eventWithoutFrequency  = $groupedCollection->map(function ($items) {
            return $items->sortByDesc('price')->first();
        });

        $sessionData = Session::get();

        $compareWithOldEvent = $eventWithoutFrequency->reject(function ($item) use ($sessionData, $startDate) {
         
            $match = $sessionData->first(function ($sessionItem) use ($item,  $startDate) {
                if($item['event_type'] == 'appointment')
                return $sessionItem->user_id == $item['user_id'] && $sessionItem->appointment < $startDate;
                else{
                    return $sessionItem->user_id == $item['user_id'] && $sessionItem->activated < $startDate;

                }

            });

            return $match !== null;
        });

      return $compareWithOldEvent;
    }
    

}
