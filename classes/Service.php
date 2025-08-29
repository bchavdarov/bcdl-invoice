<?php
namespace BCDL\Invoice;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Service {
    public string $description;
    public string $measure;   // e.g., "hours", "pcs", "kg"
    public float $quantity;
    public float $unitPrice;

    public function __construct(
        string $description,
        string $measure = '',
        float $quantity = 0.0,
        float $unitPrice = 0.0
    ) {
        $this->description = $description;
        $this->measure = $measure;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function getTotal(): float {
        return $this->quantity * $this->unitPrice;
    }

    public function toArray(): array {
        return [
            'description' => $this->description,
            'measure'     => $this->measure,
            'quantity'    => $this->quantity,
            'unitPrice'   => $this->unitPrice,
            'total'       => $this->getTotal(),
        ];
    }
}
