<?php
//   * * * * * * * * * * * * * * * * * * *
//    P R O F O R M A - S E R V E R      *
//   * * * * * * * * * * * * * * * * * * *

include "../Configuration/parser.php";
include('../Configuration/config.php');
include('../Configuration/config1.php');
$data = "";


/**
 * Fonction for save a invoice hd and dt first save invoice
 */
if (isset($_POST["saveInvoiceHdFirstStep"])) {
    $quoteNumber = $_POST["saveInvoiceHdFirstStep"];

    $query = "SELECT * FROM quotehd WHERE quoteNumber='$quoteNumber'";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    $data = $inc = $invoice_idHd =  0;

    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $succursale_id    = $row['succursale_id'];
            $dateInvoice = test_input(date('Y-m-d'));
            $quoteIDHD        = $row['quoteIDHD'];
            $customers_id    = $row['customers_id'];
            $notes            = $row['notes'];
            $monnaie        = $row['monnaie'];
            $quantiteItems    = $row['quantiteItems'];
            $totalItems        = $row['totalItems'];
            $taxes            = $row['taxes'];
            $discountPourcentage = $row['discountPourcentage'];
            $discountAmount        = $row['discountAmount'];
            $grandTotal        = $row['grandTotal'];
            $ordermoney        = $row['orderMoney'];
            $taux        = $row['taux'];
            $statut        = 'E';
            $transport    = $row['transport'];
            $typePaiement    = 'Multi';
            $statutPaiement    = 'N';
            $typeFacture    = 'I';
            $user_override    = $row['user_override'];
            $date_override    = $row['date_override'];
            $totalCost        = 0;
            $deposit    = 0;
            $userc_id = test_input($_SESSION["id"]);;
            $userm_id = test_input($_SESSION["id"]);;

            // Insert Invoicehd
            $sql = "INSERT INTO invoicehd (succursale_id, dateInvoice, quoteIDHD, customers_id, notes, monnaie, quantiteItems, totalItems, taxes, discountPourcentage, discountAmount, grandTotal, ordermoney, taux, statut,transport, typePaiement, statutPaiement, typeFacture, user_Override, date_Override, totalCost, deposit, userc_id, userm_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($con, $sql)) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "isiissddddddsdsdsssssddss",
                    $succursale_id,
                    $dateInvoice,
                    $quoteIDHD,
                    $customers_id,
                    $notes,
                    $monnaie,
                    $quantiteItems,
                    $totalItems,
                    $taxes,
                    $discountPourcentage,
                    $discountAmount,
                    $grandTotal,
                    $ordermoney,
                    $taux,
                    $statut,
                    $transport,
                    $typePaiement,
                    $statutPaiement,
                    $typeFacture,
                    $user_Override,
                    $date_Override,
                    $totalCost,
                    $deposit,
                    $userc_id,
                    $userm_id
                );
            }
        }

        // Recuperer Invoice IDHD
        if (mysqli_stmt_execute($stmt)) {
            $inc += 1;
            $invoice_idHd = mysqli_insert_id($con);
        } else {
            die("SQL query failed: " . mysqli_error($con));
        }
    }
    $fetch_data->close();

    echo $invoice_idHd;
}


/**
 * Fonction for save a invoice part 2
 */
