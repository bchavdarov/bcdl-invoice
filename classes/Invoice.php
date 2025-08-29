<?php
namespace BCDL\Invoice;

use DateTime;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Invoice {
    public ?int $id = null;
    public string $documentName = 'Invoice';
    public Party $supplier;
    public Party $customer;
    public string $number; // always 10 digits, zero-padded
    public DateTime $issueDate;
    public DateTime $eventDate;
    public ?DateTime $dueDate = null;

    /** @var Service[] */
    public array $services = [];

    public function __construct(
        Party $supplier,
        Party $customer,
        string $number,
        ?DateTime $eventDate = null
    ) {
        $this->supplier = $supplier;
        $this->customer = $customer;
        $this->number   = str_pad($number, 10, '0', STR_PAD_LEFT);
        $this->issueDate = new DateTime();
        $this->eventDate = $eventDate ?? new DateTime();
    }

    public function addService(Service $service): void {
        $this->services[] = $service;
    }

    public function removeService(int $index): void {
        if (isset($this->services[$index])) {
            unset($this->services[$index]);
            $this->services = array_values($this->services);
        }
    }

    public function getTaxBase(): float {
        return array_reduce($this->services, fn($sum, $s) => $sum + $s->getNetTotal(), 0.0);
    }

    public function getTaxAmount(): float {
        return array_reduce($this->services, fn($sum, $s) => $sum + $s->getTaxAmount(), 0.0);
    }

    public function getGrandTotal(): float {
        return $this->getTaxBase() + $this->getTaxAmount();
    }

    public function getServiceCount(): int {
        return count($this->services);
    }

    public function addServiceFromArray(array $data): void {
        $service = new Service(
            $data['description'] ?? '',
            $data['measure'] ?? '',
            (float)($data['quantity'] ?? 0),
            (float)($data['unitPrice'] ?? $data['unit_price'] ?? 0),
            (float)($data['taxRate'] ?? 0),
            (float)($data['discount'] ?? 0)
        );
        $this->addService($service);
    }

    public function toArray(): array {
        return [
            'id'           => $this->id,
            'documentName' => $this->documentName,
            'supplier'     => $this->supplier->toArray(),
            'customer'     => $this->customer->toArray(),
            'number'       => $this->number,
            'issueDate'    => $this->issueDate->format('Y-m-d'),
            'eventDate'    => $this->eventDate->format('Y-m-d'),
            'dueDate'      => $this->dueDate?->format('Y-m-d'),
            'services'     => array_map(fn($s) => $s->toArray(), $this->services),
            'taxBase'      => $this->getTaxBase(),
            'taxAmount'    => $this->getTaxAmount(),
            'grandTotal'   => $this->getGrandTotal(),
        ];
    }
}
