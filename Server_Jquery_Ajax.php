<?php
//   * * * * * * * * * * * * * * * * * * *
//    P R O F O R M A - S E R V E R      *
//   * * * * * * * * * * * * * * * * * * *

include "../Configuration/parser.php";
include('../Configuration/config.php');
include('../Configuration/config1.php');

/**
 * Fonction pour la recherche des donnees correspond a un client donner, demander par son identifiant
 * l'appel de ce fonction se fait par le ficher Quote.php
 */
$data = "";
if (isset($_POST["customer_id"])) {
    $id = $_POST["customer_id"];

    $query = "SELECT c.customers_id, c.fullname, c.rue, c.ville, c.notes, c.telephone1, ct.description, c.payment_term, c.account_balance, u.nom, u.prenom FROM customers c, utilisateurs u, customertype ct WHERE c.customer_type = ct.customertype_id AND u.user_id = c.user_id AND customers_id=$id";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row;
        }
    }
    $fetch_data->close();
    echo json_encode($data);
}


/**
 * Fonction for search code Quote HD
 */

else if (isset($_POST["codeQuoteHD"])) {
    $quoteIDHD = $_POST["codeQuoteHD"];

    $query = "SELECT * FROM quotehd WHERE quoteIDHD=$quoteIDHD";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row['code'] . $row['quoteNumber'];
        }
    }
    echo $data;
}


/**
 * Fonction pour la recherche des donnees correspond a un produit donner, demander par son identifiant
 * l'appel de ce fonction se fait par le ficher Quote.php
 */
else if (isset($_POST["product_id"])) {
    $id = $_POST["product_id"];
    $customer_id_product = $_POST["customer_id_product"];

    if ($customer_id_product != null) {
        $query = "SELECT p.produit_id, p.itemname, p.itemdescription, p.regularprice, p.uomprice, p.quantitetotal, p.rabais, p.regularprice1, p.regularprice2, p.regularprice3, p.regularprice4, p.regularprice5, p.uomprice1, p.uomprice2, p.uomprice3, p.uomprice4, p.uomprice4, p.baseunit, c.isRabais, c.customer_type, c.discount_type, c.discount_level, c.price_list, u1.description FROM produit p, customers c, unit1 u1 WHERE p.produit_id=$id AND c.customers_id=$customer_id_product AND u1.unit_id=1";
        $fetch_data = mysqli_query($con, $query);
        $row = mysqli_num_rows($fetch_data);
        if ($row > 0) {
            while ($row = mysqli_fetch_assoc($fetch_data)) {
                $data = $row;
            }
        }
        $fetch_data->close();

        echo json_encode($data);
    } else echo json_encode($data);
}


/**
 * Fonction verifier si un utilisateur exite, demander par son identifiant
 * l'appel de ce fonction se fait par le ficher Quote.php
 */
else if (isset($_POST["addProduct"])) {
    $addProduct = $_POST["addProduct"];

    $data = false;
    $query = "SELECT * FROM customers WHERE customers_id=$addProduct";
    $fetch_data = mysqli_query($con, $query);
    if ($fetch_data != null) {
        $row = mysqli_num_rows($fetch_data);
        if ($row > 0) $data = true;
        $fetch_data->close();
        echo ($data);
    } else echo ($data);
}


/**
 * Fonction for save a quote hd
 */