else if (isset($_POST["invoice_id"])) {
    $invoiceIDHD = $_POST["invoice_id"];
    $quoteIDHD = $_POST["quote_id_save"];

    $cash = $_POST["cash"];
    $debitcredit = $_POST["debitcredit"];
    $check = $_POST["check"];
    $numerocheck = $_POST["numerocheck"];
    $account = $_POST["account"];
    $depositonso = $_POST["depositonso"];

    $tax = $_POST['tax'];
    $discountPourcent = $_POST['discountPourcent'];
    $discountAmount = $_POST['discountAmount'];
    $grandTotal = $_POST['total'];
    $shipping = $_POST['shipping'];
    $subTotal = $_POST['subTotal'];

    $statut = "P";
    $shipto = $_POST['shipto'];

    $knowPayment = $montantPayer = 0;;
    $typePayment = "";
    if ($numerocheck == "") $numerocheck = "";

    if ($cash == "") $cash = 0;
    else {
        $typePayment = "Cash";
        $knowPayment += 1;
    }
    if ($debitcredit == "") $debitcredit = 0;
    else {
        $typePayment = "Debit Credit";
        $knowPayment += 1;
    }
    if ($check == "") $check = 0;
    else {
        $typePayment = "Check";
        $knowPayment += 1;
    }
    if ($account == "") {
        $account = 0;
        $montantPayer = $grandTotal;
    } else {
        $typePayment = "Account";
        $knowPayment += 1;
        $montantPayer = (($grandTotal * 1) - ($account * 1));
    }
    if ($depositonso == "") $depositonso = 0;
    else {
        $typePayment = "Deposit On So";
        $knowPayment += 1;
    }

    if ($knowPayment >= 2) $typePayment = "Multi";

    if ($discountPourcent == "") $discountPourcent = 0;
    if ($discountAmount == "") $discountAmount = 0;
    if ($tax == "") $tax = 0;
    if ($grandTotal == "") $grandTotal = 0;
    if ($shipping == "") $shipping = 0;
    if ($subTotal == "") $subTotal = 0;

    $code = "I-";
    $InvoiceNumber = $invoiceIDHD;
    $inc = $inc1 = $quantiteItems = $totalItems = 0;
    $succursale_id = $_SESSION['succursaleid'];
    $datepaiement = test_input($_SESSION["datetoday"]);
    $customers_id = $_POST['customer_id_product'];
    $userm_id = test_input($_SESSION["id"]);
    $monnaie = $_POST['currency'];
    $taux = $_POST['exrate'];
    $typeFacture = "I";
    $orderMoney = 0;
    $data1 = false;

    if ($monnaie == $_SESSION["dmoney"]) $orderMoney = 1;
    if ($monnaie == $_SESSION["fmoney"]) $orderMoney = 2;

    $query_dt = "SELECT * FROM quotedt WHERE quoteidHD = '$quoteIDHD'";
    $fetch_data_dt = mysqli_query($con, $query_dt);
    $row_dt = mysqli_num_rows($fetch_data_dt);

    if ($row_dt > 0) {
        while ($row_dt = mysqli_fetch_assoc($fetch_data_dt)) {
            $inc += 1;
            if ($row_dt['quantite'] > 0) $quantiteItems += $row_dt['quantite'];
            if ($row_dt['quantite2'] > 0) $quantiteItems += $row_dt['quantite2'];
        }
    }
    $fetch_data_dt->close();

    if ($inc > 0) {
        $statut = "P";

        //Update invoice hd
        $sql_update_hd = "UPDATE invoicehd SET code='$code', invoiceNumber=$InvoiceNumber, shipto='$shipto', quantiteItems=$quantiteItems, totalItems=$subTotal, taxes=$tax, discountPourcentage=$discountPourcent, discountAmount=$discountAmount, grandTotal=$grandTotal, statut='$statut', transport=$shipping, typePaiement='$typePayment', userm_id=$userm_id, montantpaye=$montantPayer, statutPaiement='$statut' WHERE invoiceIDHD=$invoiceIDHD";

        $result_update_hd = mysqli_query($con, $sql_update_hd);
        if ($result_update_hd) {

            //call method for data invoice dt
            saveDataInvoiceDt($con, $invoiceIDHD, $quoteIDHD);

            //Update invoice dt
            $sql_update_dt = "UPDATE invoicedt SET statut='$statut' WHERE invoiceIDHD=$invoiceIDHD";
            $result_update_dt = mysqli_query($con, $sql_update_dt);

            if ($result_update_dt) {

                //save invoice pay
                $sql_pay = "INSERT INTO invoicepaye (invoiceIDHD, succursale_id, datepaiement, cash, dbcr, cheque, chequeNumber, account, statut, customers_id, orderMoney, monnaie, taux, typeFacture, deposit)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                if ($stmt_pay = mysqli_prepare($con, $sql_pay)) {
                    mysqli_stmt_bind_param(
                        $stmt_pay,
                        "iisdddsdsissdsd",
                        $invoiceIDHD,
                        $succursale_id,
                        $datepaiement,
                        $cash,
                        $debitcredit,
                        $check,
                        $numerocheck,
                        $account,
                        $statut,
                        $customers_id,
                        $orderMoney,
                        $monnaie,
                        $taux,
                        $typeFacture,
                        $depositonso
                    );

                    if (mysqli_stmt_execute($stmt_pay)) {

                        //Update Quantity produit for a sell
                        $query_modify_quote_dt = "SELECT * FROM quotedt WHERE quoteidHD = '$quoteIDHD'";
                        $fetch_data_quote_dt = mysqli_query($con, $query_modify_quote_dt);
                        $row_dt_1 = mysqli_num_rows($fetch_data_quote_dt);

                        if ($row_dt_1 > 0) {
                            while ($row_dt_1 = mysqli_fetch_assoc($fetch_data_quote_dt)) {
                                $produit_id = $row_dt_1['produit_id'];
                                $quantityBuy = $quantityModify = $totalCost = 0;
                                $quantityToModify = returnQuantityProductById($con, $produit_id);
                                $baseunit = returnBaseUnitProductById($con, $produit_id);

                                if ($row_dt_1['quantite'] > 0) {
                                    $quantityBuy = $row_dt_1['quantite'];
                                    $quantityModify = $quantityToModify - ($quantityBuy * $baseunit);
                                    $totalCost = (($quantityBuy * $baseunit) * $row_dt_1['unitPrice2']);
                                }
                                if ($row_dt_1['quantite2'] > 0) {
                                    $quantityBuy = $row_dt_1['quantite2'];
                                    $quantityModify = $quantityToModify - $quantityBuy;
                                    $totalCost = ($quantityBuy * $row_dt_1['unitPrice2']);
                                }

                                if ($row_dt_1['typeItem'] == "Inventory" || $row_dt_1['typeItem'] == "inventory") {
                                    $sql_update_product = "UPDATE produit SET quantitetotal=$quantityModify WHERE produit_id=$produit_id";

                                    $sql_update_product_result = mysqli_query($con, $sql_update_product);
                                    if ($sql_update_product_result) {

                                        //Total Cost
                                        $sql_update_total_cost = "UPDATE invoicehd SET totalCost=$totalCost WHERE invoiceIDHD=$invoiceIDHD";

                                        $sql_update_total_cost_result = mysqli_query($con, $sql_update_total_cost);
                                        if ($sql_update_total_cost_result) {
                                            $inc1++;
                                        }
                                    }
                                }
                                if ($row_dt_1['typeItem'] == "Service" || $row_dt_1['typeItem'] == "service") {
                                    $inc1++;
                                }
                            }
                        }
                        $fetch_data_quote_dt->close();

                        if ($inc1 > 0) {

                            //update account balance customers
                            if ($account != "") {
                                $return_account_balance = returnAcountBalanceCustomerById($con, $customers_id);

                                $account_convert = 0;
                                if($monnaie =="HTG"){
                                    $account_convert = ($account * $taux) + ($return_account_balance * 1);
                                }
                                else if($monnaie =="USD"){
                                    $account_convert = ($account * 1) + ($return_account_balance * 1);
                                }

                                $sql_update_account_balance = "UPDATE customers SET account_balance=$account_convert WHERE customers_id=$customers_id";

                                $result_update_account_balance = mysqli_query($con, $sql_update_account_balance);
                                if ($result_update_account_balance) {
                                    echo $data1 = true;
                                } else  die("SQL query failed: " . mysqli_error($con));
                            } else echo $data1 = true;
                        } else  die("SQL query failed: " . mysqli_error($con));
                    } else  die("SQL query failed: " . mysqli_error($con));
                } else die("SQL query failed: " . mysqli_error($con));
            } else echo die("SQL query quote dt failed: " . mysqli_error($con));
        } else  echo die("SQL query quote hd failed: " . mysqli_error($con));
    } else echo $data1;
}


