$(document).ready(function () {
    /*
     * Call plugins select2
     */
    $(".let_choise").select2();
    $(".let_choise1").select2({
        dropdownParent: $("#myModal"),
        width: "resolve",
        theme: "bootstrap4",
        tags: true,
    });

    /*
     ** Call the plugin Datatable
     */
    new DataTable("#myDataTable", {
        layout: {
            topStart: {
                buttons: ["copy", "csv", "excel", "pdf", "print"],
            },
        },
    });
    new DataTable("#myTable");
});

//Call a quote by id
function modifyQuoteGlobalById(id) {
    $.ajax({
        url: "Server_Jquery_Ajax.php",
        method: "post",
        data: {
            quoteIDHDFound: id,
        },
        success: function (response) {
            if (response > 0) {
                // Rechercher les donnees quote correspond par son id
                $.ajax({
                    url: "Server_Jquery_Ajax.php",
                    method: "post",
                    data: {
                        quoteIDHDFoundModify: id,
                    },
                    success: function (result_entete) {
                        let json_entete = $.parseJSON(result_entete);

                        $("#quote_id_save").val(id);
                        $("#customer_id_product").val(json_entete.customers_id);
                        $("#quote_id").val(json_entete.code + "" + json_entete.quoteNumber);
                        $("#date").val(json_entete.dateQuote);

                        $("#notes").val(json_entete.notes);
                        document.getElementById(json_entete.monnaie).selected = "true";
                        if (json_entete.monnaie == "HTG") {
                            $("#exreate").val("<?= $_SESSION['taux'] ?>");
                        }

                        $("#billto").html(
                            json_entete.fullname +
                            "\n" +
                            json_entete.rue +
                            "\n" +
                            json_entete.ville +
                            "\n" +
                            json_entete.telephone1
                        );
                        $("#shipto").html(json_entete.shipTo);
                        $("#customertype").val(json_entete.description);
                        $("#payment_term").val(json_entete.payment_term);
                        $("#salesperson").val(json_entete.nom);

                        $.ajax({
                            url: "Server_Jquery_Ajax.php",
                            method: "post",
                            data: {
                                searchQuoteTdByIdQuoteHd: id,
                            },
                            success: function (result_jsondata) {
                                let jsondata = $.parseJSON(result_jsondata);
                                let quantite = 0;
                                let out = "";

                                $.each(jsondata, function (key, value) {
                                    if (value["orderUnit"] == "1") quantite = value["quantite2"];
                                    if (value["orderUnit"] == "2") quantite = value["quantite"];

                                    out += `<tr>
													<td>${value["itemName"]}</td>
													<td>${value["itemDescription"]}</td>
													<td>${quantite}</td>
													<td>${value["descriptionUnit"]}</td>
													<td>${value["unitPrice1"]}</td>
													<td>${value["unitPrice2"]}</td>
													<td>${quantite * value["unitPrice2"]}</td>
													
													<td>
														<div class="btn-group">
															<button onclick="modifyProductQuoteDtById(${value["quoteidDT"]
                                        })" type="button" id="modifyProduct" class="test btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalModif"> 
															<span><i class="fa fa-edit text-white"></i></span>
															</button>
															<button onclick="deleteProductQuoteDtById(${value["quoteidDT"]
                                        })" type="button" id="deleteProduct" class="test btn btn-danger"> 
																<span><i class="fa fa-trash text-white"></i></span>
															</button>
														</div>
													</td>
												</tr>`;
                                });

                                $("#table_quote").html(out);
                            },
                        });

                        if (json_entete.discountPourcentage > 0.0)
                            document
                                .getElementById("discountPourcent")
                                .setAttribute("value", json_entete.discountPourcentage);
                        if (json_entete.discountAmount > 0.0)
                            document
                                .getElementById("discountAmount")
                                .setAttribute("value", json_entete.discountAmount);
                        if (json_entete.taxes > 0.0)
                            document
                                .getElementById("tax")
                                .setAttribute("value", json_entete.taxes);
                        if (json_entete.transport > 0.0)
                            document
                                .getElementById("shipping")
                                .setAttribute("value", json_entete.transport);
                        if (json_entete.totalItems > 0.0)
                            document
                                .getElementById("subTotal")
                                .setAttribute("value", json_entete.totalItems);
                        if (json_entete.grandTotal > 0.0)
                            document
                                .getElementById("total")
                                .setAttribute("value", json_entete.grandTotal);

                        $(".nav-tabs a").tab("show");
                    },
                });
            } else {
                alert("Please select a quote valid !");
            }
        },
        error: function () {
            alert("Please select a quote valid !");
        },
    });
}

