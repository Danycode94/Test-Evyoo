<?php
//   * * * * * * * * * * * * 
//      I N V O I C E      *
//   * * * * * * * * * * * *          

if (!isset($_SESSION)) {
	session_start();
}
if ($_SESSION["AC04"] == 'NO') {
	require_once("pas_access.php");
	exit;
}
include "../Configuration/parser.php";
require_once("headerPlatform.php");
include('../Configuration/config.php');
include("../Configuration/config1.php");

// define variables and set to empty values
$notes = $customertype = $paymentterm = $salesperson = "";
$cash = "";
$notesErr = $qte1 = "";

?>

<div class="m-2">
	<ul class="nav nav-tabs" id="myTab">
		<li class="nav-item">
			<a href="#pane_invoice_help" class="nav-link" data-bs-toggle="tab">Help</a>
		</li>
		<li class="nav-item">
			<a href="#pane_invoice_list" class="nav-link" data-bs-toggle="tab">Invoice list</a>
		</li>
		<li class="nav-item">
			<a href="#pane_invoice" class="nav-link active" data-bs-toggle="tab">Invoice</a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade" id="pane_invoice_list">
			<section class="intro mt-4">
				<div class="mask d-flex align-items-center h-100">
					<div class="container-fluid">
						<div class="row justify-content-center">
							<div class="col-12">
								<div class="table-responsive-xxl table-scroll">
									<table class="table table-bordered table-striped table- table-hover" id="myDataTable">

										<thead style="background-color:yellow;">
											<tr>
												<th class="col-sm-1">Date</th>
												<th class="col-sm-1">Statut</th>
												<th class="col-sm-1">Invoce #</th>
												<th class="col-sm-2">Cashier </th>
												<th class="col-sm-2">Full name </th>
												<th class="col-sm-1">Total qty. </th>
												<th class="col-sm-2">Total amount </th>
												<th class="col-sm-1">Currency </th>
												<th class="col-sm-1">Action </th>
											</tr>
										</thead>
										<tbody>
											<?php
											$ret = mysqli_query($con, "SELECT q.dateInvoice,q.statut,q.code,q.invoiceNumber,q.quantiteItems,q.totalItems,q.monnaie,q.invoiceIDHD,q.userc_id,q.customers_id,u.user_id,u.nom,u.prenom,c.customers_id,c.fullname
											FROM invoiceHD q INNER JOIN utilisateurs u ON q.userc_id = u.user_id INNER JOIN customers c ON q.customers_id = c.customers_id WHERE q.statut = 'E' ORDER BY q.dateInvoice");

											$cnt = 1;
											while ($row = mysqli_fetch_array($ret)) {

											?>
												<tr class="gradeX">
													<td><?php echo date("d-m-Y", strtotime($row['dateInvoice'])); ?></td>
													<td><?php echo $row['statut']; ?></td>
													<td><?php echo $row['code'] . $row['invoiceNumber']; ?></td>
													<td><?php echo $row['nom'] . ", " . $row['prenom']; ?></td>
													<td><?php echo $row['fullname']; ?></td>
													<td><?php echo $row['quantiteItems']; ?></td>
													<td><?php echo $row['totalItems']; ?></td>
													<td><?php echo $row['monnaie']; ?></td>
													<td class="center"><a href="#?editid=<?php echo $row['invoiceIDHD']; ?>"><i class=" fa fa-edit"></i></a></td>
												</tr>
											<?php
												$cnt = $cnt + 1;
											} ?>

										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>

		<div class="tab-pane fade show active" id="pane_invoice">
			<div class="border mt-4 border-3 border-primary bg-light card color">
				<div>
					<form class="row gy-3 gy-md-4 gy-lg-0 align-items-xl-center mx-2" id="form_invoice">
						<div class="col-12 col-lg-12 mb-4">
							<div class="row align-items-center">
								<div class="col-12 col-lg-6">
									<input type="hidden" name="quote_id_save" id="quote_id_save">
									<input type="hidden" name="product_id_search" id="product_id_search">
									<input type="hidden" name="customer_id_product" id="customer_id_product">
									<input type="hidden" name="invoice_id" id="invoice_id">
									<input type="hidden" name="product_id_search_modify" id="product_id_search_modify">

									<div class="row mt-4">
										<div class="col">
											<button class="btn btn-light" type="button" onclick="readQuoteNumber()">
												<i class="ace-icon fa fa-exchange"></i>
												<span class="bigger-110">Import</span>
											</button>
										</div>
										<div class="col mb-3">
											<button class="btn btn-light" type="button" name="sauvegarder" value="sauvegarder">
												<i class="ace-icon fa fa-undo"></i>
												<span class="bigger-110">Void</span>
											</button>
										</div>
										<div class="col mb-3">
											<button class="btn btn-light" type="button" name="sauvegarder" value="sauvegarder">
												<i class="ace-icon fa fa-print"></i>
												<span class="bigger-110">Print</span>
											</button>
										</div>
										<div class="col mb-3">
											<button class="btn btn-light" type="button" name="sauvegarder" value="sauvegarder">
												<i class="ace-icon fa fa-copy"></i>
												<span class="bigger-110">Copy</span>
											</button>
										</div>
										<div class="col mb-3">
											<button class="btn btn-light" type="button" name="sauvegarder" value="sauvegarder">
												<i class="ace-icon fa fa-copy"></i>
												<span class="bigger-110">Hold</span>
											</button>
										</div>
									</div>
								</div>

								<div class="col-12 col-lg-6">
									<div class="row">
										<div class="col-4">
											<label for="quote_id">Invoice # &nbsp; &nbsp;</label>
											<input type="text" name="quote_id" id="invoice_code" class="form-control form-control-sm" readonly>
										</div>
										<div class="col-4 ">
											<label for="date">Date &nbsp; &nbsp;</label>
											<input type="text" name="date" id="date" value="<?php echo $_SESSION['datetoday']; ?>" class="form-control form-control-sm" readonly>
										</div>
										<div class="col-4">
											<label for="exrate">Ex. rate &nbsp; &nbsp; </label>
											<input type="text" name="exrate" id="exrate" value="<?php echo number_format($_SESSION["tauxDollar"], 2); ?>" class="form-control form-control-sm" readonly>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-12 mb-4">
							<div class="row">
								<div class="col-12 col-lg-5 px-4 py-3 rounded" style="background-color:powderblue;border: 1px solid blue;">
									<div class="row input-group-sm mb-3">
										<div class="col-12 col-lg-3">
											<label for="customers_id">Customer </label>
										</div>

										<div class="card-body p-0 col-12 col-lg-9">

											<!-- AUTO COMPLETE DROPDOWN -->
											<select name="customers_id" id="customers_id" class="selectpicker form-select border-0 mb-1 rounded shadow" onchange="getSearchCustomerById(this.value)">
												<option id="cust_0">Select a customer</option>
												<?php $query = mysqli_query($con, "select * from customers ");
												while ($row = mysqli_fetch_array($query)) {
												?>
													<option value="<?php echo $row['customers_id']; ?>" id="<?php echo "cust_" . $row['customers_id']; ?>"><?php echo $row['nom_compagnie']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>

									<div class="row input-group">
										<div class="col-12 col-lg-2">
											<label for="notes">Notes </label>
										</div>

										<div class="col-12 col-lg-8 mb-3">
											<textarea class="form-control form-control-sm" rows="4" id="notes" name="notes" readonly></textarea>
										</div>
										<div class="col-12 col-lg-2">
											<select class="form-select text-danger" id="currency" name="currency" style="width: 90px;" onchange="getSearchTypeMoney(this.value,'<?= number_format($_SESSION['taux'], 2); ?>','<?= number_format($_SESSION['tauxDollar'], 2); ?>')">
												<option id="USD" value="USD">USD</option>
												<option id="HTG" value="HTG">HTG</option>
											</select>
										</div>
									</div>
								</div>

								<div class="col-12 col-lg-7 px-4 py-3 rounded mt-4 mt-lg-0" style="background-color:powderblue;border: 1px solid blue;">
									<div class="row">
										<div class="col-12 col-lg-6 mb-3">
											<div class="row">
												<div class="mb-3">
													<label class="text-bg-primary px-2" for="billto">Bill to </label>
												</div>
												<div class="col-12">
													<textarea class="form-control form-control-sm bg-light" rows="5" id="billto" name="billto" readonly></textarea>
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6 mb-3">
											<div class="row">
												<div class="mb-3">
													<label class="text-bg-primary px-2" for="shipto">Ship to</label>
												</div>
												<div class="col-12">
													<textarea class="form-control form-control-sm" rows="5" id="shipto" name="shipto"></textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-12 mb-4">
							<div class="row">
								<div class="col-12">
									<div class="row input-group mt-2 mb-2">
										<div class="col-12 col-lg-1">
											<label for="message">Message </label>
										</div>

										<div class="col-12 col-lg-5">
											<select type="text" class="span11 form-select" name="message" id="message" onChange="getSubCat(this.value)" value="unit2id" required='true'>
												<option>Select a Message</option>
												<?php $query = mysqli_query($con, "select * from messages ");
												while ($row = mysqli_fetch_array($query)) {
												?>
													<option value="<?php echo $row['message_id']; ?>"><?php echo $row['message']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-12">
									<div class="row">
										<div class="col-12 col-md-3 mb-3">
											<div class="form-group">
												<label for="customertype">Customer type</label>
												<input type="text" name="customertype" id="customertype" class="form-control" readonly>
											</div>
										</div>
										<div class="col-12 col-md-3 mb-3">
											<div class="form-group">
												<label for="payment_term">Terms</label>
												<input type="text" name="payment_term" id="payment_term" class="form-control" readonly>
											</div>
										</div>
										<div class="col-12 col-md-3 mb-3">
											<div class="form-group">
												<label for="salesperson">Sales Person</label>
												<input type="text" name="salesperson" id="salesperson" class="form-control" readonly>
											</div>
										</div>
										<div class="col-12 col-md-3 mb-3 mt-0 mt-md-4">
											<div class="d-flex justify-content-left">
												<!--button class="btn btn-success " type="submit" formaction="indexPlatform.php"-->
												<?php ?>
												<?php ?>
												<?php ?>
												<?php ?>
												<button type="button" id="addProduct" class="test btn btn-success" data-bs-toggle="modal" data-bs-target="#myModal">
													<i class="ace-icon fa fa-plus"></i>
													<span class="bigger-110">Add</span>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-12 mb-4">
							<div class="border border-primary rounded-3 py-4 px-2" style="background-color:powderblue;">
								<section class="intro">
									<div class="mask d-flex align-items-center">
										<div class="container-fluid">
											<div class="row justify-content-center">
												<div class="col-12">
													<div class="table-responsive table-scroll position-relative" data-mdb-perfect-scrollbar="true">
														<table class="table table-striped table-bordered table-light border-primary table-hover">
															<thead>
																<tr>
																	<th class="col-sm-2">Item name</th>
																	<th class="col-sm-3">Item description</th>
																	<th class="col-sm-1">Quantity </th>
																	<th class="col-sm-3">Uom </th>
																	<th class="col-sm-1">Reg. price </th>
																	<th class="col-sm-1">Unit price</th>
																	<th class="col-sm-2">Total </th>
																	<th class="col-sm-1">Action </th>
																</tr>
															</thead>
															<tbody id="table_quote">

															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</section>
							</div>
						</div>

						<div class="col-12 col-lg-12 mb-4 mt-2">
							<div class="row">
								<div class="container col-12 col-lg-5 border border-1 border-primary rounded-3" id="tender" style="background-color:lightgrey">
									<div class="row input-group-sm ">
										<div class="col-sm-3">
											<label for="cash">Cash</label>
										</div>
										<div class="col-sm-2">
											<input type="number" step="1.1" autocomplete="off" name="cash" class="form-control form-control-sm" id="cash" style="width: 100px;">
										</div>
									</div>

									<div class="row input-group-sm ">
										<div class="col-sm-3">
											<label for="debitcredit">Db/Cr</label>
										</div>
										<div class="col-sm-2">
											<input type="number" step="1.1" name="debitcredit" class="form-control form-control-sm" id="debitcredit" style="width: 100px;">
										</div>
									</div>
									<div class="row input-group-sm ">
										<div class="col-sm-3">
											<label for="check">Check</label>
										</div>
										<div class="col-sm-2">
											<input type="number" step="1.1" autocomplete="off" name="check" class="form-control form-control-sm" id="check" style="width: 100px;">
										</div>
										<div class="col-sm-2">
											<input type="text" name="numerocheck" autocomplete="off" class="form-control form-control-sm" id="numerocheck" style="width: 100px;">
										</div>
									</div>
									<div class="row input-group-sm ">
										<div class="col-sm-3">
											<label for="account">Account</label>
										</div>
										<div class="col-sm-2">
											<input type="number" step="1.1" name="account" class="form-control form-control-sm" id="account" style="width: 100px;">
										</div>
									</div>
									<div class="row input-group-sm ">
										<div class="col-sm-3">
											<label for="depositonso">Deposit on SO</label>
										</div>
										<div class="col-sm-2">
											<input type="number" step="1.1" name="depositonso" class="form-control form-control-sm" id="depositonso" style="width: 100px;">
										</div>
										<div class="col-sm-4">

										</div>
									</div>
								</div>


								<div class="container col-12 col-lg-7 border border-1 border-primary rounded-3" style="background-color:powderblue">
									<div class="row input-group-sm " style="height: 18px;">
										<div class="col-sm-2">
											<label class="text-bg-primary" for="discountPourcent">Discount %</label>
										</div>
										<div class="col-sm-2"></div>
										<div class="col-sm-3">
											<label class="text-bg-primary" for="discountAmount">Discount amount</label>
										</div>
										<div class="col-sm-2">
											<label for="subTotal">Sub total</label>
										</div>
										<div class="col-sm-1">
											<input type="text" name="subTotal" class="form-control form-control-sm" id="subTotal" style="width: 150px;" readonly>
										</div>
									</div>

									<div class="row input-group-sm mb-2">
										<div class="col-sm-3 ">
											<input type="number" min="1" step="1.1" name="discountPourcent" class="form-control form-control-sm" id="discountPourcent">
										</div>
										<div class="col-sm-1">

										</div>
										<div class="col-sm-3">
											<input type="number" min="1" name="discountAmount" class="form-control form-control-sm" id="discountAmount">
										</div>
									</div>

									<div class="row input-group-sm" style="height: 18px;">
										<div class="col-sm-2">
											<label class="text-bg-primary" for="tax">Tax</label>
										</div>
										<div class="col-sm-2">

										</div>
										<div class="col-sm-3">
											<label class="text-bg-primary" for="shipping">Shipping</label>
										</div>
										<div class="col-sm-2 ">
											<label for="total">Total </label>
										</div>
										<div class="col-sm-1">
											<input type="text" name="total" class="form-control form-control-sm" id="total" style="width: 150px;" readonly>
										</div>
									</div>

									<div class="row input-group-sm mb-2" style="height: 18px;">
										<div class="col-sm-3">
											<input type="number" min="1" step="1.1" name="tax" class="form-control form-control-sm" id="tax">
										</div>
										<div class="col-sm-1">

										</div>
										<div class="col-sm-3">
											<input type="number" min="1" step="1.1" name="shipping" class="form-control form-control-sm" id="shipping">
										</div>

									</div>

									<div class="inline-block ">
										<div class="navbar row">
											<div class="col-sm-2">

											</div>

											<div class="col-sm-3">
												<button id="saveNewInvoice" class="btn btn-success " type="button">
													<i class="ace-icon fa fa-save"></i>
													<span class="bigger-110">Save & New</span>
												</button>
											</div>
											<div class="col-sm-3">
												<button id="buttonClearInvoice" class="btn btn-primary" type="button">
													<i class="ace-icon fa fa-undo"></i>
													<span class="bigger-110">Clear</span>
												</button>
											</div>

											<div class="col-sm-3">
												<button id="savePrintInvoice" class="btn btn-secondary " type="button">
													<i class="ace-icon fa fa-print"></i>
													<span class="bigger-110">Save & Print</span>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- The Modal -->
						<div class="addrecords modal fade" tabindex="-1" id="myModal" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">
									<h4 class="modal-title text-bg-primary text-center">Add records</h4>
									<!-- Modal body -->
									<div class="modal-body">
										<div class="col-12 col-lg-12 mb-4">
											<div class="row px-2">
												<div class="col-12 mb-2">
													<div class="row">
														<div class="col-12 col-lg-4">
															<div class="mb-3">
																<label class="form-label" for="itemname">Item
																	Name</label>
																<select style="width: 100%;" id="itemname"
																	name="itemname"
																	class="let_choise1 form-select shadow"
																	onchange="getSearchProductById(this.value)">
																	<option value="Select a product">Select a product
																	</option>
																	<?php $query = mysqli_query($con, "select * from produit");
																	while ($row = mysqli_fetch_array($query)) {
																	?>
																		<option value="<?php echo $row['produit_id']; ?>">
																			<?php echo $row['itemname']; ?></option>
																	<?php } ?>
																</select>
															</div>
														</div>

														<div class="col-12 col-lg-8">
															<div class="mb-3">
																<label class="form-label" for="itemdescription">Item
																	Description</label>
																<select style="width: 100%;" id="itemdescription"
																	name="itemdescription"
																	class="let_choise1 form-select shadow"
																	onchange="getSearchProductById(this.value)">
																	<option selected value="Search by description">
																		Search by description</option>
																	<?php $query = mysqli_query($con, "select * from produit");
																	while ($row = mysqli_fetch_array($query)) {
																	?>
																		<option value="<?php echo $row['produit_id']; ?>">
																			<?php echo $row['itemdescription']; ?></option>
																	<?php } ?>
																</select>

																<?php $query = mysqli_query($con, "select * from produit");
																while ($row = mysqli_fetch_array($query)) {
																?>
																	<input type="hidden"
																		id="<?php echo "InputRow" . $row['produit_id']; ?>"
																		name="<?php echo "InputRow" . $row['produit_id']; ?>"
																		value="<?php echo $row['produit_id']; ?>">
																<?php } ?>
															</div>
														</div>
													</div>
												</div>

												<div class="col-12">
													<div>
														<div class="row">
															<div class="col-12 col-lg-8">
																<div class="row">
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="onhandqty">On Hand qty.</label>
																			<input type="text" name="onhandqty"
																				id="onhandqty" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="stockdetail">Stock
																				Detail</label>
																			<input type="text" name="stockdetail"
																				id="stockdetail" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="barcode">Bar Code</label>
																			<input type="text" name="barcode"
																				id="barcode" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="uom">UOM <span
																					class="text-danger ps-2"
																					id="uom_description"></span></label>
																			<select onchange="getChangeUOM(this.value)"
																				name="uom" id="uom" class="form-select">
																				<option id="uom_2" value="UOM 2">
																				</option>
																				<option id="uom_1" value="UOM 1">
																				</option>
																			</select>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="sodetail">S. O. Detail</label>
																			<input type="text" name="sodetail"
																				id="sodetail" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="regularprice">Regular
																				Price</label>
																			<input type="text" name="regularprice"
																				id="regularprice" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="quantity">Quantity</label>
																			<input type="text" name="quantity"
																				id="quantity" class="form-control"
																				autocomplete="off" autofocus>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="unitprice">Unit Price</label>
																			<input type="text" name="unitprice"
																				id="unitprice" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="productTotal">Total</label>
																			<input type="text" name="productTotal"
																				id="productTotal" class="form-control"
																				readonly>
																		</div>
																	</div>
																</div>
															</div>
															<div class="col-12 col-lg-4">
																<div>
																	<input type="image"
																		class="form-control form-control-sm"
																		style="width: 20px; height:150px">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Modal footer -->
									<div class="modal-footer">
										<div class="row">
											<div class="col-4">
												<button class="btn btn-success " type="button" id="save_product_modal"
													name="save_product_modal" value="sauvegarder"><i
														class="ace-icon fa fa-save"></i>
												</button>
											</div>

											<div class="col-4">
												<button class="btn btn-danger " type="button" id="modal_close"
													class="btn btn-danger fa fa-exit" data-bs-dismiss="modal"><i
														class="ace-icon fa fa-sign-out"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>


						<!-- The Modal Modif -->
						<div class="addrecords modal fade" tabindex="-1" id="myModalModif" role="dialog"
							aria-hidden="true">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">
									<h4 class="modal-title text-bg-primary text-center">Modify record</h4>
									<!-- Modal body -->
									<div class="modal-body">
										<div class="col-12 col-lg-12 mb-4">
											<div class="row px-2">
												<div class="col-12 mb-2">
													<div class="row">
														<div class="col-12 col-lg-4">
															<div class="form-group mb-3">
																<label class="form-label" for="itemname_modify">Item
																	Name</label>
																<input type="text" name="itemname_modify"
																	id="itemname_modify" class="form-control" readonly>
															</div>
															<input type="hidden" name="quoteid_dt_modify"
																id="quoteid_dt_modify">
														</div>

														<div class="col-12 col-lg-8">
															<div class="form-group mb-3">
																<label class="form-label"
																	for="itemdescription_modify">Item
																	Description</label>
																<textarea name="itemdescription_modify"
																	id="itemdescription_modify"
																	class="form-control form-control-sm" rows="3"
																	readonly></textarea>
															</div>
														</div>
													</div>
												</div>

												<div class="col-12">
													<div>
														<div class="row">
															<div class="col-12 col-lg-8">
																<div class="row">
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="onhandqty_modify">On Hand
																				qty.</label>
																			<input type="text" name="onhandqty_modify"
																				id="onhandqty_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="stockdetail_modify">Stock
																				Detail</label>
																			<input type="text" name="stockdetail_modify"
																				id="stockdetail_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="barcode_modify">Bar Code</label>
																			<input type="text" name="barcode_modify"
																				id="barcode_modify" class="form-control"
																				readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="uom_modify">UOM</label>
																			<select
																				onchange="getChangeUOMProductModify(this.value)"
																				name="uom_modify" id="uom_modify"
																				class="form-select">
																				<option id="uom_1_modify" value="UOM 1">
																				</option>
																				<option id="uom_2_modify" value="UOM 2">
																				</option>
																			</select>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="sodetail_modify">S. O.
																				Detail</label>
																			<input type="text" name="sodetail_modify"
																				id="sodetail_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="regularprice_modify">Regular
																				Price</label>
																			<input type="text"
																				name="regularprice_modify"
																				id="regularprice_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label
																				for="quantity_modify">Quantity</label>
																			<input type="text" name="quantity_modify"
																				id="quantity_modify"
																				class="form-control" autocomplete="off"
																				autofocus>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label for="unitprice_modify">Unit
																				Price</label>
																			<input type="text" name="unitprice_modify"
																				id="unitprice_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																	<div class="col-12 col-md-4 mb-3">
																		<div class="form-group">
																			<label
																				for="productTotal_modify">Total</label>
																			<input type="text"
																				name="productTotal_modify"
																				id="productTotal_modify"
																				class="form-control" readonly>
																		</div>
																	</div>
																</div>
															</div>
															<div class="col-12 col-lg-4">
																<div>
																	<input type="image"
																		class="form-control form-control-sm"
																		style="width: 20px; height:150px">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Modal footer -->
									<div class="modal-footer">
										<div class="row">
											<div class="col-4">
												<button class="btn btn-success " type="button" id="modifyQuoteById"
													name="modifyQuoteById" value="sauvegarder"><i
														class="ace-icon fa fa-save"></i>
												</button>
											</div>

											<div class="col-4">
												<button class="btn btn-danger " type="button"
													class="btn btn-danger fa fa-exit" data-bs-dismiss="modal"><i
														class="ace-icon fa fa-sign-out"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="tab-pane fade" id="pane_invoice_help">
			<section class="intro mt-4">
				<div class="mask d-flex align-items-center">
					<div class="container-fluid">
						<div class="row justify-content-center">
							<div class="col-12">
								<div class="table-responsive-xxl table-scroll">
									<table class="table table-bordered table-striped table- table-hover" id="myDataTable">

										<thead style="background-color:yellow;">
											<tr>
												<th class="col-sm-1">Date</th>
												<th class="col-sm-1">Statut</th>
												<th class="col-sm-1">Invoce #</th>
												<th class="col-sm-2">Cashier </th>
												<th class="col-sm-2">Full name </th>
												<th class="col-sm-1">Total qty. </th>
												<th class="col-sm-2">Total amount </th>
												<th class="col-sm-1">Currency </th>
												<th class="col-sm-1">Action </th>
											</tr>
										</thead>
										<tbody>
											<?php
											$ret = mysqli_query($con, "SELECT q.dateInvoice,q.statut,q.code,q.invoiceNumber,q.quantiteItems,q.totalItems,q.monnaie,q.invoiceIDHD,q.userc_id,q.customers_id,u.user_id,u.nom,u.prenom,c.customers_id,c.fullname
											FROM invoiceHD q INNER JOIN utilisateurs u ON q.userc_id = u.user_id INNER JOIN customers c ON q.customers_id = c.customers_id WHERE q.statut = 'E' ORDER BY q.dateInvoice");

											$cnt = 1;
											while ($row = mysqli_fetch_array($ret)) {

											?>
												<tr class="gradeX">
													<td><?php echo date("d-m-Y", strtotime($row['dateInvoice'])); ?></td>
													<td><?php echo $row['statut']; ?></td>
													<td><?php echo $row['code'] . $row['invoiceNumber']; ?></td>
													<td><?php echo $row['nom'] . ", " . $row['prenom']; ?></td>
													<td><?php echo $row['fullname']; ?></td>
													<td><?php echo $row['quantiteItems']; ?></td>
													<td><?php echo $row['totalItems']; ?></td>
													<td><?php echo $row['monnaie']; ?></td>
													<td class="center"><a href="#?editid=<?php echo $row['invoiceIDHD']; ?>"><i class=" fa fa-edit"></i></a></td>
												</tr>
											<?php
												$cnt = $cnt + 1;
											} ?>

										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

		</div>
	</div>
</div>

<div class="modalPrint modal" id="modalPrint">

	<div class="modal-dialog modal-lg w-25 p-3">

		<div class="modal-content">

			<h4 class="modal-title text-bg-secondary text-center">Print</h4>

			<div class="modal-body mx-auto">

				<div class="col-12 my-3">

					<div class="row">

						<div class="col-12 col-md-6 mb-2 mb-md-0">

							<div>

								<button type="button" name="print" class="btn btn-secondary"
									onclick="printJS({ printable: 'print8_11', type: 'html', documentTitle: 'Quote',
								honorColor: true, scanStyles: true, targetStyles: ['*'], honorMarginPadding:false,  css: '../ScCss/print8x11.css' })">

									8.5x11

								</button>
							</div>
						</div>
						<div class="col-12 col-md-6 mb-2 mb-md-0">
							<div>

								<button type="button" class="btn btn-secondary" disabled>
									17x11
								</button>

							</div>

						</div>

					</div>

				</div>



				<div class="col-12 mb-2">



					<div class="row">



						<div class="col-12 col-md-6 mb-2 mb-md-0">


							<div>



								<button type="button" name="printPetit" class="btn btn-secondary"
									onclick="printJS({ printable: 'print80', type: 'html', documentTitle: 'Quote2',

									honorColor: true, scanStyles: true, targetStyles: ['*'], honorMarginPadding:false, css: '../ScCss/print80.css' })">

									80mm

								</button>

							</div>

						</div>



						<div class="col-12 col-md-6 mb-2 mb-md-0">



							<div>



								<button type="button" disabled class="btn btn-secondary">



									95x11



								</button>



							</div>



						</div>



					</div>



				</div>

			</div>

		</div>

	</div>

