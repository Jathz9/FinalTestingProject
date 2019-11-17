<?php namespace Operation;

require_once __DIR__ . './../serviceauthentication/AccountInformationException.php'; 
require_once __DIR__ . './../serviceauthentication/ServiceAuthentication.php';
require_once __DIR__ . './StubDeposit.php';

use AccountInformationException;
use ServiceAuthentication;
use Stub\StubDeposit;

class Transfer
{
    private $srcNumber, $srcName;
    private $service, $depositService;

    public function __construct(string $srcNumber,
        string $srcName,
        ServiceAuthentication $service = null,
        StubDeposit $depositService = null) {

        $this->srcNumber = $srcNumber;
        $this->srcName = $srcName;

        if ($service == null) {
            $this->service = new ServiceAuthentication();
        } else {
            $this->service = $service;
        }

        if ($depositService == null) {
            // $this->service = new Deub();
        } else {
            $this->depositService = $depositService;
        }
    }

    public function doTransfer(string $targetNumber, float $amount)
    {
        $response = array("isError" => true);
        $srcBal = 0;
        $toBal = 0;

        if (strlen($targetNumber) != 10 || !is_numeric($targetNumber)) {
            $response["message"] = "หมายเลขบัญชีไม่ถูกต้อง";
            return $response;
        }

        try {
            // 1----  Check toAcctNo is aready set in system
            $result = $this->service::accountAuthenticationProvider($targetNumber);
            $acctNo = $result["accNo"];
            $toBal = floatval($result["accBalance"]);

            // 2----  withdraw from scrAcctNo
            $result = $this->service::accountAuthenticationProvider($this->srcNumber);
            $srcBal = floatval($result["accBalance"]);

            if ($srcBal < $amount) {
                $response["isError"] = true;
                throw new AccountInformationException("ยอดเงินไม่เพียงพอ");
            }

            // $this->dbConnection::saveTransaction($this->srcNumber, $srcBalAfter);

            // $toBalAfter = $toBal + $amount;
            // $this->dbConnection::saveTransaction($targetNumber, $toBalAfter);

            $srcBalAfter = $srcBal - $amount;
            $response["accNo"] = $this->srcNumber;
            $response["accName"] = $this->srcName;
            $response["accBalance"] = $srcBalAfter;
            $response["isError"] = false;
        } catch (Exception $e) {
            $response["message"] = $e->getMessage();
        }

        return $response;
    }
}