// Search data to modidy a quote dt by id
function modifyProductQuoteDtById(id) {
    let customer_id_product = $("#customer_id_product").val();

    if (id != "") {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                modify_quotedt_by_id: id,
                customer_id_product: customer_id_product,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);

                $.ajax({
                    url: "Server_Jquery_Ajax.php",
                    method: "post",
                    data: {
                        product_unit1: jsondata.produit_id,
                    },
                    success: function (result) {
                        let desc_unit = $.parseJSON(result);

                        let inc = 0;
                        for (let i in desc_unit) {
                            inc++;

                            if (inc == 1) $("#uom_1_modify").html(desc_unit[i]);
                            if (inc == 2) $("#uom_2_modify").html(desc_unit[i]);
                        }

                        let quantity = (total = 0);
                        let unitprice = (regularprice = uom = "");

                        if (jsondata.quantite > 0) {
                            quantity = jsondata.quantite;
                            document.getElementById("uom_2_modify").selected = "true";
                            uom = "UOM 2";
                        }
                        if (jsondata.quantite2 > 0) {
                            quantity = jsondata.quantite2;
                            document.getElementById("uom_1_modify").selected = "true";
                            uom = "UOM 1";
                        }

                        if (jsondata.rabais == "No") {
                            if (uom == "UOM 1") {
                                unitprice = jsondata.regularprice;
                                regularprice = jsondata.regularprice;
                            } else if (uom == "UOM 2") {
                                unitprice = jsondata.uomprice;
                                regularprice = jsondata.uomprice;
                            }
                        } else if (jsondata.rabais == "Yes") {
                            if (jsondata.isRabais == "R") {
                                if (uom == "UOM 1") {
                                    unitprice = jsondata.regularprice;
                                    regularprice = jsondata.regularprice;
                                } else if (uom == "UOM 2") {
                                    unitprice = jsondata.uomprice;
                                    regularprice = jsondata.uomprice;
                                }
                            } else if (jsondata.isRabais == "S") {
                                if (jsondata.discount_type == "P") {
                                    if (uom == "UOM 1") {
                                        unitprice =
                                            jsondata.regularprice -
                                            (jsondata.regularprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        unitprice =
                                            jsondata.uomprice -
                                            (jsondata.uomprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.uomprice;
                                    }
                                } else if (jsondata.discount_type == "F") {
                                    if (uom == "UOM 1") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.regularprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.regularprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.regularprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.regularprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.regularprice5;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.uomprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.uomprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.uomprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.uomprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.uomprice5;
                                        regularprice = jsondata.uomprice;
                                    }
                                }
                            }
                        }

                        if (jsondata.qteProd != null) {
                            total = unitprice * qteProd;
                        }

                        document
                            .getElementById("itemname_modify")
                            .setAttribute("value", jsondata.itemname);
                        $("#itemdescription_modify").html(jsondata.itemdescription);
                        document
                            .getElementById("regularprice_modify")
                            .setAttribute("value", regularprice);
                        document
                            .getElementById("unitprice_modify")
                            .setAttribute("value", unitprice);
                        document
                            .getElementById("onhandqty_modify")
                            .setAttribute("value", jsondata.quantitetotal);
                        document
                            .getElementById("stockdetail_modify")
                            .setAttribute("value", jsondata.quantitetotal);

                        document
                            .getElementById("quantity_modify")
                            .setAttribute("value", quantity);
                        document
                            .getElementById("productTotal_modify")
                            .setAttribute("value", total);

                        document
                            .getElementById("quoteid_dt_modify")
                            .setAttribute("value", id);

                        document
                            .getElementById("product_id_search_modify")
                            .setAttribute("value", jsondata.produit_id);
                    },
                });
            },
        });
    } else {
        alert("Please select a product !");
    }
}

// Modify quote (DT and HD) by id
$("#modifyQuoteById").on("click", function () {
    const quote_id_dt = $("#quoteid_dt_modify").val();

    if (quote_id_dt != null) {
        let quantity = $("#quantity_modify").val();
        let uom = $("#uom_modify").val();
        let unitprice = $("#unitprice_modify").val();
        let regularprice = $("#regularprice_modify").val();
        let exrate = $("#exrate").val();
        let subTotal = 0;

        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                quote_id_dt: quote_id_dt,
                quantity: quantity,
                uom: uom,
                unitprice: unitprice,
                regularprice: regularprice,
                exrate: exrate,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);
                let out = "";
                let quantite = (exrate = subTotal = total = 0);

                if (jsondata != null) {
                    $.each(jsondata, function (key, value) {
                        if (value["orderUnit"] == "1") quantite = value["quantite2"];
                        if (value["orderUnit"] == "2") quantite = value["quantite"];
                        out += `<tr>
                            <td>${value["itemName"]}</td>
                            <td>${value["itemDescription"]}</td>
                            <td>${quantite}</td>
                            <td>${value["descriptionUnit"]}</td>
                            <td>${value["unitPrice1"]}</td>
                            <td>${value["unitPrice2"]}</td>
                            <td>${quantite * value["unitPrice2"]}</td>
                            
                            <td>
                                <div class="btn-group">
                                    <button onclick="modifyProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="modifyProduct" class="test btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalModif"> 
                                    <span><i class="fa fa-edit text-white"></i></span>
                                    </button>
                                    <button onclick="deleteProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="deleteProduct" class="test btn btn-danger"> 
                                        <span><i class="fa fa-trash text-white"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>`;

                        subTotal += quantite * value["unitPrice2"];
                    });

                    let discountAmount = $("#discountAmount").val();
                    let discountPourcent = $("#discountPourcent").val();
                    let shipping = $("#shipping").val();
                    let tax = $("#tax").val();

                    if (discountAmount != "") {
                        total = subTotal - discountAmount + tax * 1 + shipping * 1;
                    } else if (discountPourcent != "") {
                        total =
                            subTotal -
                            (subTotal * discountPourcent) / 100 +
                            (tax * subTotal) / 100 +
                            shipping * 1;
                    } else {
                        total = subTotal;
                    }

                    $("#table_quote").html(out);
                    document.getElementById("subTotal").setAttribute("value", subTotal);
                    document.getElementById("total").setAttribute("value", total);

                    alert("Record modify successfully");
                } else {
                    alert("Error modify record");
                }
            },
        });
    } else alert("Please select a product or a customer !");
});