else if (isset($_POST["saveQuoteHd_part1"])) {
    if ($_POST["saveQuoteHd_part1"] == "sauvegarder") {
        $succursaleid = test_input($_SESSION["succursaleid"]);
        $dateQuote = test_input($_SESSION["datetoday"]);
        $quoteNumber = fsearchCustCode($con, 'quote');
        $customers_id = test_input($_POST['customer_id_product']);
        $nomClient =  $orderMoney = "";
        $inc = 0;

        $query = "SELECT * FROM customers  WHERE customers_id=$customers_id";
        $fetch_data = mysqli_query($con, $query);
        $row = mysqli_num_rows($fetch_data);
        if ($row > 0) {
            while ($row = mysqli_fetch_assoc($fetch_data)) {
                $inc += 1;
                $nomClient = $row['fullname'];
            }
        }
        $fetch_data->close();

        if ($inc > 0) {
            $notes = test_input($_POST['notes']);
            $monnaie = test_input($_POST['currency']);
            $statut = "N";
            $taux = test_input($_SESSION["taux"]);
            $userc_id = test_input($_SESSION["id"]);

            if ($monnaie == $_SESSION["dmoney"]) $orderMoney = 1;
            if ($monnaie == $_SESSION["fmoney"]) $orderMoney = 2;

            $sql = "INSERT INTO quotehd (succursale_id, customers_id, nomClient, notes, monnaie, taux, statut, dateQuote, userc_id, orderMoney) VALUES (?,?,?,?,?,?,?,?,?,?)";

            if ($stmt = mysqli_prepare($con, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param(
                    $stmt,
                    "sisssdssis",
                    $succursaleid,
                    $customers_id,
                    $nomClient,
                    $notes,
                    $monnaie,
                    $taux,
                    $statut,
                    $dateQuote,
                    $userc_id,
                    $orderMoney
                );

                // Attempt to execute the prepared statement
                $result = mysqli_stmt_execute($stmt);
                if ($result) {
                    echo $quoteNumber;
                } else {
                    echo mysqli_error($con);
                }
            } else {
                die("SQL query failed: " . mysqli_error($con));
            }
        } else echo mysqli_error($con);
    }
}

//saveQuoteHd_part2
else if (isset($_POST["quote_id_save"])) {
    if ($_POST["quote_id_save"] != "sauvegarder") {
        $quoteIDHD = $_POST["quote_id_save"];

        $query = "SELECT * FROM quotehd WHERE quoteIDHD=$quoteIDHD";
        $fetch_data = mysqli_query($con, $query);
        $row = mysqli_num_rows($fetch_data);
        if ($row > 0) {
            $discountPourcent = $tax = $discountAmount = $grandTotal = $shipping = $subTotal  = 0;

            $tax = $_POST['tax'];
            $discountPourcent = $_POST['discountPourcent'];
            $discountAmount = $_POST['discountAmount'];
            $grandTotal = $_POST['total'];
            $shipping = $_POST['shipping'];
            $subTotal = $_POST['subTotal'];
            $quantiteItems = 0;
            $statut = "E";
            $quoteNumber = $quoteIDHD;
            $code = 'Q-';
            $shipto = $_POST['shipto'];
            $billto = $_POST['billto'];

            if ($discountPourcent == "") $discountPourcent = 0;
            if ($discountAmount == "") $discountAmount = 0;
            if ($tax == "") $tax = 0;
            if ($grandTotal == "") $grandTotal = 0;
            if ($shipping == "") $shipping = 0;
            if ($subTotal == "") $subTotal = 0;
            if ($shipto == "") $shipto = $billto;

            // Rechercher l'id du quote Hd dans la table quote dt
            $query_dt_by_id = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
            $fetch_data_dt = mysqli_query($con, $query_dt_by_id);
            $row_dt = mysqli_num_rows($fetch_data_dt);
            if ($row_dt > 0) {
                while ($row_dt = mysqli_fetch_assoc($fetch_data_dt)) {
                    if ($row_dt['quantite'] > 0) $quantiteItems += $row_dt['quantite'];
                    if ($row_dt['quantite2'] > 0) $quantiteItems += $row_dt['quantite2'];
                }
            }
            $fetch_data_dt->close();

            //Modifier le quote hd par son id
            $sql_hd = "UPDATE quotehd SET quoteNumber=$quoteNumber, code='$code', taxes=$tax, discountPourcentage=$discountPourcent, discountAmount=$discountAmount, grandTotal=$grandTotal, transport=$shipping, totalItems=$subTotal, quantiteItems=$quantiteItems, statut='$statut', shipTo='$shipto' WHERE quoteIDHD=$quoteIDHD";

            $result_hd = mysqli_query($con, $sql_hd);
            if ($result_hd) {
                //Modifier le quote dr par id quote hd
                $sql_dt = "UPDATE quotedt SET statut='$statut' WHERE quoteidHD=$quoteIDHD";
                $result_dt = mysqli_query($con, $sql_dt);

                if ($result_dt) {
                    echo "Quote save successfull";
                } else {
                    echo die("SQL query quote dt failed: " . mysqli_error($con));
                }
            } else {
                echo die("SQL query quote hd failed: " . mysqli_error($con));
            }
        }  else {
            echo die("SQL query quote hd failed: " . mysqli_error($con));
        }      
    }
}

//saveQuoteHd_part3 For Invoice
else if (isset($_POST["quote_id_save_invoice"])) {
    $quoteIDHD =  $_POST["quote_id_save_invoice"];
    $produit_id =  $_POST["produit_id"];
    $quantity =  $_POST["quantity"];
    $unitprice =  $_POST["unitprice"];
    $quantiteItems = $totalItems = $grandTotal = $unitPrice2 = 0;

    // Rechercher l'id du quote Hd dans la table quote dt
    $query_dt_by_id = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
    $fetch_data_dt = mysqli_query($con, $query_dt_by_id);
    $row_dt = mysqli_num_rows($fetch_data_dt);
    if ($row_dt > 0) {
        while ($row_dt = mysqli_fetch_assoc($fetch_data_dt)) {
            $inc++;
            if ($row_dt['quantite'] > 0) $quantiteItems += $row_dt['quantite'];
            if ($row_dt['quantite2'] > 0) $quantiteItems += $row_dt['quantite2'];
        }
    }
    $fetch_data_dt->close();

    if ($inc > 0) {
        $query = "SELECT * FROM quotehd WHERE quoteIDHD=$quoteIDHD";
        $fetch_data = mysqli_query($con, $query);
        $row = mysqli_num_rows($fetch_data);
        if ($row > 0) {
            while ($row = mysqli_fetch_assoc($fetch_data)) {
                $totalItems = $row['totalItems'];
                $grandTotal = $row['grandTotal'];
                $unitPrice2 = $row['unitPrice2'];
            }
        }
        $fetch_data->close();

        $grandTotal = $grandTotal + ($unitPrice2 * $quantity);
        $totalItems =  $totalItems + ($unitPrice2 * $quantity);

        //Modifier le quote hd par son id
        $sql_hd = "UPDATE quotehd SET grandTotal=$grandTotal, totalItems=$totalItems, quantiteItems=$quantiteItems WHERE quoteIDHD=$quoteIDHD";

        $result_hd = mysqli_query($con, $sql_hd);
        if ($result_hd) {
            echo true;
        }
    }
}

/**
 * Fonction for save a quote dt
 */
else if (isset($_POST["codequotehd"])) {
    $quoteIDHD = $_POST["codequotehd"];

    $query = "SELECT * FROM quotehd WHERE quoteIDHD=$quoteIDHD";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {

        // Data formulaire
        $succursaleid = test_input($_SESSION["succursaleid"]);
        $dateQuote = test_input($_SESSION["datetoday"]);
        $produit_id = test_input($_POST["produit_id"]);
        $uom = test_input($_POST["uom"]);
        $quantity = $quantity2 = $unitPrice1 = $unitPrice2 = 0;
        $monnaie = test_input($_POST['monnaie']);
        $statut = "N";
        $unitPrice2 = test_input($_POST["unitprice"]);
        $unitPrice1 = test_input($_POST["regularprice"]);

        if ($uom == "UOM 1") $quantity2 = test_input($_POST["quantity"]);
        else if ($uom == "UOM 2")  $quantity = test_input($_POST["quantity"]);

        // Search data about the product by produit_id
        $item_name = $item_description = $typeItem = $averageCost = $descriptionUnit = "";
        $unit_id = $orderUnit = 0;
        $query1 = "SELECT * FROM produit WHERE produit_id=$produit_id";
        $fetch_data1 = mysqli_query($con, $query1);
        $row1 = mysqli_num_rows($fetch_data1);
        $inc = 0;
        if ($row1 > 0) {
            while ($row1 = mysqli_fetch_assoc($fetch_data1)) {
                $inc++;
                $item_name = $row1['itemname'];
                $item_description = $row1['itemdescription'];
                $typeItem = $row1['itemtype'];
                $averageCost = $row1['averagecost'];
                if ($uom == "UOM 1") {
                    $unit_id = $row1['unit1_id'];
                    $orderUnit = 1;
                    $descriptionUnit = fsearchUnitDescription($con, "unit1", "description", $unit_id);
                } else if ($uom == "UOM 2") {
                    $unit_id = $row1['unit2_id'];
                    $orderUnit = 2;
                    $descriptionUnit = fsearchUnitDescription($con, "unit2", "description", $unit_id);
                }
            }
        }
        $fetch_data1->close();

        if ($inc > 0) {
            $sql = "INSERT INTO quotedt (succursale_id, quoteidHD, produit_id, itemName, itemDescription, quantite, quantite2, monnaie, statut, unitPrice1, unitPrice2, dateQuote, typeItem, averageCost, unit_id, descriptionUnit, orderUnit) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            if ($stmt = mysqli_prepare($con, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param(
                    $stmt,
                    "siissddssddsssiss",
                    $succursaleid,
                    $quoteIDHD,
                    $produit_id,
                    $item_name,
                    $item_description,
                    $quantity,
                    $quantity2,
                    $monnaie,
                    $statut,
                    $unitPrice1,
                    $unitPrice2,
                    $dateQuote,
                    $typeItem,
                    $averageCost,
                    $unit_id,
                    $descriptionUnit,
                    $orderUnit
                );

                // Attempt to execute the prepared statement
                $result = mysqli_stmt_execute($stmt);
                if ($result) {
                    echo true;
                } else {
                    echo $stmt;
                }
            } else {
                die("SQL query failed: " . mysqli_error($con));
            }
        } else {
            echo mysqli_error($con);
        }
    } else echo mysqli_error($con);
    $fetch_data->close();
}

