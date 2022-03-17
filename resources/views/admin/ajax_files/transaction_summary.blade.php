<div class="col-12">
                                <div class="card widget-inline">
                                    <div class="card-body p-0">
                                        <div class="row g-0">
                                            <div class="col-sm-6 col-xl-2">
                                                <div class="card shadow-none m-0">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-briefcase text-muted" style="font-size: 24px;"></i>
                                                        <h3><span>{{$response['noOfTranx']}}</span></h3>
                                                        <p class="text-muted font-15 mb-0">No of Transactions</p>
                                                    </div>
                                                </div>
                                            </div>
                
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-wallet text-muted" style="font-size: 24px;"></i>
                                                        <h3><span>{{'₦'.number_format($response['totalTranxValue'])}}</span></h3>
                                                        <p class="text-muted font-15 mb-0">Total Transaction Value</p>
                                                    </div>
                                                </div>
                                            </div>
                
                                            <div class="col-sm-6 col-xl-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-wallet text-muted" style="font-size: 24px;"></i>
                                                        <h3><span>{{'₦'.number_format($response['totalVendorPayout'])}}</span></h3>
                                                        <p class="text-muted font-15 mb-0">Total Vendor Payouts</p>
                                                    </div>
                                                </div>
                                            </div>
                
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-graph-line text-muted" style="font-size: 24px;"></i>
                                                        <h3><span>{{'₦'.number_format($response['totalVendorEarnings'])}}</span></h3>
                                                        <p class="text-muted font-15 mb-0">Total Vendor Earnings</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-graph-line text-muted" style="font-size: 24px;"></i>
                                                        <h3><span>{{'₦'.number_format($response['totalSalesRent'])}}</span></h3>
                                                        <p class="text-muted font-15 mb-0">Landmark Sales Rent</p>
                                                    </div>
                                                </div>
                                            </div>
                
                                        </div> <!-- end row -->
                                    </div>
                                </div> <!-- end card-box-->
                            </div> <!-- end col-->