<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class EncryptDecryptMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Decrypt and decode incoming data
        $requestData = $request->all();
        $decryptedData = $this->decodeAndDecrypt($requestData['response']);
//        dd($decryptedData);


        // Update the request with decrypted data
        $request->replace((array)$decryptedData);

        // Handle the request
        $response = $next($request);

        // Encrypt and encode outgoing data
        $responseData = $response->original;
        $encryptedResponse = $this->encryptAndEncode($responseData);

        // Update the response with encrypted data
        $response->setContent(['encrypted_response' => $encryptedResponse]);

        return $response;
    }

    protected function encryptAndEncode($data): string
    {
        $encryptionKey = config('constants.encryption_key');
        $encryptedData = encrypt($data, $encryptionKey);
        return base64_encode($encryptedData);
    }

    protected function decodeAndDecrypt($data)
    {
        $encryptionKey = config('constants.encryption_key');
//        dd($data,$encryptionKey);
        $decryptedData = decrypt(base64_decode($data), $encryptionKey);
        dd($decryptedData);
        return base64_encode($decryptedData);
//        return is_array($decryptedData) ? $decryptedData : [];
//        return json_decode($decryptedData, true);
    }
}