/**
 * Fonction for view data quote dt by id
 */
else if (isset($_POST["viewQuoteDtByID"])) {
    $quoteIDHD = $_POST["viewQuoteDtByID"];
    $data1 = [];
    $query2 = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
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
 * Fonction for search data to modidy a quote (HD and DT)
 */
else if (isset($_POST["modify_quotedt_by_id"])) {
    $quoteidDT = $_POST["modify_quotedt_by_id"];
    $quoteIDHD = fsearchQuoteIDHD($con, $quoteidDT);

    /*$query = "SELECT q.itemName, q.itemDescription, q.unitPrice1, q.unitPrice2, q.quantite, q.quantite2, p.quantitetotal FROM quotedt q, produit p WHERE q.quoteidDT=$quoteidDT AND p.produit_id=q.produit_id";*/

    $query = "SELECT p.produit_id, p.itemname, p.itemdescription, p.regularprice, p.uomprice, p.quantitetotal, p.rabais, p.regularprice1, p.regularprice2, p.regularprice3, p.regularprice4, p.regularprice5, p.uomprice1, p.uomprice2, p.uomprice3, p.uomprice4, p.uomprice4, c.isRabais, c.customer_type, c.discount_type, c.discount_level, c.price_list, q.quantite, q.quantite2, q.monnaie, hd.taux FROM produit p, customers c, quotedt q, quotehd hd WHERE q.quoteidDT=$quoteidDT AND hd.quoteIDHD=q.quoteidHD AND p.produit_id=q.produit_id AND c.customers_id=hd.customers_id";

    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row;
        }
    }
    $fetch_data->close();
    echo json_encode($data);
}


