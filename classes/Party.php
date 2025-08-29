<?php
// the class for the Parties
namespace BCDL\Invoice;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Party {
    public int $id;
    public string $name;
    public string $address;
    public string $crn; // Company Registration Number
    public string $vat; // Company Vat Number
    public string $mrp; // Materially Responsible Person
    public string $email;
    public string $phone;
    public string $iban;

    public function __construct(
        int $id,
        string $name,
        string $address = '',
        string $crn = '',
        string $vat = '',
        string $mrp = '',
        string $email = '',
        string $phone = '',
        string $iban = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->crn = $crn;
        $this->vat = $vat;
        $this->mrp = $mrp;
        $this->email = $email;
        $this->phone = $phone;
        $this->iban = $iban;
    }

    public function toArray(): array {
    return [
        'id'  => $this->id,
        'name'    => $this->name,
        'address' => $this->address,
        'crn'     => $this->crn,
        'vat'     => $this->vat,
        'mrp'     => $this->mrp,
        'email'   => $this->email,
        'phone'   => $this->phone,
        'iban'    => $this->iban,
    ];
    }

    public function getContactInfo(): string {
        $parts = array_filter([$this->name, $this->mrp, $this->email, $this->phone]);
        return implode(', ', $parts);
    }

    public function getFullAddress(): string {
        $parts = array_filter([$this->name, $this->address]);
        return implode("\n", $parts);
    }

    public function isValidEmail(): bool {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

}