/**
 * Fonction for search code Invoice HD
 */
else if (isset($_POST["codeInvoiceHD"])) {
    $invoiceIDHD = $_POST["codeInvoiceHD"];

    $query = "SELECT * FROM invoicehd WHERE invoiceIDHD=$invoiceIDHD";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row['code'] . $row['invoiceNumber'];
        }
    }
    echo $data;
}


/**
 * Fonction for verify Account Balance in customer
 */
else if (isset($_POST["account_balance_customer"])) {
    $customer_id = $_POST["account_balance_customer"];
    $account = $_POST["account"];
    $data = "";

    $query = "SELECT * FROM customers WHERE customers_id=$customer_id";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            if ($account != "") {
                if ($row['account_balance'] > $row['account_limit']) {
                    $data = "Registration invoice failed \n\nYour account limit: " . $row['account_limit'] . "\nYour account balance: " . $row['account_balance'] . " + " . $account . " = " . ($row['account_balance'] + ($account * 1));
                } else {
                    if (($row['account_balance'] + ($account * 1)) > $row['account_limit']) {
                        $data = "Registration invoice failed \n\nYour account limit: " . $row['account_limit'] . "\nYour account balance: " . $row['account_balance'] . " + " . $account . " = " . ($row['account_balance'] + ($account * 1));
                    } else {
                        $data = true;
                    }
                }
            } else $data = true;
        }
    }
    echo $data;
}


/**
 * Fonction for Search facture Invoice hd
 */
