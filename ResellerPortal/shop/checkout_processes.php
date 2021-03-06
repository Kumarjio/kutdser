<?php

include '../dbconfig.php';
include 'GlobalOnePaymentXMLTools.php';

$mGlobalOnePaymentXMLTools = new GlobalOnePaymentXMLTools();


if ($_GET["do"] == "register") {
    echo $mGlobalOnePaymentXMLTools->secureCardRegister("CARD_" . $_POST["merchant_reference"], $_POST["card_number"], $_POST["card_type"], $_POST["card_expiry"], $_POST["card_holders_name"], $_POST["card_cvv"]);
} else if ($_GET["do"] == "payment") {
    echo $mGlobalOnePaymentXMLTools->payment($_POST["card_number"], $_POST["card_type"], $_POST["card_expiry"], $_POST["card_holders_name"], $_POST["card_cvv"], "P_" . $_POST["merchant_reference"], $_POST["amount"]);
} else if ($_GET["do"] == "subscription") {
    echo $mGlobalOnePaymentXMLTools->subscriptionRegister("SS_" . $_POST["merchant_reference"], "CARD_" . $_POST["merchant_reference"], $_POST["subscription_start_date"], $_POST["recurring_amount"], $_POST["initial_amount"], $_POST["period_type"]);
} else if ($_GET["do"] == "subscriptionWithMerchantref") {
    echo $mGlobalOnePaymentXMLTools->subscriptionRegister("SS_" . $_POST["merchant_reference"], "CARD_" . $_POST["existed_merchant_reference"], $_POST["subscription_start_date"], $_POST["recurring_amount"], $_POST["initial_amount"], $_POST["period_type"]);
} else if ($_GET["do"] == "registerCustomerAndAddOrder") {

    $creation_date = date("Y-m-d H:i:s");

    $product_id = $_POST["product"];

    if (isset($_POST["full_name"])) {

        if ($_POST["customer_id"] == 0) {

            $is_credit = "yes";

            //Create new customer
            $result_customer = $conn_routers->query("INSERT INTO `customers` (
                `full_name` ,
                `address_line_1` ,
                `address_line_2` ,
                `postal_code` ,
                `city` ,
                `email`,
                `phone`,
                `is_reseller`,
                `reseller_id`,
                `note`
            )
            VALUES (
                '" . mysql_real_escape_string($_POST["full_name"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["address_line_1"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["address_line_2"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["postal_code"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["city"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["email"]) . "',"
                    . " '" . mysql_real_escape_string($_POST["phone"]) . "',"
                    . " '0' ,"
                    . " '" . $reseller_id . "' ,"
                    . " '" . mysql_real_escape_string($_POST["note"]) . "'
            );");

            $customer_id = $conn_routers->insert_id; //New customer's ID
        } else {
            $customer_id = $_POST["customer_id"];
            $is_credit = "no";
            $result_customer = true;
        }

        //if existed customer, set extra_order_recurring_status = pending to modify the recurring amount later by the engine
        $extra_order_recurring_status = "pending";
        if (!isset($_POST["existed_merchant_reference"])) { // new Customer
            $extra_order_recurring_status = "";
        }
        
        //2- Create new Order
        $result_order = $conn_routers->query("insert into `orders` ("
                . "`product_id`, "
                . "`creation_date`, "
                . "`status`, "
                . "`reseller_id`, "
                . "`customer_id`, "
                . "`extra_order_recurring_status` "
                . ") VALUES ("
                . "'" . $product_id . "',"
                . "'" . $creation_date . "', "
                . "'sent', "
                . "'" . $reseller_id . "', "
                . "'" . $customer_id . "', "
                . "'" . $extra_order_recurring_status . "'"
                . ")");

        $order_id = $conn_routers->insert_id; //New order's ID


        if (isset($_POST["options"])) {
            $options = json_decode($_POST['options']);

            $cancellation_date = date("Y-m-d H:i:s", strtotime($options->cancellation_date));
            $installation_date_1 = date("Y-m-d H:i:s", strtotime($options->installation_date_1));
            $installation_date_2 = date("Y-m-d H:i:s", strtotime($options->installation_date_2));
            $installation_date_3 = date("Y-m-d H:i:s", strtotime($options->installation_date_3));

            //3- Add order options
            $result_order_options = $conn_routers->query("insert into `order_options` ("
                    . "`order_id`, "
                    . "`plan`, "
                    . "`modem`, "
                    . "`router`, "
                    . "`cable_subscriber`, "
                    . "`current_cable_provider`, "
                    . "`cancellation_date`, "
                    . "`installation_date_1`, "
                    . "`installation_time_1`, "
                    . "`installation_date_2`, "
                    . "`installation_time_2`, "
                    . "`installation_date_3`, "
                    . "`installation_time_3`, "
                    . "`modem_serial_number`, "
                    . "`modem_mac_address`, "
                    . "`additional_service`, "
                    . "`product_price`, "
                    . "`additional_service_price`, "
                    . "`setup_price`, "
                    . "`modem_price`, "
                    . "`router_price`, "
                    . "`adapter_price`, "
                    . "`current_phone_number`, "
                    . "`phone_province`, "
                    . "`remaining_days_price`, "
                    . "`total_price`, "
                    . "`gst_tax`, "
                    . "`qst_tax`, "
                    . "`modem_id`, "
                    . "`modem_modem_type`"
                    . ") VALUES ('"
                    . $order_id . "', "
                    . "'" . $options->plan . "', "
                    . "'" . $options->modem . "', "
                    . "'" . $options->router . "', "
                    . "'" . $options->cable_subscriber . "', "
                    . "'" . $options->current_cable_provider . "', "
                    . "'" . $cancellation_date . "', "
                    . "'" . $installation_date_1 . "', "
                    . "'" . $options->installation_time_1 . "', "
                    . "'" . $installation_date_2 . "', "
                    . "'" . $options->installation_time_2 . "', "
                    . "'" . $installation_date_3 . "', "
                    . "'" . $options->installation_time_3 . "', "
                    . "'" . $options->modem_serial_number . "', "
                    . "'" . $options->modem_mac_address . "', "
                    . "'" . $options->additional_service . "', "
                    . "'" . $options->product_price . "', "
                    . "'" . $options->additional_service_price . "', "
                    . "'" . $options->setup_price . "', "
                    . "'" . $options->modem_price . "', "
                    . "'" . $options->router_price . "', "
                    . "'" . $options->adapter_price . "', "
                    . "'" . $options->current_phone_number . "', "
                    . "'" . $options->phone_province . "', "
                    . "'" . $options->remaining_days_price . "', "
                    . "'" . $options->total_price . "', "
                    . "'" . $options->gst_tax . "', "
                    . "'" . $options->qst_tax . "', "
                    . "'" . $options->modem_id . "', "
                    . "'" . $options->modem_modem_type . "'"
                    . ")");

            //Assgin the modem to the new customer if it is from inventory
            if ($options->modem == "inventory") {
                $conn_routers->query("update `modems` set `customer_id`='" . $customer_id . "' where `modem_id`='" . $options->modem_id . "'");
            }
        }

        //if existed customer, do not add new merchantref
        if (!isset($_POST["existed_merchant_reference"])) {
            //4- insert Order Merchant Ref
            $result_merchantrefs = $conn_routers->query("insert into `merchantrefs` ("
                    . "`merchantref`, "
                    . "`customer_id`, "
                    . "`order_id`, "
                    . "`is_credit`, "
                    . "`type`"
                    . ") VALUES ("
                    . "'" . $_POST["merchantref"] . "', "
                    . "'" . $customer_id . "', "
                    . "'" . $order_id . "', "
                    . "'" . $is_credit . "', "
                    . "'internet_order'"
                    . ")");
        } else{
            $result_merchantrefs = $conn_routers->query("insert into `merchantrefs` ("
                    . "`merchantref`, "
                    . "`customer_id`, "
                    . "`order_id`, "
                    . "`is_credit`, "
                    . "`type`"
                    . ") VALUES ("
                    . "'" . $_POST["merchantref"] . "', "
                    . "'" . $customer_id . "', "
                    . "'" . $order_id . "', "
                    . "'" . $is_credit . "', "
                    . "'payment'"
                    . ")");
        }
        
        if ($result_customer && $result_order && $result_order_options && $result_merchantrefs) {
            echo $order_id."_".(((0x0000FFFF & (int)$order_id) << 16) + ((0xFFFF0000 & (int)$order_id) >> 16));
            die();
        } else {
            echo "0";
            die();
        }
    }
    echo "0";
    die();
} else if ($_GET["do"] == "updateSubscription") {
    echo $mGlobalOnePaymentXMLTools->updateSubscription("SS_" . $_POST["merchant_reference"], "SS_" . $_POST["merchant_reference"], "CARD_" . $_POST["merchant_reference"], $_POST["recurring_amount"]);    
}
?>