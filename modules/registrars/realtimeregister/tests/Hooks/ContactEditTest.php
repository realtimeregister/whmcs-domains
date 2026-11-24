<?php

namespace Tests\Hooks;

use PHPUnit\Framework\TestCase;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\Hooks\ContactEdit;

/**
 * Only an incomplete contact may be skipped on save, other API errors must still fail (#251)
 */
class ContactEditTest extends TestCase
{
    public function testConstraintViolationIsRecognised()
    {
        $this->assertTrue(ContactEdit::isConstraintViolation(new BadRequestException(
            'Bad Request: {"violations":[{"value":"","field":"voice","message":"Invalid e164a phone number"}],'
            . '"message":"Constraint violation","type":"ConstraintViolationException"}'
        )));
    }

    public function testOtherErrorsAreNotConstraintViolations()
    {
        $this->assertFalse(ContactEdit::isConstraintViolation(
            new BadRequestException('Bad Request: {"message":"Contact exists","type":"ObjectExists"}')
        ));
        $this->assertFalse(ContactEdit::isConstraintViolation(new BadRequestException('Bad Request: not json')));
    }
}