// Delete a product by id
function deleteProductQuoteDtById(id) {
    if (id != "") {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                delete_quotedt_by_id: id,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);
                let quantite = (subTotal = total = 0);
                let exrate = $("#exrate").val();

                if (jsondata != null) {
                    let out = "";

                    $.each(jsondata, function (key, value) {
                        if (value["orderUnit"] == "1") quantite = value["quantite2"];
                        if (value["orderUnit"] == "2") quantite = value["quantite"];
                        out += `<tr>
                            <td>${value["itemName"]}</td>
                            <td>${value["itemDescription"]}</td>
                            <td>${quantite}</td>
                            <td>${value["descriptionUnit"]}</td>
                            <td>${value["unitPrice1"]}</td>
                            <td>${value["unitPrice2"]}</td>
                            <td>${quantite * value["unitPrice2"]}</td>
                            
                            <td>
                                <div class="btn-group">
                                    <button onclick="modifyProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="modifyProduct" class="test btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalModif"> 
                                    <span><i class="fa fa-edit text-white"></i></span>
                                    </button>
                                    <button onclick="deleteProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="deleteProduct" class="test btn btn-danger"> 
                                        <span><i class="fa fa-trash text-white"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>`;

                        subTotal += quantite * value["unitPrice2"];
                    });

                    let discountAmount = $("#discountAmount").val();
                    let discountPourcent = $("#discountPourcent").val();
                    let shipping = $("#shipping").val();
                    let tax = $("#tax").val();

                    if (discountAmount != "") {
                        total = subTotal - discountAmount + tax * 1 + shipping * 1;
                    } else if (discountPourcent != "") {
                        total =
                            subTotal -
                            (subTotal * discountPourcent) / 100 +
                            (tax * subTotal) / 100 +
                            shipping * 1;
                    } else {
                        total = subTotal;
                    }

                    $("#table_quote").html(out);
                    document.getElementById("subTotal").setAttribute("value", subTotal);
                    document.getElementById("total").setAttribute("value", total);

                    alert("Record deleted successfully");
                } else {
                    alert("Error deleting record");
                }
            },
        });
    } else {
        alert("Please select a product !");
    }
}

// Fonction to change Ex rate (Taux du jour)
function getSearchTypeMoney(currencyMoney, fmoney, dmoney) {
    let currency = 0;
    if (currencyMoney != null) {
        if (currencyMoney == "USD") {
            document.getElementById("exrate").setAttribute("value", dmoney);
            currency = dmoney;
        }
        if (currencyMoney == "HTG") {
            document.getElementById("exrate").setAttribute("value", fmoney);
            currency = fmoney;
        }

        let customer_id_product = $("#customer_id_product").val();
        let change_quotedt_money = $("#quote_id_save").val();
        if (change_quotedt_money != "") {
            $.ajax({
                url: "Server_Jquery_Ajax.php",
                method: "post",
                data: {
                    change_quotedt_money: change_quotedt_money,
                    currency: currency,
                    currencyMoney: currencyMoney,
                },
                success: function (response) {
                    let jsondata = $.parseJSON(response);
                    let quantite = (total = subTotal = unitprice = regularprice = 0);
                    let out = "";

                    $("#table_quote").html("");
                    $.each(jsondata, function (key, value) {
                        if (value["orderUnit"] == "1") quantite = value["quantite2"];
                        if (value["orderUnit"] == "2") quantite = value["quantite"];
                        out += `<tr>
                            <td>${value["itemName"]}</td>
                            <td>${value["itemDescription"]}</td>
                            <td>${quantite}</td>
                            <td>${value["descriptionUnit"]}</td>
                            <td>${value["unitPrice1"]}</td>
                            <td>${value["unitPrice2"]}</td>
                            <td>${quantite * value["unitPrice2"]}</td>
                            
                            <td>
                                <div class="btn-group">
                                    <button onclick="modifyProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="modifyProduct" class="test btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalModif"> 
                                    <span><i class="fa fa-edit text-white"></i></span>
                                    </button>
                                    <button onclick="deleteProductQuoteDtById(${value["quoteidDT"]
                            })" type="button" id="deleteProduct" class="test btn btn-danger"> 
                                        <span><i class="fa fa-trash text-white"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>`;

                        subTotal += quantite * value["unitPrice2"];
                    });

                    let discountAmount = $("#discountAmount").val();
                    let discountPourcent = $("#discountPourcent").val();
                    let shipping = $("#shipping").val();
                    let tax = $("#tax").val();

                    if (discountAmount != "") {
                        total = subTotal - discountAmount * 1 + tax * 1 + shipping * 1;
                        discountAmount = discountAmount * 1;
                    } else if (discountPourcent != "") {
                        total =
                            subTotal -
                            (subTotal * discountPourcent) / 100 +
                            (tax * 1 * subTotal) / 100 +
                            shipping * 1;
                        discountPourcent = discountPourcent * 1;
                    } else {
                        total = subTotal - discountAmount * 1 + tax * 1 + shipping * 1;
                    }

                    document.getElementById("total").setAttribute("value", total);
                    document.getElementById("subTotal").setAttribute("value", subTotal);

                    $("#discountAmount").val(discountAmount);
                    $("#discountPourcent").val(discountPourcent);
                    $("#shipping").val(shipping);
                    $("#tax").val(tax);
                    $(".table_quote_modif").html(out);
                },
            });
        }
    } else alert("Please select a product !");
}

