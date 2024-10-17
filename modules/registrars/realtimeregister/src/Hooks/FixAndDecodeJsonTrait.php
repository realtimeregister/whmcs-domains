<?php

namespace RealtimeRegisterDomains\Hooks;

trait FixAndDecodeJsonTrait
{
    private function fixAndDecodeJson(string $input)
    {
        // Decode any HTML entities (like &amp;)
        $decoded = htmlspecialchars_decode($input, ENT_QUOTES);

        // Recursively check for any remaining encoded entities
        if ($decoded !== $input) {
            return $this->fixAndDecodeJson($decoded);
        }

        // Return the final decoded JSON array as an associative array
        return json_decode($decoded, true);
    }
}