/**
 * Fonction for Delete a quote dt
 */
else if (isset($_POST["delete_quotedt_by_id"])) {
    $quoteidDT = $_POST["delete_quotedt_by_id"];

    //Recuperer le quoteIDHD pour savoir le nombres de quote associer avec
    $quoteIDHD = fsearchQuoteIDHD($con, $quoteidDT);

    $query = "DELETE FROM quotedt WHERE quoteidDT=$quoteidDT";
    $fetch_data = mysqli_query($con, $query);
    $data2 = [];

    $query2 = "SELECT dt.quoteidDT, dt.quoteidHD, dt.itemName, dt.itemDescription, dt.quantite, dt.quantite2, dt.unitPrice1, dt.unitPrice2, dt.monnaie, dt.orderUnit,dt.typeItem,dt.descriptionUnit, hd.taux, hd.taxes, hd.discountPourcentage, hd.discountAmount, hd.transport FROM quotedt dt, quotehd hd WHERE dt.quoteidHD=$quoteIDHD AND hd.quoteIDHD=$quoteIDHD";
    $fetch_data2 = mysqli_query($con, $query2);
    $row2 = mysqli_num_rows($fetch_data2);
    if ($row2 > 0) {
        foreach ($fetch_data2 as $res) {
            array_push($data2, $res);
        }
    }
    echo json_encode($data2);
    $fetch_data2->close();
}


