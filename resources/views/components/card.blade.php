                <div class="row">
                    
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card custom-card card-w-per-count warning">
                            <div class="card-body">
                                <div class="d-flex align-items-top">
                                    <div class="me-3"> <span class="avatar bg-warning"> <i class="fa fa-users fs-18"></i> </span> </div>
                                    <div class="flex-fill">
                                        <span class="fw-semibold custom-card-title text-muted d-block mb-2">Users</span> 
                                        <h6 class="fw-semibold mb-2">{{ $totalUsers }}</h6>
                                        <p class="mb-0"> <span class="badge bg-primary-transparent">active</span> </p>
                                    </div>
                                    <div> <span class="fs-14 fw-semibold text-warning card-percentage">+1.03%</span> </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card custom-card card-w-per-count danger">
                            <div class="card-body">
                                <div class="d-flex align-items-top">
                                    <div class="me-3"> <span class="avatar bg-danger"> <i class="fa fa-building fs-18"></i> </span> </div>
                                    <div class="flex-fill">
                                        <span class="fw-semibold custom-card-title text-muted d-block mb-2">Companies</span> 
                                        <h6 class="fw-semibold mb-2">{{ $totalCompanies }}</h6>
                                        <p class="mb-0"> <span class="badge bg-primary-transparent">whole time</span> </p>
                                    </div>
                                    <div> <span class="fs-14 fw-semibold text-danger card-percentage">+1.03%</span> </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card custom-card card-w-per-count success">
                            <div class="card-body">
                                <div class="d-flex align-items-top">
                                    <div class="me-3"> <span class="avatar bg-success"> <i class="fa fa-money-check-dollar fs-18"></i> </span> </div>
                                    <div class="flex-fill">
                                        <span class="fw-semibold custom-card-title text-muted d-block mb-2">Spending</span> 
                                        <h6 class="fw-semibold mb-2">Rs {{ number_format($currentMonthSupplyCost) }}</h6>
                                        <p class="mb-0"> <span class="badge bg-primary-transparent">This Month</span> </p>
                                    </div>
                                    <div> <span class="fs-14 fw-semibold text-success card-percentage">+1.03%</span> </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card custom-card card-w-per-count info">
                            <div class="card-body">
                                <div class="d-flex align-items-top">
                                    <div class="me-3"> <span class="avatar bg-info"> <i class="fa fa-broom fs-18"></i> </span> </div>
                                    <div class="flex-fill">
                                        <span class="fw-semibold custom-card-title text-muted d-block mb-2">Products</span> 
                                        <h6 class="fw-semibold mb-2">{{ $currentMonthProductsSupplied }}</h6>
                                        <p class="mb-0"> <span class="badge bg-primary-transparent">This Month</span> </p>
                                    </div>
                                    <div> <span class="fs-14 fw-semibold text-info card-percentage">+1.03%</span> </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