// for search a customer by id
function getSearchCustomerById(val) {
    if (val != null) {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: "customer_id=" + val,
            success: function (result) {
                let jsondata = $.parseJSON(result);
                $("#notes").html(jsondata.notes);
                $("#billto").html(
                    jsondata.fullname + "\n" + jsondata.rue + "\n" + jsondata.ville
                );
                $("#shipto").html(
                    jsondata.fullname +
                    "\n" +
                    jsondata.rue +
                    "\n" +
                    jsondata.ville +
                    "\n" +
                    jsondata.telephone1
                );

                // Reinitialiser les autres champs a vide
                $("#table_quote").html("");
                document.getElementById("discountPourcent").setAttribute("value", "");
                document.getElementById("discountAmount").setAttribute("value", "");
                document.getElementById("tax").setAttribute("value", "");
                document.getElementById("shipping").setAttribute("value", "");
                document.getElementById("subTotal").setAttribute("value", "");
                document.getElementById("total").setAttribute("value", "");
                $("#quote_id").val("");
                document.getElementById("USD").selected = "true";

                // update l'Id quote a chaque fois qu'un customer est choisir
                //Initialiser le a sauvegarder
                document
                    .getElementById("quote_id_save")
                    .setAttribute("value", "sauvegarder");

                document
                    .getElementById("customertype")
                    .setAttribute("value", jsondata.description);
                document
                    .getElementById("payment_term")
                    .setAttribute("value", jsondata.payment_term);
                document
                    .getElementById("salesperson")
                    .setAttribute("value", jsondata.nom + "," + jsondata.prenom);
                document
                    .getElementById("customer_id_product")
                    .setAttribute("value", jsondata.customers_id);
            },
            error: function () {
                alert("Please select a customer !");
            },
        });
    } else alert("Please select a customer !");
}

