<?php

namespace Marvel\Console;

use BitPayKeyUtils\KeyHelper\PrivateKey;
use BitPayKeyUtils\Storage\EncryptedFilesystemStorage;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;

class GenerateBitPayConfig extends Command
{
    protected $signature = 'marvel:generate_bitpay_config';

    protected $description = 'Generating configuration file for BitPay......';
    public function handle()
    {
        $this->comment($this->description);
        try {
            // Set to true if the environment for which the configuration file will be generated is Production.
            // Will be set to Test otherwise
            $isProd = config('shop.bitpay.mode');

            $privateKeyname = config('shop.bitpay.private_key_path'); // Add here the name for your Private key

            $generateMerchantToken = config('shop.bitpay.enable_merchant'); // Set to true to generate a token for the Merchant facade
            $generatePayoutToken =  config('shop.bitpay.enable_payout'); // Set to true to generate a token for the Payout facade (Request to Support if you need it)

            $yourMasterPassword = config('shop.bitpay.private_key_secret'); //Will be used to encrypt your PrivateKey

            $generateJSONfile = config('shop.bitpay.generate_json'); // Set to true to generate the Configuration File in Json format
            $generateYMLfile =  config('shop.bitpay.generate_yml'); // Set to true to generate the Configuration File in Yml format

            $proxy = null; // The url of your proxy to forward requests through. Example: http://********.com:3128

            /**
             * Generate new private key.
             * Make sure you provide an easy recognizable name to your private key
             * NOTE: In case you are providing the BitPay services to your clients,
             *       you MUST generate a different key per each of your clients
             *
             * WARNING: It is EXTREMELY IMPORTANT to place this key files in a very SECURE location
             **/
            $privateKey = new PrivateKey($privateKeyname);
            $storageEngine = new EncryptedFilesystemStorage($yourMasterPassword);

            try {
                //  Use the EncryptedFilesystemStorage to load the Merchant's encrypted private key with the Master Password.
                $privateKey = $storageEngine->load($privateKeyname);
            } catch (Exception $ex) {
                //  Check if the loaded keys is a valid key
                if (!$privateKey->isValid()) {
                    $privateKey->generate();
                }

                //  Encrypt and store it securely.
                //  This Master password could be one for all keys or a different one for each Private Key
                $storageEngine->persist($privateKey);
            }

            /**
             * Generate the public key from the private key every time (no need to store the public key).
             **/
            try {
                $publicKey = $privateKey->getPublicKey();
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }

            /**
             * Derive the SIN from the public key.
             **/
            try {
                $sin = $publicKey->getSin()->__toString();
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }

            /* Setting the base URL for the BitPay API depending on whether the environment is
            production or not. If `` is true, it sets the base URL to `'https://bitpay.com'`,
            otherwise it sets it to `'https://test.bitpay.com'`. */

            $baseUrl = $isProd ? 'https://bitpay.com' : 'https://test.bitpay.com';
            $env = $isProd ? 'Prod' : 'Test';


            $merchantToken = null;
            $payoutToken = null;


            $this->newLine();

            /**
             * Request a token for the Merchant facade
             */

            try {
                if ($generateMerchantToken) {
                    $resultData = $this->getResponseDataFromBitPay('merchant', $sin, $baseUrl, $publicKey, $privateKey);
                    $merchantToken = $resultData['data'][0]['token'];
                    $this->makeTable('merchant', $resultData);

                    $this->components->info("Please, Go to the following link to approve your BitPay API Token.");
                    $formattedUrl = '<fg=yellow;options=bold>' . $baseUrl . "/api-access-request?pairingCode=" . $resultData['data'][0]['pairingCode'] . '</>';
                    $this->components->info($formattedUrl);
                    $this->components->info("Once you have this Pairing Code approved you can start using the Client.");
                }

                /**
                 * Repeat the process for the Payout facade
                 */

                if ($generatePayoutToken) {
                    $resultData = $this->getResponseDataFromBitPay('payout', $sin, $baseUrl, $publicKey, $privateKey);
                    $payoutToken = $resultData['data'][0]['token'];
                    $this->makeTable('payout', $resultData);

                    $this->components->info("Please, Go to the following link to approve your BitPay API Token.");
                    $formattedUrl = '<fg=yellow;options=bold>' . $baseUrl . "/api-access-request?pairingCode=" . $resultData['data'][0]['pairingCode'] . '</>';
                    $this->components->info($formattedUrl);
                    $this->components->info("Once you have this Pairing Code approved you can start using the Client.");
                }
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
                exit;
            }


            /**
             * Generate Config File
             */

            $config = [
                "BitPayConfiguration" => [
                    "Environment" => $env,
                    "EnvConfig"   => [
                        'Test' => [
                            "PrivateKeyPath"   => $isProd ? null : $privateKeyname,
                            "PrivateKeySecret" => $isProd ? null : $yourMasterPassword,
                            "ApiTokens"        => [
                                "merchant" => $isProd ? null : $merchantToken,
                                "payout"  => $isProd ? null : $payoutToken,
                            ],
                            "Proxy" => $proxy,
                        ],
                        'Prod' => [
                            "PrivateKeyPath"   => $isProd ? $privateKeyname : null,
                            "PrivateKeySecret" => $isProd ? $yourMasterPassword : null,
                            "ApiTokens"        => [
                                "merchant" => $isProd ? $merchantToken : null,
                                "payout"  => $isProd ? $payoutToken : null,
                            ],
                            "Proxy" => $proxy,
                        ],
                    ],
                ],
            ];

            try {
                if ($generateJSONfile) {
                    $json_data = json_encode($config, JSON_PRETTY_PRINT);
                    file_put_contents(config('shop.bitpay.config_file.json'), $json_data);
                }

                if ($generateYMLfile) {
                    $yml_data = Yaml::dump($config, 8, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
                    file_put_contents(config('shop.bitpay.config_file.yml'), $yml_data);
                }

                $this->components->info('<fg=green>BitPay config file has been generated Successfully to:</> <fg=default>storage/app/private</>');
                
                $this->newLine();
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }


    /**
     * This function sends a POST request to the BitPay server with specific headers and data, and
     * returns the response data as an array.
     * 
     * @param string facade The type of API request being made to BitPay. It could be "merchant" for
     * merchant API requests or "payroll" for payroll API requests.
     * @param sin The sin parameter is an identifier for the request being made to the BitPay server.
     * It is used to track the request and its associated response.
     * @param baseUrl The base URL of the BitPay API.
     * @param publicKey The public key used for authentication with the BitPay server.
     * @param privateKey The private key used for signing the request to BitPay API.
     * 
     * @return array An array of response data from BitPay.
     */
    private function getResponseDataFromBitPay(string $facade, $sin, $baseUrl, $publicKey, $privateKey): array
    {

        $postData = json_encode(
            [
                'id'     => $sin,
                'facade' => $facade,
                'label'  => config('app.name') . '_BITPAY_' . strtoupper($facade) . '_LABEL_' . now()->timestamp,
            ]
        );

        $curlCli = curl_init($baseUrl . "/tokens");

        curl_setopt(
            $curlCli,
            CURLOPT_HTTPHEADER,
            [
                'x-accept-version: 2.0.0',
                'Content-Type: application/json',
                'x-identity'  => $publicKey->__toString(),
                'x-signature' => $privateKey->sign($baseUrl . "/tokens" . $postData),
            ]
        );

        curl_setopt($curlCli, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlCli, CURLOPT_POSTFIELDS, stripslashes($postData));
        curl_setopt($curlCli, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlCli);
        $httpcode = curl_getinfo($curlCli, CURLINFO_HTTP_CODE);
        if ($httpcode == 403) {
            $this->components->error('Could not make contact to BitPay Server. Response code: ' . $httpcode);
            exit;
        }
        $resultData = json_decode($result, true);
        curl_close($curlCli);

        if (array_key_exists('error', $resultData)) {
            $this->error($resultData['error']);
            exit;
        }
        return $resultData;
    }

    private function makeTable(string $facade, $params)
    {

        $array = [
            ["key"   => "Label",                     "value" => $params['data'][0]['label']],
            ["key"   => "Facade",                     "value" => $facade],
            ["key"   => "<fg=yellow>Token</>",         "value" => '<info>' . $params['data'][0]['token'] . '</info>'],
            ["key"   => "<fg=yellow>PairingCode</>",   "value" => '<info>' . $params['data'][0]['pairingCode'] . '</info>']
        ];
        $this->table(['key', 'Value'], $array);
        $this->newLine();
    }
}
