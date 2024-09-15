<?php
namespace Operation;

require_once __DIR__ . './../serviceauthentication/serviceauthentication.php';
require_once __DIR__ . './../outputs/Outputs.php';

use BillingException;
use AccountInformationException;
use Exception;
use Error;
use DBConnection;
use ServiceAuthentication;
use Output\Outputs;

class BillPayment
{

    private $session;

    public function __construct(string $session)
    {
        $this->session = $session;
    }
	
    public function getBill(int $billType) : Outputs
    {    
        $output = new Outputs();
        try {
			$output->billAmount = DBConnection::getCharge($this->session,$billType);
         } catch (BillingException $e) {
             $output->errorMessage = $e->getMessage();
         } catch (AccountInformationException $e) {
             $output->errorMessage = $e->getMessage();
         } catch (Exception $e) {
             $output->errorMessage = $e->getMessage();
         } catch (Error $e) {
             $output->errorMessage = $e->getMessage();
         }
         return $output;
		 
    }

    public function pay(int $billType) : Outputs
    {
        $output = new Outputs();
        if ($billType >= 0 && $billType <= 2)
		{
            $balance = DBConnection::getBalance($this->session,$billType); //เงินที่มีในบัญชี
			$charge = DBConnection::getCharge($this->session,$billType); //ดึงหนี้สิน
			
			if($balance < $charge){
				$output->errorMessage = "ยอดเงินไม่พอสำหรับจ่ายบิล";
			}
			else{
				$newBalance = $balance - $charge ;
				$newCharge = 0;
				DBConnection::saveTransaction($this->session, $newBalance);
				DBConnection::updateBill($this->session, $newCharge,$billType);
				$output->accountBalance = $newBalance ; //เงินใหม่่
				$output->billAmount = $newCharge;
			}
		}
        else{
          $output->errorMessage = "ไม่มีรายการชำระเงินดังกล่าวในระบบ!";
		}
		
    return $output;
    }
   
}