</div>


<style>
	#print8_11 {
		display: none;
	}

	#print80 {
		display: none;
	}
</style>

<div id="print8_11">

	<div class="container">

		<div class="contenu_en-tete">

			<div class="en-tete1" style="margin-top: 1.5rem;">

				<span id="companyName" style="font-weight: 600;"></span>

				<span id="storeAdresse"></span>

				<span id="storeTelephone"></span>

				<span id="storeEmail"></span>

			</div>



			<div class="en-tete2">

				<div> INVOICE </div>

				<div> No &nbsp;<span id="codeQuote"></span> </div>

				<div> Date &nbsp; <span><?= date("d-m-Y"); ?></span></div>

			</div>

		</div>



		<div class="billToflex" style="padding: 7px;">

			<div>
				<span> Bill to </span>
				<span id="font">
					<span id="billTo"></span>
				</span>
			</div>

			<div style="margin-left :5rem;">
				<span> Shipp to </span>
				<span id="font">
					<span id="shipTo"></span>

				</span>
			</div>
		</div>



		<table class="table1" style="margin-top: 2rem;">

			<tr>

				<th>Cutomer ID</th>

				<th>Sales person</th>

				<th>Terms</th>

				<th>po #</th>

				<th>Currency</th>

				<th>Quote #</th>

			</tr>

			<tr>

				<td id="cust_id"></td>

				<td id="cust_nom"></td>

				<td id="cust_payment_term"></td>

				<td id="po"></td>

				<td id="cust_currency" style="font-weight: 550"></td>

				<td id="quoteInv"></td>

			</tr>

		</table>



		<table class="table table-striped table-bordered table-light border-primary table-hover text-uppercase fw-bold text-break">

			<tr>

				<th class="column3">Item name</th>
				<th class="column4">Item description</th>
				<th class="column1">Qty</th>
				<th class="column2">UOM</th>
				<th class="column1">Price</th>
				<th class="column1">Total</th>

			</tr>



			<tbody id="tableQuoteDt" class="tableQuoteDt"></tbody>

		</table>


		<div class="contenu-discount">

			<div class="border"></div>

			<div style="text-align: start;">Cash au taux de <?= $_SESSION["taux"] ?> </div>

			<div>

				<span class="disc">Sub-Total : </span>

				<span id="dt_totalItems"></span>

			</div>

			<div>

				<span>Paid by: <span id="paid"></span></span> <span style="padding: 0 0 0 4rem;">cash by: <span id="cash"></span></span>
				<span id="disc" style="padding: 0 0 0 4rem;"> Discount % :

					<span id="dt_discountPourcentage"></span>

				</span>

				<span class="disc">Discount : </span>

				<span id="dt_discountAmount"></span>

			</div>

			<div>

				<span class="disc"> Tax : </span>

				<span id="dt_taxes"></span>

			</div>

			<div>

				<span class="disc">Shipping : </span>

				<span id="dt_transport"></span>

			</div>

			<div>

				<span class="disc"> Total : </span>

				<span id="dt_grandTotal"></span>

			</div>

		</div>

		<div class="signature">
			<div> receive by: </div>
			<div class="bordure"></div>

			<div style="margin-left: 13rem;"> Signature: </div>
			<div class="bordure"></div>

		</div>

	</div>

	<style>
		.signature {
			display: flex;
			margin-top: 5rem;
		}


		.bordure {
			width: 25%;
			border-bottom: 1.5px solid black;
			/* margin-left: 15rem; */

		}
	</style>

