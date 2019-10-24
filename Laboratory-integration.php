<?php

$lis_server = "localhost";
$lis_user = "root";
$lis_password = "root";
$lis_db = "table";

$hhims_server = "localhost";
$hhims_user = "root";
$hhims_password = "root";
$hhims_db = "db";

// Create connection

$conn = new mysqli($lis_server, $lis_user, $lis_password, $lis_db);
$conn2 = new mysqli($hhims_server, $hhims_user, $hhims_password, $hhims_db);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}


$sql = "SELECT MESSAGE_ID,MESSAGE FROM mirth_message WHERE Status='Pending'"; // select pending messages 
$result = $conn->query($sql);


if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {

        $array = explode("|", $row["MESSAGE"]);
        $msg_type = $array[8];
        if ($msg_type != 'ORM^O01') {

            $pid = preg_quote('OBR', '~'); // don't forget to quote input string!
            $pid_array = preg_grep('~' . $pid . '~', $array);
            $labid_field = $array[key($pid_array) + 3];
            $lab_order_id = filter_var($labid_field, FILTER_SANITIZE_NUMBER_INT);
echo "Lab ID =";
echo $lab_order_id; // get the lab order id

            echo "</br>";


// get the lab order items data relevant to the lab order 

            $sql1 = "SELECT lab_tests.LoincCode,lab_tests.Name,lab_order_items.LAB_ORDER_ITEM_ID FROM lab_tests LEFT JOIN lab_order_items ON lab_tests.LABID=lab_order_items.LABID WHERE lab_order_items.LAB_ORDER_ID='$lab_order_id'";
            $result1 = $conn2->query($sql1);
            

            if ($result1->num_rows > 0) {
                // output data of each row
                while ($row1 = $result1->fetch_assoc()) {

                    $input = preg_quote($row1["LoincCode"], '~'); // don't forget to quote input string!
                    $res = preg_grep('~' . $input . '~', $array);



                    if (count($res) == 1) {

                        $res1 = $array[key($res) + 2];
                        $res2 = $array[key($res) + 3];
                        $lab_order_item_id = $row1["LAB_ORDER_ITEM_ID"];

                        $sql2 = "UPDATE lab_order_items SET TestValue='$res1 $res2', Status='Updated' WHERE LAB_ORDER_ITEM_ID='$lab_order_item_id'";
                        $result2 = $conn2->query($sql2);

                        $message_id = $row["MESSAGE_ID"];

                        $sql3 = "UPDATE mirth_message SET Status='Done' WHERE MESSAGE_ID='$message_id' "; // update table
                        $result3 = $conn->query($sql3);

echo $row1["Name"];
echo "&nbsp";echo "&nbsp";echo "&nbsp";
echo "=";
echo "&nbsp";echo "&nbsp";echo "&nbsp";
echo $res1. " " .$res2;
                    }
                }
echo "</br>";
echo "</br>";
            }
        }
    }
}




//echo "</br>";
//return;

    $conn->close();
    $conn2->close();

   header("Location: http://IP/hhims/index.php/search/lab_orders/");
    exit();
?>
