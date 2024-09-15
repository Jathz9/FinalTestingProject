<?php

include_once 'ServiceType.php';
include_once 'AccountInformationException.php';
include_once 'BillingException.php';

class DBConnection {
    public static function accountInformationProvider(): array {
        $argument = func_get_args();

        if (count($argument) == 1) {
            return DBConnection::serviceAuthentication($argument[0]);
        }
        elseif(count($argument) == 2) {
            return DBConnection::userAuthentication(
                $argument[0],
                $argument[1]
            );
        }
    }

    public static function saveTransaction(string $accNo, int $updatedBalance): bool {
        $con = new mysqli('localhost', 'root', '', 'integration');

        $stmt = "UPDATE account SET balance = ". $updatedBalance. " WHERE no = ". $accNo;
        $result = $con->query($stmt);
        $con->close();

        return $result;
    }

    private static function serviceAuthentication(string $accNo): array {
        $con = new mysqli('localhost', 'root', '', 'integration');

        $stmt = "SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance, "
            . "waterCharge as accWaterCharge, "
            . "electricCharge as accElectricCharge, "
            . "phoneCharge as accPhoneCharge "
            . "FROM account "
            . "WHERE no = ". $accNo;
        $result = $con->query($stmt);
        $con->close();

        if ($result->num_rows == 0) {
            throw new AccountInformationException("Account number : {$accNo} not found.");
        }
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    private static function userAuthentication(string $accNo, string $pin): array {
        $con = new mysqli('localhost', 'root', '', 'integration');

        $stmt = "SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance "
            . "FROM account "
            . "WHERE no = ". $accNo. " AND pin = ". $pin;
        $result = $con->query($stmt);
        $con->close();

        if ($result->num_rows == 0) {
            throw new AccountInformationException("Account number or PIN is invalid.");
        }
        return $result->fetch_array(MYSQLI_ASSOC);
    }
	
	public static function getCharge(string $accNo, int $type): int{
		$con = new mysqli('localhost', 'root', '', 'integration');
		if($type == 0){
			$stmt = "SELECT waterCharge as charge FROM account WHERE no =".$accNo;
		}
		elseif ($type==1) {
            $stmt = "SELECT electricCharge as charge FROM account WHERE no =".$accNo;
        }
        elseif ($type==2) {
            $stmt = "SELECT phoneCharge as charge FROM account WHERE no =".$accNo;
        }
        
		$result = mysqli_query($con, $stmt); 
		$row = mysqli_fetch_array($result);

        if(!$result) {
            throw new AccountInformationException("account number : {$accNo} not found.");
        }

        return $row['charge'];

	}
	
	public static function getBalance(string $accNo, int $type): int{
		$con = new mysqli('localhost', 'root', '', 'integration');
		if($type == 0){
			$stmt = "SELECT balance as bal FROM account WHERE no =".$accNo;
		}
		elseif ($type==1) {
            $stmt = "SELECT balance as bal FROM account WHERE no =".$accNo;
        }
        elseif ($type==2) {
            $stmt = "SELECT balance as bal FROM account WHERE no =".$accNo;
        }
		
		$result = mysqli_query($con, $stmt); 
		$row = mysqli_fetch_array($result);

        if(!$result) {
            throw new AccountInformationException("account number : {$accNo} not found.");
        }

        return $row['bal'];

	}
	
	public static function updateBill(string $accNo, int $updatedBalance, int $type): bool{
		
		$con = new mysqli('localhost', 'root', '', 'integration');

		if($type == 0){
			$stmt = "UPDATE account SET waterCharge = ". $updatedBalance. " WHERE no = ". $accNo;
		}
		elseif ($type == 1) {
            $stmt = "UPDATE account SET electricCharge = ". $updatedBalance. " WHERE no = ". $accNo;
            
        }
        elseif ($type == 2) {
            $stmt = "UPDATE account SET phoneCharge = ". $updatedBalance. " WHERE no = ". $accNo;
            
        }
		
        $result = $con->query($stmt);
        $con->close();

        return $result;
	
	}
	
	public static function restore(): bool{
		
		$con = new mysqli('localhost', 'root', '', 'integration');

		$stmt = "UPDATE account SET balance = 10000,waterCharge = 200 WHERE no = 0112233445";
		

        $result = $con->query($stmt);
        $con->close();

        return $result;
		
	}
	
	
}
