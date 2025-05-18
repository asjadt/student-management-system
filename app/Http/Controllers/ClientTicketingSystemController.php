<?php

namespace App\Http\Controllers;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Illuminate\Http\Request;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\RSA;


class ClientTicketingSystemController extends Controller
{
    protected $senderPrivateKeyPath;
    protected $receiverPublicKeyPath;

    public function __construct()
    {
        $this->senderPrivateKeyPath = storage_path('keys/sender_private.pem');
        $this->receiverPublicKeyPath = storage_path('keys/receiver_public.pem');

        // Optionally generate keys here if missing (better to generate keys separately and copy manually)
        if (!file_exists($this->senderPrivateKeyPath) || !file_exists($this->receiverPublicKeyPath)) {
            abort(500, 'Keys are missing. Please generate and place keys manually.');
        }
    }

    /**
     * @OA\Get(
     *      path="/get-ticket-token",
     *      operationId="getTicketToken",
     *      tags={"ticketing_client"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get encrypted JWT ticket token",
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=500, description="Server error"),
     * )
     */
    public function getTicketToken(Request $request)
    {
        $user = auth()->user();
        $business = $user->business;

        // Read keys
        $senderPrivateKey = file_get_contents($this->senderPrivateKeyPath);
        $receiverPublicKey = file_get_contents($this->receiverPublicKeyPath);

        // Build JWT signed by sender's private key
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($senderPrivateKey),
            InMemory::plainText('') // No public key needed for signing
        );

        $now = new DateTimeImmutable();


        $token = $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify('+5 minutes'))
            // ->expiresAt($now->modify('+20 seconds'))
            ->withClaim('ticketing_system_user_id', $user->id)
            ->withClaim('ticketing_system_name', $user->full_name)
            ->withClaim('ticketing_system_email', $user->email)
            ->withClaim('ticketing_system_business_id', $business->id ?? null)
            ->withClaim('ticketing_system_business_name', $business->name ?? null)
            ->withClaim('ticketing_system_business_identifier_prefix', $business->identifier_prefix ?? null)
            ->withClaim('ticketing_system_business_web_page', $business->web_page ?? null)
            ->withClaim('ticketing_system_business_phone', $business->phone ?? null)
            ->withClaim('ticketing_system_business_email', $business->email ?? null)
            ->withClaim('ticketing_system_business_address_line_1', $business->address_line_1 ?? null)
            ->withClaim('ticketing_system_business_address_line_2', $business->address_line_2 ?? null)
            ->withClaim('ticketing_system_business_city', $business->city ?? null)
            ->withClaim('ticketing_system_business_country', $business->country ?? null)
            ->withClaim('ticketing_system_business_postcode', $business->postcode ?? null)
            ->withClaim('ticketing_system_business_currency', $business->currency ?? null)
            ->withClaim('ticketing_system_business_logo', env("APP_URL") . $business->logo ?? null)
            ->withClaim('ticketing_system_app_id', env('APP_ID'))
            ->withClaim('ip', $request->ip())
            ->getToken($config->signer(), $config->signingKey());


        $jwtString = $token->toString();

        // --- Hybrid encryption starts here ---

        // 1. Generate AES key & IV
        $aesKey = random_bytes(32); // AES-256 key
        $iv = random_bytes(16);     // AES block size

        // 2. Encrypt JWT string with AES-256-CBC
        $encryptedJwt = openssl_encrypt($jwtString, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
        if ($encryptedJwt === false) {
            Log::error('AES encryption failed');
            return response()->json(['error' => 'Encryption failed'], 500);
        }

        // 3. Encrypt AES key with receiver's RSA public key
        if (!openssl_public_encrypt($aesKey, $encryptedAesKey, $receiverPublicKey)) {
            Log::error('RSA encryption of AES key failed');
            return response()->json(['error' => 'Encryption failed'], 500);
        }

        // 4. Base64 encode encrypted pieces
        $payload = [
            'encrypted_key' => base64_encode($encryptedAesKey),
            'iv' => base64_encode($iv),
            'encrypted_token' => base64_encode($encryptedJwt),
        ];

        return response()->json($payload);
    }
}