// for search a product by id product and id customer
function getSearchProductById(product_id) {
    let customer_id_product = $("#customer_id_product").val();
    let qteProd = $("#quantity").val();
    let uom = $("#uom").val();
    let monnaie = $("#currency").val();
    let currency = $("#exrate").val();

    if (product_id != null && customer_id_product != null) {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                product_id: product_id,
                customer_id_product: customer_id_product,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);
                $.ajax({
                    url: "Server_Jquery_Ajax.php",
                    method: "post",
                    data: {
                        product_unit1: product_id,
                    },
                    success: function (result) {
                        let desc_unit = $.parseJSON(result);

                        let inc = 0;
                        for (let i in desc_unit) {
                            inc++;

                            if (inc == 1) $("#uom_1").html(desc_unit[i]);
                            if (inc == 2) $("#uom_2").html(desc_unit[i]);
                        }

                        let unitprice = "";
                        let regularprice = "";
                        let total = 0;

                        if (jsondata.rabais == "No") {
                            if (uom == "UOM 1") {
                                unitprice = jsondata.regularprice;
                                regularprice = jsondata.regularprice;
                            } else if (uom == "UOM 2") {
                                unitprice = jsondata.uomprice;
                                regularprice = jsondata.uomprice;
                            }
                        } else if (jsondata.rabais == "Yes") {
                            if (jsondata.isRabais == "R") {
                                if (uom == "UOM 1") {
                                    unitprice = jsondata.regularprice;
                                    regularprice = jsondata.regularprice;
                                } else if (uom == "UOM 2") {
                                    unitprice = jsondata.uomprice;
                                    regularprice = jsondata.uomprice;
                                }
                            } else if (jsondata.isRabais == "S") {
                                if (jsondata.discount_type == "P") {
                                    if (uom == "UOM 1") {
                                        unitprice =
                                            jsondata.regularprice -
                                            (jsondata.regularprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        unitprice =
                                            jsondata.uomprice -
                                            (jsondata.uomprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.uomprice;
                                    }
                                } else if (jsondata.discount_type == "F") {
                                    if (uom == "UOM 1") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.regularprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.regularprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.regularprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.regularprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.regularprice5;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.uomprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.uomprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.uomprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.uomprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.uomprice5;
                                        regularprice = jsondata.uomprice;
                                    }
                                }
                            }
                        }

                        let quantiteTotalProduct = jsondata.quantitetotal;
                        let baseunit = jsondata.baseunit;
                        let qtTotalProductByUom = 0;
                        if (uom == "UOM 1") {
                            let trunc_total = quantiteTotalProduct / baseunit;
                            qtTotalProductByUom = Math.trunc(trunc_total);
                        } else if (uom == "UOM 2") {
                            qtTotalProductByUom = quantiteTotalProduct;
                        }

                        if (qteProd != null) {
                            if (monnaie == "HTG") {
                                unitprice = unitprice * currency;
                                regularprice = regularprice * currency;
                                total = unitprice * currency * qteProd;
                            } else if (monnaie == "USD") {
                                total = unitprice * qteProd;
                            }
                        }

                        document
                            .getElementById("regularprice")
                            .setAttribute("value", regularprice);
                        document
                            .getElementById("unitprice")
                            .setAttribute("value", unitprice);
                        document
                            .getElementById("productTotal")
                            .setAttribute("value", total);
                        document
                            .getElementById("onhandqty")
                            .setAttribute("value", qtTotalProductByUom);
                        document
                            .getElementById("product_id_search")
                            .setAttribute("value", jsondata.produit_id);
                        document
                            .getElementById("stockdetail")
                            .setAttribute("value", qtTotalProductByUom);

                        // var row_id = $('#InputRow' + jsondata.produit_id).val();
                        // if (row_id) {
                        // 	$('#itemdescription').val(row_id);
                        // 	$('#itemdescription').trigger('change');
                        // }
                    },
                });
            },
            error: function () {
                alert("Please select a product !");
            },
        });
    } else alert("Please select a customer or a product");
}

// Change UOM for find different price
function getChangeUOM(uom) {
    let customer_id_product = $("#customer_id_product").val();
    let product_id = $("#product_id_search").val();

    let qteProd = $("#quantity").val();
    let monnaie = $("#currency").val();
    let currency = $("#exrate").val();

    if (product_id != null && customer_id_product != null) {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                product_id: product_id,
                customer_id_product: customer_id_product,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);
                $.ajax({
                    url: "Server_Jquery_Ajax.php",
                    method: "post",
                    data: {
                        product_unit1: product_id,
                    },
                    success: function (result) {
                        let desc_unit = $.parseJSON(result);

                        let inc = 0;
                        for (let i in desc_unit) {
                            inc++;

                            if (inc == 1) $("#uom_1").html(desc_unit[i]);
                            if (inc == 2) $("#uom_2").html(desc_unit[i]);
                        }

                        let unitprice = "";
                        let regularprice = "";
                        let total = 0;

                        if (jsondata.rabais == "No") {
                            if (uom == "UOM 1") {
                                unitprice = jsondata.regularprice;
                                regularprice = jsondata.regularprice;
                            } else if (uom == "UOM 2") {
                                unitprice = jsondata.uomprice;
                                regularprice = jsondata.uomprice;
                            }
                        } else if (jsondata.rabais == "Yes") {
                            if (jsondata.isRabais == "R") {
                                if (uom == "UOM 1") {
                                    unitprice = jsondata.regularprice;
                                    regularprice = jsondata.regularprice;
                                } else if (uom == "UOM 2") {
                                    unitprice = jsondata.uomprice;
                                    regularprice = jsondata.uomprice;
                                }
                            } else if (jsondata.isRabais == "S") {
                                if (jsondata.discount_type == "P") {
                                    if (uom == "UOM 1") {
                                        unitprice =
                                            jsondata.regularprice -
                                            (jsondata.regularprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        unitprice =
                                            jsondata.uomprice -
                                            (jsondata.uomprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.uomprice;
                                    }
                                } else if (jsondata.discount_type == "F") {
                                    if (uom == "UOM 1") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.regularprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.regularprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.regularprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.regularprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.regularprice5;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.uomprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.uomprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.uomprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.uomprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.uomprice5;
                                        regularprice = jsondata.uomprice;
                                    }
                                }
                            }
                        }

                        let quantiteTotalProduct = jsondata.quantitetotal;
                        let baseunit = jsondata.baseunit;
                        let qtTotalProductByUom = 0;
                        if (uom == "UOM 1") {
                            let trunc_total = quantiteTotalProduct / baseunit;
                            qtTotalProductByUom = Math.trunc(trunc_total);
                        } else if (uom == "UOM 2") {
                            qtTotalProductByUom = quantiteTotalProduct;
                        }

                        if (qteProd != null) {
                            if (monnaie == "HTG") {
                                unitprice = unitprice * currency;
                                regularprice = regularprice * currency;
                                total = unitprice * currency * qteProd;
                            } else if (monnaie == "USD") {
                                total = unitprice * qteProd;
                            }
                        }

                        document
                            .getElementById("regularprice")
                            .setAttribute("value", regularprice);
                        document
                            .getElementById("unitprice")
                            .setAttribute("value", unitprice);
                        document
                            .getElementById("productTotal")
                            .setAttribute("value", total);
                        document
                            .getElementById("onhandqty")
                            .setAttribute("value", qtTotalProductByUom);
                        document
                            .getElementById("product_id_search")
                            .setAttribute("value", jsondata.produit_id);
                        document
                            .getElementById("stockdetail")
                            .setAttribute("value", qtTotalProductByUom);
                    },
                });
            },
            error: function () {
                alert("Please select a product !");
            },
        });
    } else alert("Please select a customer or a product");
}

