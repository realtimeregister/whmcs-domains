<?php

namespace RealtimeRegister\Entities;

use RealtimeRegister\Enums\ContactType;

class Contact
{
    protected static array $mapping = [
        'firstName' => 'firstname',
        'lastName' => 'lastname',
        'companyName' => 'companyname',
        'email' => 'email',
        'address1' => 'address1',
        'address2' => 'address2',
        'city' => 'city',
        'state' => 'state',
        'fullState' => 'fullstate',
        'postCode' => 'postcode',
        'countryCode' => 'countrycode',
        'countryName' => 'countryname',
        'phoneNumber' => 'phonenumber',
        'phoneCountryCode' => 'phonecc',
        'fullPhoneNumber' => 'fullphonenumber',
        'additionalFields' => 'additionalfields',
    ];

    public function __construct(
        public readonly ContactType $type,
        public readonly ?string     $firstName = null,
        public readonly ?string     $lastName = null,
        public readonly ?string     $companyName = null,
        public readonly ?string     $email = null,
        public readonly ?string     $address1 = null,
        public readonly ?string     $address2 = null,
        public readonly ?string     $city = null,
        public readonly ?string     $state = null,
        public readonly ?string     $fullState = null,
        public readonly ?string     $postCode = null,
        public readonly ?string     $countryCode = null,
        public readonly ?string     $countryName = null,
        public readonly ?string     $phoneNumber = null,
        public readonly ?string     $phoneCountryCode = null,
        public readonly ?string     $fullPhoneNumber = null,
        public readonly ?array      $additionalFields = null
    )
    {

    }

    public function diff(WhmcsContact $contact)
    {
        $diff = [];

        foreach (['name', 'addressLine', 'postalCode', 'city', 'country', 'email', 'voice'] as $field) {
            if ($this->{$field} != $new_contact[$field]) {
                $diff[$field] = $new_contact[$field];
            }
        }
    }

    public static function fromLocalApi(ContactType $type, DataObject $data)
    {
        return new Contact(
            type: $type,
            firstName: $data->firstname,
            lastName: $data->lastname,
            companyName: $data->companyname,
            email: $data->email,
            address1: $data->address1,
            address2: $data->address2,
            city: $data->city,
            state: $data->state,
            fullState: $data->state,
            postCode: $data->postcode,
            countryCode: $data->country,
            phoneNumber: $data->phonenumber
        );
    }

    public static function fromArray(ContactType $type, array $data): Contact
    {
        $mapped = [];

        $reflection = new \ReflectionClass(static::class);

        foreach (static::$mapping as $internal => $external) {

            if (!$reflection->hasProperty($internal)) {
                continue;
            }

            $prop = $reflection->getProperty($internal);

            $mapped[$internal] = $data[$external] ?? $prop->getDefaultValue();
        }

        return new Contact($type, ...$mapped);
    }

    public static function fromWhmcs(ContactType $type, array $params): Contact
    {
        $prefix = static::prefix($type);

        $mapped = [];

        $reflection = new \ReflectionClass(static::class);

        foreach (static::$mapping as $internal => $external) {

            if (!$reflection->hasProperty($internal)) {
                continue;
            }

            $prop = $reflection->getProperty($internal);

            $mapped[$internal] = $params[$prefix.$external] ?? $prop->getDefaultValue();
        }

        return new Contact($type, ...$mapped);
    }

    public function toWhmcs(): array
    {
        $prefix = static::prefix($this->type);

        $mapped = [];

        foreach (static::$mapping as $internal => $external) {
            $mapped[$prefix.$external] = $this->{$internal};
        }

        return $mapped;
    }

    protected static function prefix(ContactType $type): string
    {
        return match ($type) {
            ContactType::Admin => 'admin',
            ContactType::Tech => 'tech',
            ContactType::Billing => 'billing',
            default => '',
        };
    }

    public function toArray(): array
    {
        $array = [];

        foreach (static::$mapping as $internal => $external) {
            $array[$internal] = $this->{$internal};
        }

        return $array;
    }
}