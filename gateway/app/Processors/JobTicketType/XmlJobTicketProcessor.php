<?php

namespace App\Processors\JobTicketType;

use App\Contracts\JobTicketAbstract;
use App\Http\Resources\Items\ItemResource;
use App\Models\Domain;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class XmlJobTicketProcessor extends JobTicketAbstract
{

   /**
    * @param Order  $order
    * @param Item   $item
    * @param string $iso
    * @param string $tenant
    * @return \Illuminate\Http\Response|JsonResponse
    */
   public function format(
      Order  $order,
      Item   $item,
      string $iso,
      string $tenant
   ) {
      /**
       * need Check on this
       */

      if ($supplier = Domain::findByFqdn($item->supplierName)->first()) {
         return response()->view('job_tickets.job_ticket_xml', [
            'order' => $order,
            'item' => ItemResource::make($item),
            'iso' => $iso,
            'supplier' => (object)['name' => $supplier->fqdn, 'supplier_id' => $item->supplier_id],
         ])->header('Content-Type', 'text/xml');
      }
      return response()->json([
         'message' => __("Sorry!, We couldn't find this Supplier"),
         'status' => Response::HTTP_NOT_FOUND
      ], Response::HTTP_NOT_FOUND);
   }

    /**
     * @param Order $order
     * @param string $iso
     * @param string $format
     * @return \Illuminate\Http\Response|JsonResponse
     */
   public static function formatOrder(
      Order  $order,
      string $iso,
      string $format = 'xml'
   ) {
      switch ($format) {
         case 'xml':
            // return xml view
            return response()->view('job_tickets.job_ticket_xml', [
               'order' => $order,
               'iso' => $iso,
            ])->header('Content-Type', 'text/xml');

         case 'html':
            // return html view
            return response()->view('job_tickets.job_ticket_html', [
               'order' => $order,
               'iso' => $iso,
            ]);

         case 'pdf':
            // return pdf view
            return Pdf::loadView('job_tickets.job_ticket_pdf', [
               'order' => $order,
               'iso' => $iso
            ])->stream();

         default:
            return response()->json([
               'message' => __('unknown format'),
               'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
      }
   }
}
