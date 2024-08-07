<?php

namespace RealtimeRegister\Enums;

enum ContactType: string
{
    case Registrant = 'registrant';
    case Admin = 'admin';
    case Tech = 'tech';
    case Billing = 'billing';
}
