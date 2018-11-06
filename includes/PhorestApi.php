<?php
/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 02/11/2018
 * Time: 13:43
 */
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );

class PhorestApi
{


    //public $username, $password, $branch_id, $business_id, $apiurl;

    public function __construct($username, $password, $branch_id, $business_id, $apiurl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->branch_id = $branch_id;
        $this->business_id = $business_id;
        $this->apiurl = $apiurl;
    }

    // formatting the date for phorest
    function formatDate($date)
    {
        $time = date("H:i:s");
        $n_date = $date . "T" . $time . ".000Z";
        $new_date = str_replace("/", "-", $n_date);
        return $new_date;
    }

// sending the json object to the phorest api
    function sendJson($uri, $json)
    {

        $this->api_logger($json, 'sendJson');

        $process = curl_init($uri);
        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($process, CURLOPT_HEADER, false);
        curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $json);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        curl_close($process);


        $this->api_logger($return, 'receiveJson');


        return $return;
    }


    function getJson($uri)
    {


        $auth = base64_encode($this->username . ":" . $this->password);
        $context = stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth"
            ]
        ]);

        $response = file_get_contents($uri, false, $context);


        $this->api_logger($response, 'getJson');


        return $response;


    }

// format phone number
    function formatMobileNumber($number)
    {
        $phone = $number;
        // irish phone number
        if (substr($phone, 0, 3) === "089" || substr($phone, 0, 3) === "086" || substr($phone, 0, 3) === "087" || substr($phone, 0, 3) === "085") {
            $country_eir_code = '353';
            $phone = substr($phone, 1);
            $phone = $country_eir_code . $phone;
        } // uk number
        elseif (substr($phone, 0, 2) === "07") {
            $country_eir_code = '44';
            $phone = substr($phone, 1);
            $phone = $country_eir_code . $phone;
        } else {
            $phone = '';
        }
        return $phone;
    }

// find a service name by ServiceId
    function getServiceName($serviceId)
    {
        $uri = 'https://' . $this->apiurl . '/third-party-api-server/api/business/' . $this->business_id . '/branch/' . $this->branch_id . '/service';
        // getting a list of the services
        $response = $this->getJson($uri);

        $data = json_decode($response, true);

        foreach ($data["_embedded"]["services"] as $dataitem) {
            if ($dataitem["serviceId"] == $serviceId) {
                $name = $dataitem['name'];
            } else {
                // nothing
            }
        } // end of for loop
        return $name;
    }

// create client
    function createClient($client_date, $email, $name, $lastName, $phone = "", $smsmarketing = false, $emailmarketing = false, $uri)
    {

// creating the array for the new client
        $client_data = array(
            'address' => array(
                'city' => '',
                'country' => '',
                'postalCode' => '',
                'state' => '',
                'streetAddress1' => '',
                'streetAddress2' => ''
            ),
            'birthDate' => '',
            'clientSince' => $client_date,
            'creatingBranchId' => $this->branch_id,
            'creditAccount' => array(
                'creditDays' => 1,
                'creditLimit' => 500.00,
                'outstandingBalance' => 0.00
            ),
            'email' => $email,
            'emailMarketingConsent' => $emailmarketing,
            'firstName' => $name,
            'gender' => 'MALE',
            'landLine' => '',
            'lastName' => $lastName,
            'mobile' => $phone,
            'notes' => 'Client Card Created Through Online Voucher Purchase',
            'photoUrl' => '',
            'preferredStaffId' => '',
            'smsMarketingConsent' => $smsmarketing,
            'version' => 1
        );
        $json_client = json_encode($client_data);
        // sending the json object though the sendJson method
        $client_response = $this->sendJson($uri, $json_client);
        // decoding the response and getting the client id


        $client_response = json_decode($client_response, true);


        return $client_response;
    }

// create the voucher
    function createVoucher($id, $amount, $years_valid)
    {
        $days_valid = (365 * $years_valid);
        $date = date("Y/m/d");
        $issue_date = $this->formatDate($date);
        $e_date = date('Y-m-d', strtotime(date("Y-m-d", current_time('timestamp')) . " + $days_valid day"));
        $expiry_date = $this->formatDate($e_date);
        $voucher_data = array(
            'clientId' => $id,
            'creatingBranchId' => $this->branch_id,
            'expiryDate' => $expiry_date,
            'issueDate' => $issue_date,
            'originalBalance' => $amount
        );

        $json_voucher = json_encode($voucher_data);

        $uri = 'https://' . $this->apiurl . '/third-party-api-server/api/business/' . $this->business_id . '/voucher';

        $voucher_response = $this->sendJson($uri, $json_voucher);


        $voucher = json_decode($voucher_response, true);


        return $voucher;

    }

    function createVoucherClient($phone, $email, $name, $lastName, $sms_marketing, $email_maketing)
    {


        $uri = 'https://' . $this->apiurl . '/third-party-api-server/api/business/' . $this->business_id . '/client';

        $json = $this->getJson($uri);


        $data = json_decode($json, true);

        // loops through the client data to find a match for the mobile number
        foreach ($data["_embedded"]["clients"] as $dataitem) {

            if ($dataitem["mobile"] == $phone) {
                $id = $dataitem['clientId'];
                break;
            }
        }

        if (isset($id) && !empty($id)) {

            return $id;


        } else {


            $date = date("Y/m/d");
            $client_date = $this->formatDate($date);
            $client_response = $this->createClient($client_date, $email, $name, $lastName, $phone, $sms_marketing, $email_maketing, $uri);
            if ($client_response['statusCode'] == '400') {
                //Walk in client so if all goes wrong.
                $client_response = $this->createClient($client_date, $email, $name, $lastName, "", $sms_marketing, $email_maketing, $uri);
            }


            return $client_response['clientId'];


        }


    }


    public function api_logger($data, $file_name)
    {

        $fp = fopen(PHOREST_BASE_DIR . 'assets/api-logs/' . $file_name . '.log', 'a');
        fwrite($fp, $data);
        fclose($fp);

    }
}