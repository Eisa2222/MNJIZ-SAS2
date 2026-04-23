<?php

namespace App\Services\OperationsCenter\Offer;

use App\Data\OperationsCenter\Offer\OfferData;
use App\Data\OperationsCenter\Offer\OfferUpdateData;
use App\Enums\OperationsCenter\Offer\OfferStatus;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\OperationsCenter\Offer\Offers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfferService
{
    /**
     * Create a new offer.
     *
     * @param OfferData $dto
     * @return Offers
     */
    public function createOffer(OfferData $dto): Offers
    {
        return DB::transaction(function () use ($dto) {

            $customer = Customers::findOrFail($dto->customerId);

            $data = $dto->toArray() + [
                'status'                  => OfferStatus::PENDING,
                'offer_number'            => $this->generateOfferNumber(),
                'relationship_manager_id' => $customer->relationship_manager_id,
                'created_by'              => Auth::user()->employee->id ?? null,
            ];

            return Offers::create($data);
        });
    }



    /**
     *
     * @param  Offers    $offer
     * @param  OfferUpdateData $dto
     * @return Offers
     */
    public function updateOffer(Offers $offer, OfferUpdateData $dto): Offers
    {
        return DB::transaction(function () use ($offer, $dto) {

            $customer = Customers::findOrFail($dto->customerId);

            $data = $dto->toArray() + [
                'relationship_manager_id' => $customer->relationship_manager_id,
                'updated_by'              => Auth::user()->employee->id ?? null,
            ];

            $offer->update($data);
            return $offer;
        });
    }




    /*
    |============================================================================
    |============================================================================
    |                           Private methods
    |============================================================================
    |============================================================================
    */
    private function generateOfferNumber(): string
    {
        $currentYear = Carbon::now()->format('y');

        $lastOffer = Offers::withTrashed()->where('offer_number', 'like', "Q$currentYear-%")
            ->orderBy('offer_number', 'desc')
            ->first();

        $lastSerial = $lastOffer ? (int)substr($lastOffer->offer_number, -3) : 0;

        $newSerial = str_pad($lastSerial + 1, 3, '0', STR_PAD_LEFT);

        return "Q{$currentYear}-{$newSerial}";
    }
}