/**
 * Fonction for modidy a quote DT
 */
else if (isset($_POST["quote_id_dt"])) {
    $quote_id_dt_hd = $_POST["quote_id_dt"];
    $uom = test_input($_POST["uom"]);
    $unitprice = test_input($_POST["unitprice"]);
    $regularprice = test_input($_POST["regularprice"]);
    $exrate = test_input($_POST["exrate"]);
    $quantity2 = $quantity = 0;

    // Search data about the product by produit_id
    $item_name = $item_description = $typeItem = $averageCost = $descriptionUnit = "";
    $unit_id = $orderUnit = 0;

    $produit_id = fsearchProduct_idOnQuoteDT($con, $quote_id_dt_hd);
    $query = "SELECT * FROM produit WHERE produit_id=$produit_id";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $item_name = $row['itemname'];
            $item_description = $row['itemdescription'];
            $typeItem = $row['itemtype'];
            $averageCost = $row['averagecost'];

            if ($uom == "UOM 1") {
                $quantity2 = $_POST["quantity"];
                $unit_id = $row['unit1_id'];
                $orderUnit = 1;
                $descriptionUnit = fsearchUnitDescription($con, "unit1", "description", $unit_id);
            } else if ($uom == "UOM 2") {
                $quantity = $_POST["quantity"];
                $unit_id = $row['unit2_id'];
                $orderUnit = 2;
                $descriptionUnit = fsearchUnitDescription($con, "unit2", "description", $unit_id);
            }
        }
    }
    $fetch_data->close();


    //Modifier le quote hd par son id
    $sql = "UPDATE quotedt SET itemName='$item_name', itemDescription='$item_description', typeItem='$typeItem', averageCost='$averageCost', descriptionUnit='$descriptionUnit', unit_id=$unit_id, orderUnit=$orderUnit, quantite=$quantity, quantite2=$quantity2, unitPrice1=$regularprice, unitPrice2=$unitprice  WHERE quoteidDT =$quote_id_dt_hd";

    $result = mysqli_query($con, $sql);
    if ($result) {
        $data1 = [];
        $quoteIDHD = fsearchQuoteIDHD($con, $quote_id_dt_hd);

        $query2 = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
        $fetch_data2 = mysqli_query($con, $query2);
        $row2 = mysqli_num_rows($fetch_data2);
        if ($row2 > 0) {
            foreach ($fetch_data2 as $res) {
                array_push($data1, $res);
            }
        }
        $fetch_data2->close();
        echo json_encode($data1);
    } else {
        echo die("SQL query quote hd failed: " . mysqli_error($con));
    }
}


