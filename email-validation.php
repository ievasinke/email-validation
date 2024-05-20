<?php
/*
 * Use https://emailvalidation.io/ API to create an application that
 * allows to enter the email address and return if its valid or not.
 *
 * Send an email with basic "hello world" information to validated email
 * You can use anything from different APis, mailgun, sparkpost, gmail, smtp services etc.
 * TODO export API_KEY_EMAIL=yourownkey
 * TODO export API_KEY_MAILGUN=yourownkey
 */

require 'vendor/autoload.php';
use Mailgun\Mailgun;

$appKeyValidation = getenv("API_KEY_EMAIL");
$emailToValidate = (string)readline("Enter the email address for validation: ");
$emailUrl = "https://api.emailvalidation.io/v1/info?apikey=$appKeyValidation&email=$emailToValidate";

$contextOptions = [
    "http" => [
        "ignore_errors" => true,
    ],
];
$context = stream_context_create($contextOptions);
$data = file_get_contents($emailUrl, false, $context);
$http_response_header = $http_response_header ?? [];

if (strpos($http_response_header[0], '200') === false) {
    exit ("Error: Failed to fetch data. HTTP Response: " . $http_response_header[0] . "\n");
}

$emailsData = json_decode($data);
$apiKeyMailgun = getenv("API_KEY_MAILGUN");
$domain = "sandbox139eb76be3f444ad90f13165a0df1cf0.mailgun.org";
echo "Email validity score: $emailsData->score\n";

if ($emailsData->format_valid) {
    try {
        $mg = Mailgun::create($apiKeyMailgun);

        $result = $mg->messages()->send($domain, [
            'from' => 'Mailgun Sandbox <postmaster@sandbox139eb76be3f444ad90f13165a0df1cf0.mailgun.org>',
            'to' => $emailToValidate,
            'subject' => 'The PHP is awesome!',
            'text' => 'hello world'
        ]);
    } catch (\Mailgun\Exception\HttpClientException $e) {
        echo "Mailgun API Exception: " . $e->getMessage();
    } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
        echo "Mailgun API Exception Interface: " . $e->getMessage();
    }
}