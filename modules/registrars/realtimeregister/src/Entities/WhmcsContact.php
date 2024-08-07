<?php

namespace RealtimeRegister\Entities;

use libphonenumber\PhoneNumberUtil;
use SandwaveIo\RealtimeRegister\Domain\Contact as RtrContact;

class WhmcsContact
{
    public function __construct(
        public ?int $id,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $companyName = null,
        public ?string $email = null,
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postCode = null,
        public ?string $countryCode = null,
        public ?string $phoneNumber = null,
    ) {
    }

    public static function make(DataObject $data): WhmcsContact
    {
        return new WhmcsContact(
            id: $data->contactid,
            firstName: $data->firstname,
            lastName: $data->lastname,
            companyName: $data->companyname,
            email: $data->email,
            address1: $data->address1,
            address2: $data->address2,
            city: $data->city,
            state: $data->state,
            postCode: $data->postcode,
            countryCode: $data->country,
            phoneNumber: $data->phonenumber
        );
    }

    public function diff(RtrContact $rtrContact, DataObject $whmcsContact): array
    {
        $diff = [];

        $currentContact = $rtrContact->toArray();

        foreach (['name', 'addressLine', 'postalCode', 'city', 'country', 'email', 'voice'] as $field) {
            if ($currentContact[$field] != $whmcsContact[$field]) {
                $diff[$field] = $whmcsContact[$field];
            }
        }

        foreach (['organization', 'state', 'fax'] as $field) {
            if ($currentContact[$field] && !$whmcsContact[$field]) {
                $diff[$field] = '';
            } elseif ($currentContact[$field] != $whmcsContact[$field] && $whmcsContact[$field]) {
                $diff[$field] = $whmcsContact[$field];
            }
        }

        return $diff;
    }

    public function toRtrArray(bool $organizationAllowed): DataObject
    {
        $data = [
            'organization' => $this->companyName,
            'name' => trim(sprintf('%s %s', $this->firstName, $this->lastName)),
            'addressLine' => array_values(array_filter([$this->address1, $this->address2])),
            'postalCode' => $this->postCode,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->countryCode,
            'email' => $this->email,
            'voice' => $this->phoneNumberFormatted(),
        ];

        if (!$organizationAllowed) {
            unset($data['organization']);
        }

        return new DataObject($data);
    }

    public function phoneNumberFormatted(): ?string
    {
        if (!$this->phoneNumber) {
            return $this->phoneNumber;
        }

        return WhmcsContact::formatE164a($this->phoneNumber, $this->countryCode);
    }

    public static function formatE164a(string $number, ?string $country = null): ?string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $phoneNumber = $phoneNumberUtil->parse($number, $country);

        return '+' . $phoneNumber->getCountryCode() . '.'
            . $phoneNumberUtil->getNationalSignificantNumber($phoneNumber);
    }
}