/**
 * Fonction for change money a quote en cours
 */
else if (isset($_POST["change_quotedt_money"])) {
    $quoteIDHD = $_POST["change_quotedt_money"];
    $currencyMoney = $_POST["currencyMoney"];

    $fmoney = number_format($_SESSION['taux'], 2);
    $dmoney = number_format($_SESSION['tauxDollar'], 2);

    $query = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        $result = $indice = 0;

        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $indice += 1;

            if ($currencyMoney == "HTG") {
                if ($row['monnaie'] == "USD") {
                    $regularprice = ($row['unitPrice1'] * $fmoney);
                    $unitprice = ($row['unitPrice2'] * $fmoney);

                    $sql = "UPDATE quotedt SET monnaie='$currencyMoney', unitPrice1=$regularprice, unitPrice2=$unitprice WHERE quoteidHD =$quoteIDHD";
                }
            }
            if ($currencyMoney == "USD") {
                if ($row['monnaie'] == "HTG") {
                    $regularprice = $row['unitPrice1'] / $fmoney;
                    $unitprice = $row['unitPrice2'] / $fmoney;

                    $sql = "UPDATE quotedt SET monnaie='$currencyMoney', unitPrice1=$regularprice, unitPrice2=$unitprice WHERE quoteidHD =$quoteIDHD";
                }
            }
            if (mysqli_query($con, $sql)) {
                $sql_hd = "UPDATE quotehd SET monnaie='$currencyMoney' WHERE quoteIDHD =$quoteIDHD";
                if (mysqli_query($con, $sql_hd)) {
                    $result += 1;
                }
            }
        }

        if ($indice == $result) {
            $data1 = [];
            $query2 = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
            $fetch_data2 = mysqli_query($con, $query2);
            $row2 = mysqli_num_rows($fetch_data2);
            if ($row2 > 0) {
                foreach ($fetch_data2 as $res) {
                    array_push($data1, $res);
                }
            }
            $fetch_data2->close();
            echo json_encode($data1);
        } else echo mysqli_error($con);
    } else echo mysqli_error($con);
    $fetch_data->close();
}


/**
 * Fonction for search unit of a product
 */
else if (isset($_POST["product_unit1"])) {
    $id = $_POST["product_unit1"];

    $query = "SELECT * FROM produit WHERE produit_id=$id";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        $unit1_id = $unit2_id = 0;
        $data1 = [];
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $unit1_id =  $row['unit1_id'];
            $unit2_id = $row['unit2_id'];
        }

        $description_unit1_id = fsearchUnitDescription($con, "unit1", "description", $unit1_id);
        $description_unit2_id = fsearchUnitDescription($con, "unit2", "description", $unit2_id);

        array_push($data1, $description_unit1_id, $description_unit2_id);
        echo json_encode($data1);
    }
    $fetch_data->close();
}


/**
 * Fonctions for display facture quote
 */
else if (isset($_POST["enTeteFacture"])) {
    $quoteIDHD = $_POST["enTeteFacture"];

    $query = " SELECT s.Adresse, s.emailStore, s.Name, s.Telephone1, s.Telephone2, hd.code, hd.quoteNumber,
    c.fullname, c.rue, c.ville, c.telephone1, hd.shipTo, u.nom, c.customers_id, c.payment_term, c.currency,
    hd.discountPourcentage, hd.totalItems, hd.discountAmount, hd.taxes, hd.transport, hd.grandTotal,
    pm.companyname
    from stores s, quotehd hd, quotedt dt, customers c, utilisateurs u, parametre pm
    where s.succursale_id = hd.succursale_id 
    and hd.quoteIDHD = dt.quoteidHD 
    and c.customers_id = hd.customers_id
    and s.succursale_id = pm.Succursale_id 
    and u.user_id = c.user_id
    and hd.quoteIDHD = $quoteIDHD";

    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row;
        }
    }
    echo json_encode($data);
}