function getChangeUOMProductModify(uom) {
    let customer_id_product = $("#customer_id_product").val();
    let product_id = $("#product_id_search_modify").val();

    let qteProd = $("#quantity_modify").val();
    let monnaie = $("#currency").val();
    let currency = $("#exrate").val();

    if (product_id != null && customer_id_product != null) {
        $.ajax({
            url: "Server_Jquery_Ajax.php",
            method: "post",
            data: {
                product_id: product_id,
                customer_id_product: customer_id_product,
            },
            success: function (response) {
                let jsondata = $.parseJSON(response);
                $.ajax({
                    url: "Server_Jquery_Ajax.php",
                    method: "post",
                    data: {
                        product_unit1: product_id,
                    },
                    success: function (result) {
                        let desc_unit = $.parseJSON(result);

                        let inc = 0;
                        for (let i in desc_unit) {
                            inc++;

                            if (inc == 1) $("#uom_1_modify").html(desc_unit[i]);
                            if (inc == 2) $("#uom_2_modify").html(desc_unit[i]);
                        }

                        let unitprice = "";
                        let regularprice = "";
                        let total = 0;

                        if (jsondata.rabais == "No") {
                            if (uom == "UOM 1") {
                                unitprice = jsondata.regularprice;
                                regularprice = jsondata.regularprice;
                            } else if (uom == "UOM 2") {
                                unitprice = jsondata.uomprice;
                                regularprice = jsondata.uomprice;
                            }
                        } else if (jsondata.rabais == "Yes") {
                            if (jsondata.isRabais == "R") {
                                if (uom == "UOM 1") {
                                    unitprice = jsondata.regularprice;
                                    regularprice = jsondata.regularprice;
                                } else if (uom == "UOM 2") {
                                    unitprice = jsondata.uomprice;
                                    regularprice = jsondata.uomprice;
                                }
                            } else if (jsondata.isRabais == "S") {
                                if (jsondata.discount_type == "P") {
                                    if (uom == "UOM 1") {
                                        unitprice =
                                            jsondata.regularprice -
                                            (jsondata.regularprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        unitprice =
                                            jsondata.uomprice -
                                            (jsondata.uomprice * jsondata.discount_level) / 100;
                                        regularprice = jsondata.uomprice;
                                    }
                                } else if (jsondata.discount_type == "F") {
                                    if (uom == "UOM 1") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.regularprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.regularprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.regularprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.regularprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.regularprice5;
                                        regularprice = jsondata.regularprice;
                                    }

                                    if (uom == "UOM 2") {
                                        if (jsondata.price_list == "1")
                                            unitprice = jsondata.uomprice1;
                                        else if (jsondata.price_list == "2")
                                            unitprice = jsondata.uomprice2;
                                        else if (jsondata.price_list == "3")
                                            unitprice = jsondata.uomprice3;
                                        else if (jsondata.price_list == "4")
                                            unitprice = jsondata.uomprice4;
                                        else if (jsondata.price_list == "5")
                                            unitprice = jsondata.uomprice5;
                                        regularprice = jsondata.uomprice;
                                    }
                                }
                            }
                        }

                        let quantiteTotalProduct = jsondata.quantitetotal;
                        let baseunit = jsondata.baseunit;
                        let qtTotalProductByUom = 0;
                        if (uom == "UOM 1") {
                            let trunc_total = quantiteTotalProduct / baseunit;
                            qtTotalProductByUom = Math.trunc(trunc_total);
                        } else if (uom == "UOM 2") {
                            qtTotalProductByUom = quantiteTotalProduct;
                        }

                        if (qteProd != null) {
                            if (monnaie == "HTG") {
                                unitprice = unitprice * currency;
                                regularprice = regularprice * currency;
                                total = unitprice * currency * qteProd;
                            } else if (monnaie == "USD") {
                                total = unitprice * qteProd;
                            }
                        }

                        document
                            .getElementById("itemname_modify")
                            .setAttribute("value", jsondata.itemname);
                        $("#itemdescription_modify").html(jsondata.itemdescription);
                        document
                            .getElementById("regularprice_modify")
                            .setAttribute("value", regularprice);
                        document
                            .getElementById("unitprice_modify")
                            .setAttribute("value", unitprice);
                        document
                            .getElementById("onhandqty_modify")
                            .setAttribute("value", qtTotalProductByUom);
                        document
                            .getElementById("stockdetail_modify")
                            .setAttribute("value", qtTotalProductByUom);

                        document
                            .getElementById("quantity_modify")
                            .setAttribute("value", qteProd);
                        document
                            .getElementById("productTotal_modify")
                            .setAttribute("value", total);
                    },
                });
            },
            error: function () {
                alert("Please select a product !");
            },
        });
    } else alert("Please select a customer or a product");
}