</div>


<script src="../js/drop.js"></script>
<script src="../js/server_ajax.js"></script>

<script>
	$(document).ready(function() {
		document.getElementById("saveNewInvoice").disabled = true;
		document.getElementById("savePrintInvoice").disabled = true;
	});

	// call a quote by id
	function readQuoteNumber() {
		let quotenumber = prompt("Please enter quote number", "");
		if (quotenumber != null) {
			let page_url = document.location.href;
			modifyQuoteGlobalById(quotenumber);

			$.ajax({
				url: "Server_Jquery_Ajax.php",
				method: "post",
				data: {
					quoteIDHDFound: quotenumber,
				},
				success: function(responseS) {
					if (responseS > 0) {
						$.ajax({
							url: "Server_Jquery_Ajax.php",
							method: "post",
							data: {
								statutComplet: quotenumber,
							},
							success: function(responseC) {
								if (responseC >= 2) {

									//Insert data to Invoice
									$.ajax({
										url: "Server_Jquery_Invoice.php",
										method: "post",
										data: {
											saveInvoiceHdFirstStep: quotenumber,
										},
										success: function(responseSaveInvoice) {
											if (responseSaveInvoice > 0) {
												document.getElementById("invoice_id").setAttribute("value", responseSaveInvoice);
											}
										}
									});
								}
							}
						});
					} else {
						window.location.href = page_url;
					}
				}
			});
		}
	}

	// Clear and refresh invoice
	$("#buttonClearInvoice").on('click', function() {
		let page_url = document.location.href;
		window.location.href = page_url;
	});


	// for save a product on the modal
	$("#save_product_modal").on("click", function() {
		save_product_modal_by_type_page("Invoice");
		$("#uom_1").html("");
		$("#uom_2").html("");
	});


	// Verify type of payment
	$("#cash").on('keyup', function() {
		verifyMontantTotal();
	});
	$("#debitcredit").on('keyup', function() {
		verifyMontantTotal();
	});

	$("#check").on('keyup', function() {
		verifyMontantTotal();
	});
	$("#numerocheck").on('keyup', function() {
		verifyMontantTotal();
	});

	$("#account").on('keyup', function() {
		verifyMontantTotal();
	});
	$("#depositonso").on('keyup', function() {
		verifyMontantTotal();
	});

	//Verifier si le montant a payer est >= montant total
	function verifyMontantTotal() {
		let cash = $("#cash").val();
		let debitcredit = $("#debitcredit").val();
		let check = $("#check").val();
		let numerocheck = $("#numerocheck").val();
		let account = $("#account").val();
		let depositonso = $("#depositonso").val();
		let total = $("#total").val();

		if (cash == "") cash = 0;
		if (debitcredit == "") debitcredit = 0;
		if (check == "") check = 0;
		if (account == "") account = 0;
		if (depositonso == "") depositonso = 0;

		if (total != "") {
			if (((cash * 1) + (debitcredit * 1) + (check * 1) + (account * 1) + (depositonso * 1)) >= (total * 1)) {
				if ((check * 1) > 0) {
					if (numerocheck != "" && numerocheck.length >= 11) {
						document.getElementById("saveNewInvoice").disabled = false;
						document.getElementById("savePrintInvoice").disabled = false;
					} else {
						document.getElementById("saveNewInvoice").disabled = true;
						document.getElementById("savePrintInvoice").disabled = true;
					}
				} else {
					document.getElementById("saveNewInvoice").disabled = false;
					document.getElementById("savePrintInvoice").disabled = false;
				}
			} else {
				document.getElementById("saveNewInvoice").disabled = true;
				document.getElementById("savePrintInvoice").disabled = true;
			}
		} else {
			document.getElementById("saveNewInvoice").disabled = true;
			document.getElementById("savePrintInvoice").disabled = true;
		}
	}


	// Save and New Invoice
	$("#saveNewInvoice").on('click', function() {
		let invoice_id_save = $('#invoice_id').val();
		let page_url = document.location.href;
		let customer_id_product = $('#customer_id_product').val();
		let account = $('#account').val();
		let quoteIDHD = $('#quote_id_save').val();


		if (invoice_id_save == "") {
			$.ajax({
				url: "Server_Jquery_Ajax.php",
				method: "post",
				data: $('#form_invoice').serialize(),
				success: function(response) {
					let message_save = response;
				}
			});


			$.ajax({
				url: "Server_Jquery_Ajax.php",
				method: "post",
				data: "quoteIDHDFound=" + quoteIDHD,
				success: function(responseS) {
					if (responseS > 0) {

						$.ajax({
							url: "Server_Jquery_Ajax.php",
							method: "post",
							data: {
								statutComplet: quoteIDHD,
							},
							success: function(responseC) {
								if (responseC >= 2) {

									//Insert data to Invoice
									$.ajax({
										url: "Server_Jquery_Invoice.php",
										method: "post",
										data: {
											saveInvoiceHdFirstStep: quoteIDHD,
										},
										success: function(responseSaveInvoice) {
											if (responseSaveInvoice > 0) {
												document.getElementById("invoice_id").setAttribute("value", responseSaveInvoice);
											}
										}
									});
								}
							}
						});
					} else {
						window.location.href = page_url;
					}
				},
				error: function(err) {
					alert("Error Quote Number: " + err)
				}
			});
		}

		$.ajax({
			url: "Server_Jquery_Invoice.php",
			method: "post",
			data: {
				account_balance_customer: customer_id_product,
				account: account
			},
			success: function(result_account) {

				if (result_account == true) {
					$.ajax({
						url: "Server_Jquery_Invoice.php",
						method: "post",
						data: $("#form_invoice").serialize(),
						success: function(responseUpdateInvoice) {

							if (responseUpdateInvoice == true) {
								let invoice_id_save_1 = $('#invoice_id').val();

								$.ajax({
									url: "Server_Jquery_Invoice.php",
									method: "post",
									data: {
										codeInvoiceHD: invoice_id_save_1
									},
									success: function(result) {
										alert("Invoice save successfully" + "\nCode Invoice: " + result);
										window.location.href = page_url;
									}
								});
							}
						}
					});
				} else {
					alert(result_account);
				}
			}
		});
	});


	// Save and Print Invoice
	$("#savePrintInvoice").on('click', function() {
		let invoice_id_save = $('#invoice_id').val();
		let page_url = document.location.href;
		let customer_id_product = $('#customer_id_product').val();
		let account = $('#account').val();
		let quoteIDHD = $('#quote_id_save').val();


		if (invoice_id_save == "") {
			$.ajax({
				url: "Server_Jquery_Ajax.php",
				method: "post",
				data: $('#form_invoice').serialize(),
				success: function(response) {
					let message_save = response;
				}
			});


			$.ajax({
				url: "Server_Jquery_Ajax.php",
				method: "post",
				data: "quoteIDHDFound=" + quoteIDHD,
				success: function(responseS) {
					if (responseS > 0) {

						$.ajax({
							url: "Server_Jquery_Ajax.php",
							method: "post",
							data: {
								statutComplet: quoteIDHD,
							},
							success: function(responseC) {
								if (responseC >= 2) {

									//Insert data to Invoice
									$.ajax({
										url: "Server_Jquery_Invoice.php",
										method: "post",
										data: {
											saveInvoiceHdFirstStep: quoteIDHD,
										},
										success: function(responseSaveInvoice) {
											if (responseSaveInvoice > 0) {
												document.getElementById("invoice_id").setAttribute("value", responseSaveInvoice);
											}
										}
									});
								}
							}
						});
					} else {
						window.location.href = page_url;
					}
				},
				error: function(err) {
					alert("Error Quote Number: " + err)
				}
			});
		}

		$.ajax({
			url: "Server_Jquery_Invoice.php",
			method: "post",
			data: {
				account_balance_customer: customer_id_product,
				account: account
			},
			success: function(result_account) {

				if (result_account == true) {
					$.ajax({
						url: "Server_Jquery_Invoice.php",
						method: "post",
						data: $("#form_invoice").serialize(),
						success: function(responseUpdateInvoice) {

							if (responseUpdateInvoice == true) {
								let invoice_id_save_1 = $('#invoice_id').val();

								$.ajax({
									url: "Server_Jquery_Invoice.php",
									method: "post",
									data: {
										codeInvoiceHD: invoice_id_save_1
									},
									success: function(result) {

										//Rechercher l'en-tete du facture Invoice HD
										$.ajax({
											url: "Server_Jquery_Invoice.php",
											method: "post",
											data: "enTeteFactureInvoice=" + invoice_id_save_1,
											success: function(result_entete) {
												let json_entete = $.parseJSON(result_entete);

												/**
												 * Print format 8.5 x 11
												 */
												$("#companyName").html(json_entete.companyname);
												$("#storeAdresse").html(json_entete.Adresse);
												$("#storeTelephone").html(json_entete.Telephone1 + " * " + json_entete.Telephone2);
												$("#storeEmail").html(json_entete.emailStore);
												$("#codeQuote").html(json_entete.code + "" + json_entete.invoiceNumber);

												$("#billTo").html(json_entete.fullname + " </br>" + json_entete.rue + " " + json_entete.ville + "</br> " + json_entete.telephone1);
												$("#shipTo").html(json_entete.fullname + " </br>" + json_entete.rue + " " + json_entete.ville + "</br> " + json_entete.telephone1);

												$("#cust_id").html(json_entete.customers_id);
												$("#cust_nom").html(json_entete.nom);
												$("#cust_payment_term").html(json_entete.payment_term);
												$("#cust_currency").html(json_entete.currency);
												$("#quoteInv").html(json_entete.quoteIDHD);

												$("#dt_totalItems").html(json_entete.totalItems);
												$("#dt_discountPourcentage").html(json_entete.discountPourcentage);
												$("#dt_discountAmount").html(json_entete.discountAmount);
												$("#dt_taxes").html(json_entete.taxes);
												$("#dt_transport").html(json_entete.transport);
												$("#dt_grandTotal").html(json_entete.grandTotal);
												$("#cash").html(json_entete.grandTotal);


												//Rechercher les produits correspond a un customer
												$.ajax({
													url: "Server_Jquery_Invoice.php",
													method: "post",
													data: "invoiceDtFacture=" + invoice_id_save_1,
													success: function(resultQoteDtFacture) {

														let json_quoteDt = $.parseJSON(resultQoteDtFacture);
														let out = "";
														$(".tableQuoteDt").html("");

														$.each(json_quoteDt, function(key, value) {
															let qt = tot = 0;
															pr = value['unitPrice2'];
															if (value['quantite'] > 0) qt = value['quantite'];
															if (value['quantite2'] > 0) qt = value['quantite2'];
															tot = qt * pr;

															out += `<tr>
																<td>${value['itemName']}</td>
																<td>${value['itemDescription']}</td>
																<td>${qt}</td>
																<td>${value['descriptionUnit']}</td>
																<td>${value['unitPrice2']}</td>
																<td>${tot}</td>
															</tr>`;
														});

														$("#tableQuoteDt").html(out);
													}

												});
												let modal = document.getElementById("modalPrint");
												modal.style.display = "block";
											}

										});
									}
								});
							}
						}
					});
				} else {
					alert(result_account);
				}
			}
		});
	});

	window.onclick = function(event) {
		let modal = document.getElementById("modalPrint");
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}
</script>


<?php
require_once("../footer.php");
?>