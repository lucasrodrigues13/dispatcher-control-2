<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        #error-message {
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(99,102,241,0.08);
            border-left: 4px solid #6366f1;
            background: linear-gradient(90deg, #f8d7da 80%, #e9ecef 100%);
            color: #842029;
            max-width: 100%;
        }
        .onboarding-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .onboarding-step.active {
            display: block;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .role-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            position: relative;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .role-card.selected {
            border-color: #6366f1;
            background: linear-gradient(145deg, #6366f1, #4f46e5);
            color: white;
        }
        .role-card input[type="radio"] {
            display: none;
        }
        .check-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            background: #4f46e5;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .role-card.selected .check-icon {
            display: flex;
        }
        .role-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #4f46e5;
        }
        .role-card.selected .role-icon {
            color: white;
        }
        .disabled-card {
            opacity: 0.5;
            cursor: not-allowed !important;
            position: relative;
        }
        .disabled-card:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        .disabled-card .role-icon {
            color: #6c757d !important;
        }
        .coming-soon-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #6c757d;
            color: white;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }
        .step-progress {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
        }
        .step-circle.active {
            background: #6366f1;
            color: white;
        }
        .email-loading {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            color: #6366f1;
        }
        .form-group {
            position: relative;
        }

        /* Password Requirements Styles */
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
            font-size: 0.875rem;
        }
        .password-requirements h6 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }
        .requirement:last-child {
            margin-bottom: 0;
        }
        .requirement i {
            margin-right: 8px;
            width: 16px;
            font-size: 12px;
        }
        .requirement.valid {
            color: #198754;
        }
        .requirement.valid i {
            color: #198754;
        }
        .requirement.invalid {
            color: #dc3545;
        }
        .requirement.invalid i {
            color: #dc3545;
        }
        .requirement.neutral {
            color: #6c757d;
        }
        .requirement.neutral i {
            color: #6c757d;
        }
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        .password-strength-bar.weak {
            background: #dc3545;
            width: 25%;
        }
        .password-strength-bar.fair {
            background: #fd7e14;
            width: 50%;
        }
        .password-strength-bar.good {
            background: #ffc107;
            width: 75%;
        }
        .password-strength-bar.strong {
            background: #198754;
            width: 100%;
        }
        .strength-label {
            font-size: 0.75rem;
            margin-top: 4px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @media (max-width: 768px) {
            .auth-card {
                padding: 1.5rem;
            }
            .role-card {
                margin-bottom: 1rem;
            }
            .step-progress {
                gap: 10px;
            }
            .step-circle {
                width: 30px;
                height: 30px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <!-- Progress Steps -->
                <div class="step-progress mb-4" role="navigation" aria-label="Progress steps">
                    <div class="step-circle active" aria-current="step">1</div>
                    <div class="step-circle">2</div>
                </div>

                <!-- Step 1 - Role Selection -->
                <div class="onboarding-step active" id="step1">
                    <div class="auth-card p-5">
                        <h2 class="mb-4 text-center">What best describes your company?</h2>
                        <div class="row g-4 mb-4 justify-content-center">
                            <!-- Dispatcher - Único tipo de conta permitido -->
                            <div class="col-lg-6">
                                <label class="role-card card h-100 text-center p-4 selected" onclick="selectRole(this)" role="button" tabindex="0" aria-label="Select Dispatcher role">
                                    <input type="radio" name="role" value="dispatcher" checked hidden>
                                    <div class="check-icon"><i class="bi bi-check"></i></div>
                                    <div class="card-body">
                                        <i class="bi bi-clipboard-data role-icon"></i>
                                        <h5 class="card-title mb-3">Dispatcher</h5>
                                        <p class="card-text small">Crie sua conta como Dispatcher para gerenciar suas operações de transporte e logística</p>
                                        <p class="card-text small text-muted mt-2"><small>Você será o administrador principal e poderá criar outros usuários em sua conta</small></p>
                                    </div>
                                </label>
                            </div>
                            <!-- <div class="col-lg-4">
                                <label class="role-card card h-100 text-center p-4" onclick="selectRole(this)" role="button" tabindex="0" aria-label="Select Broker role">
                                    <input type="radio" name="role" value="broker" hidden>
                                    <div class="check-icon"><i class="bi bi-check"></i></div>
                                    <div class="card-body">
                                        <i class="bi bi-briefcase role-icon"></i>
                                        <h5 class="card-title mb-3">Broker</h5>
                                        <p class="card-text small">Cargo brokering and negotiations</p>
                                    </div>
                                </label>
                            </div> -->
                        </div>
                        <div id="error-message" class="alert alert-danger d-none" role="alert"></div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button"
                                    class="btn btn-outline-secondary col-5 col-md-4 px-4"
                                    onclick="window.location.href='{{ url('/') }}'">
                            <i class="bi bi-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn btn-primary col-5 col-md-4 py-3" onclick="nextStep(2)">
                                Next<i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2 - Role-Specific Forms -->
                <div class="onboarding-step" id="step2">
                    <div class="auth-card p-5">
                        <!-- Dispatcher Form -->
                        <div id="Dispatcher" style="display: none;">
                            <h2 class="mb-4 text-center">Dispatcher Information</h2>
                            <form method="POST" action="{{ route('dispatchers.store') }}" onsubmit="event.preventDefault(); return validateForm('Dispatcher');">
                                @csrf
                                <input type="hidden" name="register_type" value="auth_register">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="form-group">
                                            <label for="type">Type <span class="text-danger">*</span></label>
                                            <select name="type" id="type" class="form-control" required aria-describedby="type-error">
                                                <option value="" selected disabled>Select Type</option>
                                                <option value="Individual">Individual</option>
                                                <option value="Company">Company</option>
                                            </select>
                                            <div id="type-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 individual">
                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Enter name" aria-describedby="name-error">
                                            <div id="name-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 company">
                                        <div class="form-group">
                                            <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control" id="company_name" placeholder="Enter company name" aria-describedby="company_name-error">
                                            <div id="company_name-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" aria-describedby="email-error">
                                            <div class="email-loading d-none">
                                                <i class="bi bi-arrow-repeat spinner"></i>
                                            </div>
                                            <div id="email-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control password-input" id="password" placeholder="Enter password" autocomplete="new-password" aria-describedby="password-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-requirements" id="password-requirements" name="password-requirements">
                                                <h6>Password Requirements:</h6>
                                                <div class="requirement neutral" id="req-length">
                                                    <i class="bi bi-circle"></i>
                                                    <span>At least 6 characters</span>
                                                </div>
                                                <div class="requirement neutral" id="req-uppercase">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One uppercase letter (A-Z)</span>
                                                </div>
                                                <div class="requirement neutral" id="req-special">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One special character (!@#$%^&*)</span>
                                                </div>
                                                <div class="password-strength">
                                                    <div class="password-strength-bar" id="strength-bar"></div>
                                                </div>
                                                <div class="strength-label" id="strength-label">Enter password to see strength</div>
                                            </div>
                                            <div id="password-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" placeholder="Confirm password" autocomplete="new-password" aria-describedby="confirmPassword-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="confirmPassword-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 individual">
                                        <div class="form-group">
                                            <label for="ssn_itin">SSN/ITIN</label>
                                            <input type="text" name="ssn_itin" class="form-control" id="ssn_itin" placeholder="Enter SSN or ITIN" aria-describedby="ssn_itin-error">
                                            <div id="ssn_itin-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 company">
                                        <div class="form-group">
                                            <label for="ein_tax_id">EIN/Tax ID</label>
                                            <input type="text" name="ein_tax_id" class="form-control" id="ein_tax_id" placeholder="Enter EIN or Tax ID" aria-describedby="ein_tax_id-error">
                                            <div id="ein_tax_id-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 company">
                                        <div class="form-group">
                                            <label for="department">Department</label>
                                            <input type="text" name="department" class="form-control" id="department" placeholder="Enter department" aria-describedby="department-error">
                                            <div id="department-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" name="address" class="form-control" id="address" placeholder="Enter address" aria-describedby="address-error">
                                            <div id="address-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" name="city" class="form-control" id="city" placeholder="Enter city" aria-describedby="city-error">
                                            <div id="city-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input type="text" name="state" class="form-control" id="state" placeholder="Enter state" aria-describedby="state-error">
                                            <div id="state-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="zip_code">Zip Code</label>
                                            <input type="text" name="zip_code" class="form-control" id="zip_code" placeholder="Enter zip code" aria-describedby="zip_code-error">
                                            <div id="zip_code-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <input type="text" name="country" class="form-control" id="country" placeholder="Enter country" aria-describedby="country-error">
                                            <div id="country-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter phone number" aria-describedby="phone-error">
                                            <div id="phone-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="notes">Notes</label>
                                            <textarea name="notes" class="form-control" id="notes" rows="3" placeholder="Additional notes..." aria-describedby="notes-error"></textarea>
                                            <div id="notes-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(1)">
                                        <i class="bi bi-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                                        Continue<i class="bi bi-check2 ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Carrier Form -->
                        <div id="Carrier" style="display: none;">
                            <h2 class="mb-4 text-center">Carrier Information</h2>
                            <form method="POST" action="" onsubmit="return validateForm('Carrier')">
                                <input type="hidden" name="register_type" value="auth_register">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="carrier_company_name">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control" id="carrier_company_name" placeholder="Enter company name" aria-describedby="carrier_company_name-error">
                                            <div id="carrier_company_name-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="carrier_email">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control email-check" id="carrier_email" placeholder="Enter email" aria-describedby="carrier_email-error">
                                            <div class="email-loading d-none">
                                                <i class="bi bi-arrow-repeat spinner"></i>
                                            </div>
                                            <div id="carrier_email-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <!-- Add password fields for Carrier -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="carrier_password">Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control password-input" id="carrier_password" placeholder="Enter password" autocomplete="new-password" aria-describedby="carrier_password-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="carrier_password" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-requirements" id="carrier_password-requirements">
                                                <h6>Password Requirements:</h6>
                                                <div class="requirement neutral" id="carrier_req-length">
                                                    <i class="bi bi-circle"></i>
                                                    <span>At least 6 characters</span>
                                                </div>
                                                <div class="requirement neutral" id="carrier_req-uppercase">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One uppercase letter (A-Z)</span>
                                                </div>
                                                <div class="requirement neutral" id="carrier_req-special">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One special character (!@#$%^&*)</span>
                                                </div>
                                                <div class="password-strength">
                                                    <div class="password-strength-bar" id="carrier_strength-bar"></div>
                                                </div>
                                                <div class="strength-label" id="carrier_strength-label">Enter password to see strength</div>
                                            </div>
                                            <div id="carrier_password-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="carrier_confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="confirmPassword" class="form-control" id="carrier_confirmPassword" placeholder="Confirm password" autocomplete="new-password" aria-describedby="carrier_confirmPassword-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="carrier_confirmPassword" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="carrier_confirmPassword-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(1)">
                                            <i class="bi bi-arrow-left me-2"></i>Back
                                        </button>
                                        <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                                            Continue<i class="bi bi-check2 ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Broker Form -->
                        <div id="Broker" style="display: none;">
                            <h2 class="mb-4 text-center">Broker Information</h2>
                            <form method="POST" action="" onsubmit="return validateForm('Broker')">
                                <input type="hidden" name="register_type" value="auth_register">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="broker_company_name">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control" id="broker_company_name" placeholder="Enter company name" aria-describedby="broker_company_name-error">
                                            <div id="broker_company_name-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="broker_email">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control email-check" id="broker_email" placeholder="Enter email" aria-describedby="broker_email-error">
                                            <div class="email-loading d-none">
                                                <i class="bi bi-arrow-repeat spinner"></i>
                                            </div>
                                            <div id="broker_email-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <!-- Add password fields for Broker -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="broker_password">Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control password-input" id="broker_password" placeholder="Enter password" autocomplete="new-password" aria-describedby="broker_password-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="broker_password" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-requirements" id="broker_password-requirements">
                                                <h6>Password Requirements:</h6>
                                                <div class="requirement neutral" id="broker_req-length">
                                                    <i class="bi bi-circle"></i>
                                                    <span>At least 6 characters</span>
                                                </div>
                                                <div class="requirement neutral" id="broker_req-uppercase">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One uppercase letter (A-Z)</span>
                                                </div>
                                                <div class="requirement neutral" id="broker_req-special">
                                                    <i class="bi bi-circle"></i>
                                                    <span>One special character (!@#$%^&*)</span>
                                                </div>
                                                <div class="password-strength">
                                                    <div class="password-strength-bar" id="broker_strength-bar"></div>
                                                </div>
                                                <div class="strength-label" id="broker_strength-label">Enter password to see strength</div>
                                            </div>
                                            <div id="broker_password-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="broker_confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="confirmPassword" class="form-control" id="broker_confirmPassword" placeholder="Confirm password" autocomplete="new-password" aria-describedby="broker_confirmPassword-error">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="broker_confirmPassword" aria-label="Show password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="broker_confirmPassword-error" class="alert alert-danger d-none mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(1)">
                                            <i class="bi bi-arrow-left me-2"></i>Back
                                        </button>
                                        <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                                            Continue<i class="bi bi-check2 ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variável global para controlar validação de email
        let emailValidationStatus = {};

        document.addEventListener("DOMContentLoaded", () => {
            const passwordInput = document.getElementById("password");
            const requirementsBox = document.getElementById("password-requirements");

            requirementsBox.style.display = "none";

            passwordInput.addEventListener("focus", () => {
            requirementsBox.style.display = "block";
            });

            passwordInput.addEventListener("blur", () => {
            requirementsBox.style.display = "none";
            });
        });

        // Password validation function
        function validatePassword(password) {
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                special: /[!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?]/.test(password)
            };

            const score = Object.values(requirements).filter(Boolean).length;
            let strength = 'weak';
            if (score === 1) strength = 'weak';
            else if (score === 2) strength = 'fair';
            else if (score === 3 && password.length >= 8) strength = 'strong';
            else if (score === 3) strength = 'good';

            return { requirements, strength, score };
        }

        // Update password requirements display
        function updatePasswordRequirements(passwordId, password) {
            const prefix = passwordId.includes('carrier') ? 'carrier_' : passwordId.includes('broker') ? 'broker_' : '';
            const validation = validatePassword(password);

            // Update requirement indicators
            const lengthReq = document.getElementById(`${prefix}req-length`);
            const uppercaseReq = document.getElementById(`${prefix}req-uppercase`);
            const specialReq = document.getElementById(`${prefix}req-special`);
            const strengthBar = document.getElementById(`${prefix}strength-bar`);
            const strengthLabel = document.getElementById(`${prefix}strength-label`);

            if (!lengthReq || !uppercaseReq || !specialReq || !strengthBar || !strengthLabel) return;

            // Update length requirement
            updateRequirementStatus(lengthReq, validation.requirements.length);
            // Update uppercase requirement
            updateRequirementStatus(uppercaseReq, validation.requirements.uppercase);
            // Update special character requirement
            updateRequirementStatus(specialReq, validation.requirements.special);

            // Update strength bar
            strengthBar.className = `password-strength-bar ${validation.strength}`;

            // Update strength label
            const strengthTexts = {
                weak: 'Weak',
                fair: 'Fair',
                good: 'Good',
                strong: 'Strong'
            };

            const strengthColors = {
                weak: '#dc3545',
                fair: '#fd7e14',
                good: '#ffc107',
                strong: '#198754'
            };

            if (password.length === 0) {
                strengthLabel.textContent = 'Enter password to see strength';
                strengthLabel.style.color = '#6c757d';
                strengthBar.className = 'password-strength-bar';
            } else {
                strengthLabel.textContent = strengthTexts[validation.strength];
                strengthLabel.style.color = strengthColors[validation.strength];
            }
        }

        // Update individual requirement status
        function updateRequirementStatus(element, isValid) {
            const icon = element.querySelector('i');
            element.className = 'requirement ' + (isValid ? 'valid' : 'invalid');
            icon.className = isValid ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill';
        }

        // Password toggle functionality
        function initializePasswordToggles() {
            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-eye', !isHidden);
                        icon.classList.toggle('bi-eye-slash', isHidden);
                    }
                });
            });
        }

        // Initialize password validation listeners
        function initializePasswordValidation() {
            document.querySelectorAll('.password-input').forEach(input => {
                input.addEventListener('input', (e) => {
                    updatePasswordRequirements(e.target.id, e.target.value);
                });

                // Initialize display
                updatePasswordRequirements(input.id, input.value);
            });
        }

        // Step navigation
        function nextStep(step) {
            if (step === 2) {
                // Dispatcher sempre está selecionado, então sempre permitir avançar
                const errorBox = document.getElementById('error-message');
                errorBox.classList.add('d-none');
            }

            document.querySelectorAll('.step-circle').forEach((circle, i) => {
                circle.classList.toggle('active', i < step);
                circle.setAttribute('aria-current', i < step ? 'step' : 'false');
            });
            document.querySelectorAll('.onboarding-step').forEach(el => el.classList.remove('active'));
            document.getElementById(`step${step}`).classList.add('active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Role selection - Apenas Dispatcher disponível
        function selectRole(card) {
            // Apenas Dispatcher está disponível, então sempre selecionar Dispatcher
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            card.querySelector('input[type="radio"]').checked = true;
            const roleTitle = 'Dispatcher'; // Sempre Dispatcher
            document.getElementById('Dispatcher').style.display = 'block';
            window.selectedRoleTitle = roleTitle;

            // Initialize password validation
            setTimeout(() => {
                initializePasswordValidation();
            }, 100);
        }

        // Auto-selecionar Dispatcher ao carregar
        document.addEventListener('DOMContentLoaded', () => {
            const dispatcherCard = document.querySelector('input[value="dispatcher"]').closest('.role-card');
            if (dispatcherCard) {
                selectRole(dispatcherCard);
            }
        });

        // Toggle fields based on type selection
        function toggleFields() {
            const type = document.getElementById('type')?.value;
            document.querySelectorAll('.individual, .company').forEach(el => {
                el.classList.add('d-none');
            });
            if (type === 'Individual') {
                document.querySelectorAll('.individual').forEach(el => el.classList.remove('d-none'));
            } else if (type === 'Company') {
                document.querySelectorAll('.company').forEach(el => el.classList.remove('d-none'));
            }
        }

        // Verificação de email único com debounce
        let emailCheckTimeout;
        async function checkEmailUnique(emailInput) {
            const email = emailInput.value.trim();
            const errorBox = document.getElementById(`${emailInput.id}-error`);
            const loadingIcon = emailInput.parentNode.querySelector('.email-loading');

            // Reset do status de validação
            emailValidationStatus[emailInput.id] = false;

            if (!email) {
                errorBox.classList.add('d-none');
                loadingIcon.classList.add('d-none');
                return false;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorBox.textContent = 'Please enter a valid email address.';
                errorBox.classList.remove('d-none');
                loadingIcon.classList.add('d-none');
                return false;
            }

            // Mostrar loading
            loadingIcon.classList.remove('d-none');

            try {
                const response = await fetch(`/email-exists?email=${encodeURIComponent(email)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });
                const data = await response.json();

                loadingIcon.classList.add('d-none');

                if (data.exists) {
                    errorBox.textContent = 'This email is already registered. Please use a different email.';
                    errorBox.classList.remove('d-none');
                    emailValidationStatus[emailInput.id] = false;
                    return false;
                } else {
                    errorBox.classList.add('d-none');
                    emailValidationStatus[emailInput.id] = true;
                    return true;
                }
            } catch (error) {
                console.error('Error checking email:', error);
                loadingIcon.classList.add('d-none');
                errorBox.textContent = 'Error validating email. Please try again.';
                errorBox.classList.remove('d-none');
                emailValidationStatus[emailInput.id] = false;
                return false;
            }
        }

        function validateForm(role) {
            let isValid = true;
            const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?]).{6,}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            const fields = {
                Dispatcher: {
                    type: { input: 'type', required: true },
                    name: { input: 'name', required: true, condition: () => document.getElementById('type')?.value === 'Individual' },
                    company_name: { input: 'company_name', required: true, condition: () => document.getElementById('type')?.value === 'Company' },
                    email: { input: 'email', required: true, regex: emailRegex, regexMessage: 'Please enter a valid email address.' },
                    password: { input: 'password', required: true, regex: passwordRegex, regexMessage: 'Password must be at least 6 characters, with one uppercase and one special character.' },
                    confirmPassword: { input: 'confirmPassword', required: true, match: 'password', matchMessage: 'Passwords must match.' }
                },
                Carrier: {
                    company_name: { input: 'carrier_company_name', required: true },
                    email: { input: 'carrier_email', required: true, regex: emailRegex, regexMessage: 'Please enter a valid email address.' },
                    password: { input: 'carrier_password', required: true, regex: passwordRegex, regexMessage: 'Password must be at least 6 characters, with one uppercase and one special character.' },
                    confirmPassword: { input: 'carrier_confirmPassword', required: true, match: 'carrier_password', matchMessage: 'Passwords must match.' }
                },
                Broker: {
                    company_name: { input: 'broker_company_name', required: true },
                    email: { input: 'broker_email', required: true, regex: emailRegex, regexMessage: 'Please enter a valid email address.' },
                    password: { input: 'broker_password', required: true, regex: passwordRegex, regexMessage: 'Password must be at least 6 characters, with one uppercase and one special character.' },
                    confirmPassword: { input: 'broker_confirmPassword', required: true, match: 'broker_password', matchMessage: 'Passwords must match.' }
                }
            };

            // Clear previous errors
            document.querySelectorAll('.alert.alert-danger').forEach(el => {
                el.textContent = '';
                el.classList.add('d-none');
            });

            // Verificar se o email foi validado
            const emailField = fields[role].email;
            if (emailField) {
                const emailInput = document.getElementById(emailField.input);
                if (emailInput && !emailValidationStatus[emailField.input]) {
                    const errorBox = document.getElementById(`${emailField.input}-error`);
                    errorBox.textContent = 'Please verify that the email is valid and available.';
                    errorBox.classList.remove('d-none');
                    emailInput.focus();
                    return false;
                }
            }

            // Validate fields
            for (const [field, config] of Object.entries(fields[role])) {
                const input = document.getElementById(config.input);
                if (!input) continue;
                const errorBox = document.getElementById(`${config.input}-error`);
                if (config.condition && !config.condition()) continue;

                if (config.required && !input.value.trim()) {
                    errorBox.textContent = `Please fill in the ${field.replace('_', ' ')} field.`;
                    errorBox.classList.remove('d-none');
                    input.focus();
                    isValid = false;
                    continue;
                }

                if (config.regex && !config.regex.test(input.value.trim())) {
                    errorBox.textContent = config.regexMessage;
                    errorBox.classList.remove('d-none');
                    input.focus();
                    isValid = false;
                    continue;
                }

                if (config.match && input.value.trim() !== document.getElementById(config.match).value.trim()) {
                    errorBox.textContent = config.matchMessage;
                    errorBox.classList.remove('d-none');
                    input.focus();
                    isValid = false;
                }
            }

            if (isValid) {
                // Desabilitar botão durante submissão
                const submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Processing...<i class="bi bi-arrow-repeat spinner ms-2"></i>';
                }

                const form = document.querySelector(`#${role} form`);
                const formData = new FormData(form);
                
                console.log('Enviando requisição para:', form.action);
                console.log('Dados do formulário:', Object.fromEntries(formData));
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'include', // Importante para manter sessão/cookies
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    // Verificar se a resposta é JSON
                    const contentType = response.headers.get('content-type');
                    
                    // Se for um redirect, seguir o redirect
                    if (response.status >= 300 && response.status < 400) {
                        const redirectUrl = response.headers.get('Location') || response.url;
                        window.location.href = redirectUrl;
                        return null;
                    }
                    
                    // Ler o texto da resposta (só pode ser lido uma vez)
                    const text = await response.text();
                    
                    // Se não for JSON, logar para debug
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Resposta não-JSON recebida:', text.substring(0, 500));
                        throw new Error('Resposta inválida do servidor. Esperado JSON.');
                    }

                    // Tentar fazer parse do JSON
                    let data;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (e) {
                        console.error('Erro ao fazer parse do JSON:', e, 'Texto recebido:', text.substring(0, 200));
                        throw new Error('Resposta inválida do servidor: não é JSON válido');
                    }
                    
                    if (!response.ok) {
                        // Erro de validação ou outro erro (status 422, 500, etc)
                        throw { data, status: response.status };
                    }
                    
                    return data;
                })
                .then(data => {
                    console.log('Resposta recebida:', data);
                    
                    if (data && data.success) {
                        // Aguardar um pouco para garantir que a sessão foi estabelecida
                        // e usar replace para evitar problemas de cache
                        console.log('Sucesso! Redirecionando para /verify-email');
                        setTimeout(() => {
                            window.location.replace(data.redirect_url || '/verify-email');
                        }, 300);
                    } else if (data === null) {
                        // Redirect já foi tratado
                        console.log('Redirect já tratado');
                        return;
                    } else {
                        console.error('Resposta sem success:', data);
                        throw { data, status: 200 };
                    }
                })
                .catch(error => {
                    console.error('Erro no fetch:', error);
                    
                    const errorBox = document.getElementById('error-message');
                    
                    // Tratar erros de validação do Laravel
                    if (error.data && error.data.errors) {
                        const errorMessages = Object.values(error.data.errors).flat();
                        errorBox.textContent = errorMessages.join(' ');
                    } else if (error.data && error.data.message) {
                        errorBox.textContent = error.data.message;
                    } else if (error.message) {
                        errorBox.textContent = 'Error submitting the form: ' + error.message;
                    } else {
                        errorBox.textContent = 'An error occurred. Please try again.';
                    }
                    
                    errorBox.classList.remove('d-none');
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    // Reabilitar botão
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Continue<i class="bi bi-check2 ms-2"></i>';
                    }
                });
                return false;
            }
            return false;
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', () => {
            initializePasswordToggles();
            initializePasswordValidation();
            toggleFields();

            // Event listener para mudança de tipo
            document.getElementById('type')?.addEventListener('change', toggleFields);

            // Event listeners para verificação de email com debounce
            document.querySelectorAll('input[type="email"]').forEach(input => {
                input.addEventListener('input', (e) => {
                    clearTimeout(emailCheckTimeout);
                    emailCheckTimeout = setTimeout(() => {
                        checkEmailUnique(e.target);
                    }, 500); // 500ms de delay
                });

                input.addEventListener('blur', (e) => {
                    clearTimeout(emailCheckTimeout);
                    checkEmailUnique(e.target);
                });
            });

            // Keyboard navigation para role cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        selectRole(card);
                    }
                });
            });
        });
    </script>
</body>
</html>
