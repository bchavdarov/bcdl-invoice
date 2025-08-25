<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// the classes for the invoice

class Party {
    public int $custid;
    public string $name;
    public string $address;
    public string $crn; // Company Registration Number
    public string $vat; // Company Vat Number
    public string $mrp; // Materially Responsible Person
    public string $email;
    public string $phone;

    public function __construct(
        int $custid,
        string $name,
        string $address,
        string $crn,
        string $vat,
        string $mrp,
        string $email,
        string $phone
    ) {
        $this->custid = $custid;
        $this->name = $name;
        $this->address = $address;
        $this->crn = $crn;
        $this->vat = $vat;
        $this->mrp = $mrp;
        $this->email = $email;
        $this->phone = $phone;
    }
}