//
else if (isset($_POST["quoteDtFacture"])) {
    $quoteIDHD = $_POST["quoteDtFacture"];

    $data1 = [];
    $query2 = " SELECT dt.itemName, dt.itemDescription, dt.quantite, dt.quantite2, dt.descriptionUnit, dt.unitPrice2
								 from stores s, quotehd hd, quotedt dt, customers c, utilisateurs u, parametre pm
								 where s.succursale_id = hd.succursale_id 
								 and hd.quoteIDHD = dt.quoteiDHD 
								 and c.customers_id = hd.customers_id
                                  and s.succursale_id = pm.Succursale_id
								 and u.user_id = c.user_id
								 and hd.quoteIDHD=$quoteIDHD";

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
 * Fonction pour verifier si un quote existe deja
 */
else if (isset($_POST["quoteIDHDFound"])) {
    $quoteIDHD = $_POST["quoteIDHDFound"];

    $data = 0;
    $query = "SELECT * FROM quotehd WHERE quoteIDHD=$quoteIDHD AND statut != 'C' AND statut != 'N'";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row['customers_id'];
        }
        echo $data;
    } else echo $data;
    $fetch_data->close();
}


/**
 * Fonction
 */
else if (isset($_POST["quoteIDHDFoundModify"])) {
    $quoteIDHD = $_POST["quoteIDHDFoundModify"];

    $query = " SELECT s.Adresse, s.emailStore, s.Name, s.Telephone1, s.Telephone2, hd.code, hd.quoteNumber, hd.quoteidHD, hd.dateQuote, hd.monnaie, hd.taux,
    c.fullname, c.rue, c.ville, c.telephone1, hd.shipTo, u.nom, c.customers_id, c.payment_term, c.currency, c.customer_type, c.notes, ct.description,
    hd.discountPourcentage, hd.totalItems, hd.discountAmount, hd.taxes, hd.transport, hd.grandTotal
    from stores s, quotehd hd, quotedt dt, customers c, utilisateurs u ,
    customertype ct WHERE c.customer_type = ct.customertype_id
    and s.succursale_id = hd.succursale_id 
    and hd.quoteIDHD = dt.quoteidHD 
    and c.customers_id = hd.customers_id
    and u.user_id = c.user_id
    and hd.quoteIDHD = $quoteIDHD  limit 1";

    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        while ($row = mysqli_fetch_assoc($fetch_data)) {
            $data = $row;
        }
    }
    echo json_encode($data);
}


/**
 * Fonction pour rechercher un quotedt par id quotehd
 */
else if (isset($_POST["searchQuoteTdByIdQuoteHd"])) {
    $quoteIDHD = $_POST["searchQuoteTdByIdQuoteHd"];

    $data1 = [];
    $query = "SELECT * FROM quotedt WHERE quoteidHD=$quoteIDHD";
    $fetch_data = mysqli_query($con, $query);
    $row = mysqli_num_rows($fetch_data);
    if ($row > 0) {
        foreach ($fetch_data as $res) {
            array_push($data1, $res);
        }
    }
    $fetch_data->close();
    echo json_encode($data1);
}

/**
 * Fonction Passer le statut a (C) completer
 */
else if (isset($_POST["statutComplet"])) {
    $quoteIDHD = $_POST["statutComplet"];

    $sql = "UPDATE quotehd SET statut='C' WHERE quoteIDHD=$quoteIDHD";
    $result = mysqli_query($con, $sql);
    $inc = 0;

    if ($result) {
        $inc +=  1;
        $sql_dt = "UPDATE quotedt SET statut='C' WHERE quoteidHD=$quoteIDHD";
        $result_dt = mysqli_query($con, $sql_dt);

        if ($result_dt) {
            $inc +=  1;
        }
    }
    echo $inc;
}