else if (isset($_POST["enTeteFactureInvoice"])) {
    $invoiceIDHD = $_POST["enTeteFactureInvoice"];
    $yes = "Y";

    $sql_update = "UPDATE invoicehd SET isPrinted='$yes' WHERE invoiceIDHD=$invoiceIDHD";
    mysqli_query($con, $sql_update);

    $query = " SELECT s.Adresse, hd.quoteIDHD,  p.companyname, s.Telephone1, s.Telephone2, hd.code, hd.invoiceNumber, s.emailStore,
        c.fullname, c.rue, c.ville, c.telephone1, hd.statut, u.nom, c.customers_id, c.payment_term, c.currency,
        hd.discountPourcentage, hd.totalItems, hd.discountAmount, hd.taxes, hd.transport, hd.grandTotal
        from stores s, invoicehd hd, invoicedt dt, customers c, utilisateurs u, parametre p 
        where s.succursale_id = p.succursale_id 
        and s.succursale_id = hd.succursale_id
        and hd.invoiceIDHD = dt.invoiceIDHD
        and c.customers_id = hd.customers_id
        and u.user_id = c.user_id
        and hd.invoiceIDHD = $invoiceIDHD  limit 1";

    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row;
        }
        echo json_encode( $data);
    } else  echo die("SQL query quote hd failed: " . mysqli_error($con));
}


/**
 * Fonction for Search data facture Invoice dt
 */
else if (isset($_POST["invoiceDtFacture"])) {
    $invoiceIDHD = $_POST["invoiceDtFacture"];
    $data1 = [];

    $query2 = " SELECT dt.itemName, dt.itemDescription, dt.quantite, dt.quantite2, dt.descriptionUnit, dt.unitPrice2
                                from stores s, invoicehd hd, invoicedt dt, customers c, utilisateurs u, parametre p 
                                where s.succursale_id = p.succursale_id 
                                and s.succursale_id = hd.succursale_id
                                and hd.invoiceIDHD = dt.invoiceIDHD
                                and c.customers_id = hd.customers_id
                                and u.user_id = c.user_id
                                and hd.invoiceIDHD = $invoiceIDHD ";

    $fetch_data2 = mysqli_query($con, $query2);
    $row2 = mysqli_num_rows($fetch_data2);
    if ($row2 > 0) {
        foreach ($fetch_data2 as $res) {
            array_push($data1, $res);
        }
    }
    $fetch_data2->close();

    echo json_encode($data1);
}


/**
 * Fonction for save a invoice dt
 */
function saveDataInvoiceDt($con, $invoice_idHd,  $quoteIDHD)
{

    $query_dt = "SELECT * FROM quotedt WHERE quoteidHD = '$quoteIDHD'";
    $fetch_data_dt = mysqli_query($con, $query_dt);
    $row_dt = mysqli_num_rows($fetch_data_dt);

    if ($row_dt > 0) {
        while ($row_dt = mysqli_fetch_assoc($fetch_data_dt)) {
            $succursale_id    = $row_dt['succursale_id'];
            $invoiceIDHD = $invoice_idHd;
            $produit_id    = $row_dt['produit_id'];
            $dateInvoice = $row_dt['dateQuote'];
            $quantite = $row_dt['quantite'];
            $unitPrice1    = $row_dt['unitPrice1'];
            $unitPrice2    = $row_dt['unitPrice2'];
            $statut    = 'E';
            $monnaie = $row_dt['monnaie'];
            $typeFacture = 'I';
            $averageCost = $row_dt['averageCost'];
            $orderUnit = $row_dt['orderUnit'];
            $descriptionUnit = $row_dt['descriptionUnit'];
            $quantite2 = $row_dt['quantite2'];
            $unit_id = $row_dt['unit_id'];
            $itemName = $row_dt['itemName'];
            $itemDescription = $row_dt['itemDescription'];
            $typeItem = $row_dt['typeItem'];

            // Insert Invoice dt
            $sql_dt = "INSERT INTO invoicedt (succursale_id, invoiceIDHD, produit_id, dateInvoice, quantite, unitPrice1,unitPrice2, statut, monnaie, typeFacture, taux, averageCost, orderUnit, descriptionUnit, quantite2, unit_id,itemName, itemDescription, typeItem)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_dt = mysqli_prepare($con, $sql_dt)) {
                mysqli_stmt_bind_param(
                    $stmt_dt,
                    "iiisdddsssddssdisss",
                    $succursale_id,
                    $invoiceIDHD,
                    $produit_id,
                    $dateInvoice,
                    $quantite,
                    $unitPrice1,
                    $unitPrice2,
                    $statut,
                    $monnaie,
                    $typeFacture,
                    $taux,
                    $averageCost,
                    $orderUnit,
                    $descriptionUnit,
                    $quantite2,
                    $unit_id,
                    $itemName,
                    $itemDescription,
                    $typeItem
                );

                if (mysqli_stmt_execute($stmt_dt)) {
                } else {
                    die("SQL query failed: " . mysqli_error($con));
                }
            } else {
                die("SQL query failed: " . mysqli_error($con));
            }
        }
    }
    $fetch_data_dt->close();
}
