<?php

namespace App\Data\ElectronicServices\PurchaseRequests;


class EmployeePurchaseRequestsData
{
    public string                     $item_name;
    public ?string                    $item_description;
    public ?int                       $item_quantity;
    public int                        $purchase_category_id;

    public function __construct(array $data)
    {
        $this->item_name              = $data['item_name'];
        $this->item_description       = $data['item_description'] ?? null;
        $this->item_quantity          = !empty($data['item_quantity']) ? (int) $data['item_quantity'] : null;
        $this->purchase_category_id   = $data['purchase_category_id'];
    }

    public function toArray(): array
    {
        return [
            'item_name'              => $this->item_name,
            'item_description'       => $this->item_description,
            'item_quantity'          => $this->item_quantity,
            'purchase_category_id'   => $this->purchase_category_id,
        ];
    }
}