// for search a quantity of a product
$("#quantity").on("keyup", function () {
    let qteProd = $("#quantity").val();
    let unitprice = $("#unitprice").val();
    let onhandqty = $("#onhandqty").val();
    let total = 0;

    if (unitprice != null) {
        if (qteProd * 1 > onhandqty * 1) {
            alert("The quantity selected must be below the stock quantity");
            $("#quantity").val("");
        } else total = unitprice * qteProd;
    }
    document.getElementById("productTotal").setAttribute("value", total);
});

$("#quantity_modify").on("keyup", function () {
    let qteProd = $("#quantity_modify").val();
    let unitprice = $("#unitprice_modify").val();
    let onhandqty = $("#onhandqty_modify").val();
    let total = 0;

    if (unitprice != null) {
        if (qteProd * 1 > onhandqty * 1) {
            alert("The quantity selected must be below the stock quantity");
            $("#quantity_modify").val("");
        } else total = unitprice * qteProd;
    }
    document.getElementById("productTotal_modify").setAttribute("value", total);
});

// for save a customer on click add product
$("#addProduct").on("click", function () {
    let addProduct = $("#customer_id_product").val();
    let quote_id = $("#quote_id_save").val();
    let currency = $("#currency").val();
    let notes = $("#notes").val();
    let page_url = document.location.href;

    $.ajax({
        url: "Server_Jquery_Ajax.php",
        method: "post",
        data: {
            addProduct: addProduct,
        },
        success: function (response) {
            if (response == false) {
                alert("Please select a customer");
                window.location.href = page_url;
            } else {
                if (quote_id == "sauvegarder") {
                    $.ajax({
                        url: "Server_Jquery_Ajax.php",
                        method: "post",
                        data: {
                            saveQuoteHd_part1: quote_id,
                            notes: notes,
                            currency: currency,
                            customer_id_product: addProduct,
                        },
                        success: function (result) {
                            document
                                .getElementById("quote_id_save")
                                .setAttribute("value", result);
                        },
                    });
                }
            }
        },
        error: function () {
            alert("Please select a customer");
            window.location.href = page_url;
        },
    });
});

