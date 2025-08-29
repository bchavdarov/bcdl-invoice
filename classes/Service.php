<?php
namespace BCDL\Invoice;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Service {
    public string $description;
    public string $measure;       // e.g., "hours", "pcs", "kg"
    public float $quantity;
    public float $unitPrice;      // price per unit, excluding tax
    public float $taxRate;        // in percent, e.g., 20 for 20%
    public float $discount;       // absolute amount, applied before tax

    public function __construct(
        string $description,
        string $measure = '',
        float $quantity = 0.0,
        float $unitPrice = 0.0,
        float $taxRate = 0.0,
        float $discount = 0.0
    ) {
        $this->description = $description;
        $this->measure = $measure;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->taxRate = $taxRate;
        $this->discount = $discount;
    }

    // Net total before tax and after discount
    public function getNetTotal(): float {
        return max(0, $this->quantity * $this->unitPrice - $this->discount);
    }

    // Tax amount for this line
    public function getTaxAmount(): float {
        return $this->getNetTotal() * $this->taxRate / 100;
    }

    // Total amount payable for this line (net + tax)
    public function getPayableAmount(): float {
        return $this->getNetTotal() + $this->getTaxAmount();
    }

    public function toArray(): array {
        return [
            'description'   => $this->description,
            'measure'       => $this->measure,
            'quantity'      => $this->quantity,
            'unitPrice'     => $this->unitPrice,
            'discount'      => $this->discount,
            'taxRate'       => $this->taxRate,
            'netTotal'      => $this->getNetTotal(),
            'taxAmount'     => $this->getTaxAmount(),
            'payableAmount' => $this->getPayableAmount(),
        ];
    }
}
