<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Crypt;

trait Encryptable
{

    // Encrypt attributes automatically
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable) && !is_null($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    // Decrypt attributes automatically
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable) && !is_null($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (Exception $e) {
                return $value;
            }
        }

        return $value;
    }
}