// for save a product on the modal
function save_product_modal_by_type_page(type_page) {
    let quote_id_save = $("#quote_id_save").val();
    let produit_id = $("#product_id_search").val();

    let quantity = $("#quantity").val();
    let monnaie = $("#currency").val();
    let uom = $("#uom").val();
    let unitprice = $("#unitprice").val();
    let regularprice = $("#regularprice").val();
    let subTotal = 0;

    let discountAmount = $("#discountAmount").val();
    let discountPourcent = $("#discountPourcent").val();
    let shipping = $("#shipping").val();
    let tax = $("#tax").val();

    if (quantity > 0) {
        if (quote_id_save != "sauvegarder") {
            $.ajax({
                url: "Server_Jquery_Ajax.php",
                method: "post",
                data: {
                    codequotehd: quote_id_save,
                    produit_id: produit_id,
                    quantity: quantity,
                    monnaie: monnaie,
                    uom: uom,
                    unitprice: unitprice,
                    regularprice: regularprice,
                },
                success: function (response) {
                    if (response == true) {
                        // verifier si la methode a appeller a partir du Invoice
                        if (type_page == "Invoice") {
                            $.ajax({
                                url: "Server_Jquery_Ajax.php",
                                method: "post",
                                data: {
                                    quote_id_save_invoice: quote_id_save,
                                    produit_id: produit_id,
                                    quantity: quantity,
                                    unitprice: unitprice,
                                },
                                success: function (responseTrue) {
                                    if (responseTrue == true) {
                                        document.getElementById("saveNewInvoice").disabled = true;
                                        document.getElementById("savePrintInvoice").disabled = true;
                                    }
                                },
                            });
                        }

                        // verifier si la methode a appeller a partir du Quote
                        else if (type_page == "Quote") {
                            document.getElementById("saveNew").disabled = false;
                            document.getElementById("savePrint").disabled = false;
                        }

                        $.ajax({
                            url: "Server_Jquery_Ajax.php",
                            method: "post",
                            data: { viewQuoteDtByID: quote_id_save },
                            success: function (responseViewDt) {
                                let jsondata = $.parseJSON(responseViewDt);
                                let quantite = 0;
                                let out = "";

                                $.each(jsondata, function (key, value) {
                                    if (value["orderUnit"] == "1") quantite = value["quantite2"];
                                    if (value["orderUnit"] == "2") quantite = value["quantite"];
                                    out += `<tr>
                              <td>${value["itemName"]}</td>
                              <td>${value["itemDescription"]}</td>
                              <td>${quantite}</td>
                              <td>${value["descriptionUnit"]}</td>
                              <td>${value["unitPrice1"]}</td>
                              <td>${value["unitPrice2"]}</td>
                              <td>${quantite * value["unitPrice2"]}</td>
                              
                              <td>
                                  <div class="btn-group">
                                      <button onclick="modifyProductQuoteDtById(${value["quoteidDT"]
                                        })" type="button" id="modifyProduct" class="test btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalModif"> 
                                      <span><i class="fa fa-edit text-white"></i></span>
                                      </button>
                                      <button onclick="deleteProductQuoteDtById(${value["quoteidDT"]
                                        })" type="button" id="deleteProduct" class="test btn btn-danger"> 
                                          <span><i class="fa fa-trash text-white"></i></span>
                                      </button>
                                  </div>
                              </td>
                          </tr>`;

                                    subTotal += quantite * value["unitPrice2"];
                                });

                                $("#table_quote").html(out);
                                let selectProduct = document.getElementById("itemname");
                                selectProduct.selectedIndex = 0;
                                document
                                    .getElementById("regularprice")
                                    .setAttribute("value", "");
                                document.getElementById("unitprice").setAttribute("value", "");
                                document
                                    .getElementById("productTotal")
                                    .setAttribute("value", "");
                                document.getElementById("onhandqty").setAttribute("value", "");
                                document
                                    .getElementById("subTotal")
                                    .setAttribute("value", subTotal);

                                let total = 0;
                                if (discountAmount != "") {
                                    total = subTotal - (discountAmount + tax * 1 + shipping * 1);
                                } else if (discountPourcent != "") {
                                    total =
                                        subTotal -
                                        (subTotal * discountPourcent) / 100 +
                                        (tax * subTotal) / 100 +
                                        shipping * 1;
                                } else {
                                    total = subTotal - discountAmount + tax * 1 + shipping * 1;
                                }

                                document.getElementById("total").setAttribute("value", total);
                                $("#quantity").val("");
                                $("#stockdetail").val("");
                            },
                        });
                    }
                },
            });
        } else {
            alert("Please your quote it not valid !");
        }
    } else {
        alert("Please enter a quantity of the product you want !");
    }
}

// Fonctions for call a input discount pourcent
$("#discountPourcent").on("keyup", function () {
    let pourcent = $("#discountPourcent").val();
    let shipping = $("#shipping").val();
    let subTotal = $("#subTotal").val();
    let tax = $("#tax").val();

    let total =
        subTotal -
        (subTotal * pourcent) / 100 +
        (tax * subTotal) / 100 +
        shipping * 1;
    document.getElementById("total").setAttribute("value", total);
    $("#discountAmount").val("");
});

// Fonctions for call a input discount pourcent
$("#discountAmount").on("keyup", function () {
    let pourcent = $("#discountAmount").val();
    let subTotal = $("#subTotal").val();
    let shipping = $("#shipping").val();
    let tax = $("#tax").val();

    let total = subTotal - pourcent + tax * 1 + shipping * 1;
    document.getElementById("total").setAttribute("value", total);
    $("#discountPourcent").val("");
});

// Fonctions for call a input tax
$("#tax").on("keyup", function () {
    let discountAmount = $("#discountAmount").val();
    let discountPourcent = $("#discountPourcent").val();
    let subTotal = $("#subTotal").val();
    let shipping = $("#shipping").val();
    let tax = $("#tax").val();
    let total = 0;

    if (discountAmount != "") {
        total = subTotal - discountAmount + tax * 1 + shipping * 1;
    } else if (discountPourcent != "") {
        total =
            subTotal -
            (subTotal * discountPourcent) / 100 +
            (tax * subTotal) / 100 +
            shipping * 1;
    } else {
        total = subTotal - discountAmount + tax * 1 + shipping * 1;
    }
    document.getElementById("total").setAttribute("value", total);
});

// Fonctions for call a input shipping
$("#shipping").on("keyup", function () {
    let discountAmount = $("#discountAmount").val();
    let discountPourcent = $("#discountPourcent").val();
    let subTotal = $("#subTotal").val();
    let shipping = $("#shipping").val();
    let tax = $("#tax").val();
    let total = 0;

    if (discountAmount != "") {
        total = subTotal - discountAmount + tax * 1 + shipping * 1;
    } else if (discountPourcent != "") {
        total =
            subTotal -
            (subTotal * discountPourcent) / 100 +
            (tax * subTotal) / 100 +
            shipping * 1;
    } else {
        total = subTotal * 1 + shipping * 1;
    }
    document.getElementById("total").setAttribute("value", total);
});
